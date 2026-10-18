<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Tests\TestCase;

/**
 * An unreachable mail server (no internet, wrong SMTP settings) must not turn a saved change into a 500 error:
 * the change is kept, the teacher sees a warning, and the email can be sent again later.
 */
class MailFailureTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Guardian $guardian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Teacher address',
        ]);
        $this->guardian = Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian', 'email' => 'old@example.com'])->id,
            'first_name' => 'Casey',
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Socorro, QC, Metro Manila, NCR',
            'relationship_to_child' => 'Mother',
        ]);
        $child = Child::create([
            'guardian_id' => $this->guardian->id,
            'first_name' => 'Jordan',
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2022-01-15',
            'address' => 'Socorro, QC, Metro Manila, NCR',
            'handedness' => 'right',
            'is_studying' => false,
        ]);
        ProgressRecord::create([
            'child_id' => $child->id,
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_date_round_1' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'in_progress',
        ]);
    }

    /** Every email fails the way it does when smtp.gmail.com cannot be resolved. */
    private function breakTheMailServer(): void
    {
        Mail::extend('unreachable', fn () => new class extends AbstractTransport
        {
            protected function doSend(SentMessage $message): void
            {
                throw new TransportException('Connection could not be established with host "smtp.gmail.com:587"');
            }

            public function __toString(): string
            {
                return 'unreachable://';
            }
        });
        config(['mail.mailers.unreachable' => ['transport' => 'unreachable'], 'mail.default' => 'unreachable']);
    }

    private function updateGuardian(string $email): TestResponse
    {
        return $this->actingAs($this->teacherUser)->followingRedirects()->put(route('guardians.update', $this->guardian->id), [
            'email' => $email,
            'first_name' => 'Isabella',
            'middle_name' => 'Cruz',
            'last_name' => 'Chowalter',
            'sex' => 'Female',
            'contact_number' => '09283746501',
            'relationship_to_child' => 'Mother',
            'barangay' => 'Socorro',
            'municipality' => 'QC',
            'province' => 'Metro Manila',
            'region' => 'NCR',
        ]);
    }

    public function test_changing_a_guardians_email_while_mail_is_down_saves_and_warns_instead_of_crashing(): void
    {
        $this->breakTheMailServer();

        $this->updateGuardian('new@example.com')
            ->assertOk()
            ->assertSee('Guardian updated successfully.')
            ->assertSee('The verification email could not be sent')
            ->assertDontSee('A verification link was sent');

        $user = $this->guardian->user->fresh();
        $this->assertSame('new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertSame('Isabella', $this->guardian->fresh()->first_name);
        $this->assertDatabaseHas('logs', ['action' => 'updated', 'details' => 'Guardian updated; email changed but the verification email could not be sent.']);
    }

    public function test_the_verification_email_can_be_resent_for_unverified_guardians(): void
    {
        $this->breakTheMailServer();
        $this->updateGuardian('new@example.com');

        // The ⋮ menu offers "Resend Verification Email" while the address is unverified.
        $this->actingAs($this->teacherUser)->get(route('guardians.index'))
            ->assertSee(route('guardians.resend-verification', $this->guardian->id), false);

        // Still down: a warning, not a 500.
        $this->actingAs($this->teacherUser)->followingRedirects()
            ->post(route('guardians.resend-verification', $this->guardian->id))
            ->assertOk()
            ->assertSee('The verification email could not be sent');

        // Back up: the link is sent.
        Notification::fake();
        $this->actingAs($this->teacherUser)->followingRedirects()
            ->post(route('guardians.resend-verification', $this->guardian->id))
            ->assertOk()
            ->assertSee('A new verification link was sent to new@example.com.');
        Notification::assertSentTo($this->guardian->user, CustomVerifyEmail::class);
        $this->assertDatabaseHas('logs', ['action' => 'verification_resent', 'entity_id' => $this->guardian->id]);

        // Verified guardians don't get the option.
        $this->guardian->user->markEmailAsVerified();
        $this->actingAs($this->teacherUser)->get(route('guardians.index'))
            ->assertDontSee(route('guardians.resend-verification', $this->guardian->id), false);
    }

    public function test_teachers_cannot_resend_for_guardians_they_do_not_own(): void
    {
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $otherTeacher->id,
            'first_name' => 'Other',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Male',
            'contact_number' => '09170000009',
            'address' => 'Elsewhere',
        ]);

        $this->actingAs($otherTeacher)
            ->post(route('guardians.resend-verification', $this->guardian->id))
            ->assertNotFound();
    }

    public function test_registering_a_guardian_while_mail_is_down_keeps_the_account_and_warns(): void
    {
        $this->breakTheMailServer();

        $this->actingAs($this->teacherUser)->followingRedirects()->post(route('guardians.store'), [
            'email' => 'fresh@example.com',
            'first_name' => 'Ana',
            'last_name' => 'Perez',
            'sex' => 'Female',
            'contact_number' => '09171112222',
            'relationship_to_child' => 'Mother',
            'barangay' => 'Balite',
            'municipality' => 'Rodriguez',
            'province' => 'Rizal',
            'region' => 'CALABARZON',
            'child_first_name' => 'Carlos',
            'child_last_name' => 'Perez',
            'child_sex' => 'Male',
            'child_date_of_birth' => '2022-03-14',
            'child_handedness' => 'right',
            'child_is_studying' => '0',
            'child_barangay' => 'Balite',
            'child_municipality' => 'Rodriguez',
            'child_province' => 'Rizal',
            'child_region' => 'CALABARZON',
        ])->assertOk()
            ->assertSee('Guardian and child registered successfully.')
            ->assertSee('The verification email could not be sent');

        $this->assertDatabaseHas('users', ['email' => 'fresh@example.com', 'role' => 'guardian']);
    }

    public function test_password_reset_requests_answer_the_same_while_mail_is_down(): void
    {
        $this->breakTheMailServer();

        $this->from(route('auth.password.request'))->followingRedirects()
            ->post(route('auth.password.email'), ['email' => 'old@example.com'])
            ->assertOk()
            ->assertSee('If an account exists for that email, a password reset link will be sent.');
    }
}
