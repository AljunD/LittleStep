<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_teacher_account_and_hashes_the_password(): void
    {
        Notification::fake();

        $response = $this->post(route('auth.register.submit'), [
            'first_name' => 'Taylor',
            'middle_name' => '',
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000100',
            'barangay' => 'Balite',
            'municipality' => 'Rodriguez',
            'province' => 'Rizal',
            'region' => 'CALABARZON',
            'email' => '  TEACHER@example.com ',
            'password' => 'a-secure-test-password',
            'password_confirmation' => 'a-secure-test-password',
        ]);

        $user = User::where('email', 'teacher@example.com')->firstOrFail();

        $response->assertRedirect(route('verification.notice'));
        $this->assertSame('teacher', $user->role);
        $this->assertTrue(Hash::check('a-secure-test-password', $user->password));
        $this->assertDatabaseHas('teachers', [
            'user_id' => $user->id,
            'first_name' => 'Taylor',
            'address' => 'Balite, Rodriguez, Rizal, CALABARZON',
        ]);
        Notification::assertSentTo($user, CustomVerifyEmail::class);
    }

    public function test_registration_rejects_passwords_over_the_bcrypt_byte_limit(): void
    {
        $password = str_repeat("\u{00E9}", 37);

        $response = $this->from(route('auth.register.form'))
            ->post(route('auth.register.submit'), [
                'first_name' => 'Taylor',
                'last_name' => 'Teacher',
                'sex' => 'Female',
                'contact_number' => '09170000100',
                'barangay' => 'Balite',
                'municipality' => 'Rodriguez',
                'province' => 'Rizal',
                'region' => 'CALABARZON',
                'email' => 'teacher@example.com',
                'password' => $password,
                'password_confirmation' => $password,
            ]);

        $response->assertRedirect(route('auth.register.form'))
            ->assertSessionHasErrors('password');
        $this->assertSame(0, User::count());
        $this->assertSame(0, Teacher::count());
    }

    public function test_account_confirmation_email_contains_no_personal_profile_data_or_external_assets(): void
    {
        $html = view('emails.account-details')->render();

        $this->assertStringContainsString('Your LittleStep email is verified', $html);
        $this->assertStringNotContainsString('cdn.jsdelivr.net', $html);
        $this->assertStringNotContainsString('contact_number', $html);
        $this->assertStringNotContainsString('Address:', $html);
        $this->assertStringNotContainsString('Password:', $html);
    }

    public function test_sign_in_and_registration_forms_are_configured_to_lock_after_submit(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-lock-submit', false)
            ->assertSee('data-submitting-text="Signing in..."', false)
            ->assertSee('form.dataset.submitting === \'true\'', false);

        $this->get(route('auth.register.form'))
            ->assertOk()
            ->assertSee('data-lock-submit', false)
            ->assertSee('data-submitting-text="Registering..."', false);
    }

    public function test_guardian_mobile_login_requires_email_verification(): void
    {
        $user = User::factory()->create([
            'email' => 'guardian@example.com',
            'password' => Hash::make('Password123@'),
            'role' => 'guardian',
            'email_verified_at' => null,
        ]);

        Guardian::factory()->create([
            'user_id' => $user->id,
            'sex' => 'Female',
            'contact_number' => '09171234567',
            'address' => 'Test guardian address',
        ]);

        $response = $this->postJson('/api/v1/guardian/login', [
            'email' => 'guardian@example.com',
            'password' => 'Password123@',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => 'Email not verified. Please verify your email before logging in.']);
    }

    public function test_verified_guardian_can_log_in_get_scoped_children_and_revoke_their_token(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'email' => 'guardian@example.com',
            'password' => Hash::make('Password123@'),
            'role' => 'guardian',
            'email_verified_at' => now(),
        ]);
        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
            'sex' => 'Female',
            'contact_number' => '09171234567',
            'address' => 'Test guardian address',
        ]);
        $child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => 'Jordan',
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2021-04-12',
            'address' => 'Private address',
            'handedness' => 'right',
            'is_studying' => false,
            'photo_path' => 'private/photo.png',
        ]);
        $photo = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jN7sAAAAASUVORK5CYII=',
            true
        );
        $this->assertNotFalse($photo);
        Storage::disk('local')->put('private/photo.png', $photo);
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000100',
            'address' => 'Test address',
        ]);
        ProgressRecord::create([
            'child_id' => $child->id,
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2026-10-17',
            'evaluation_date_round_1' => '2026-10-17',
            'evaluation_number' => 1,
            'status' => 'in_progress',
        ]);
        $otherGuardian = Guardian::factory()->create([
            'user_id' => User::factory()->create([
                'role' => 'guardian',
                'email_verified_at' => now(),
            ])->id,
            'sex' => 'Female',
            'contact_number' => '09171234568',
            'address' => 'Other guardian address',
        ]);
        $otherChild = Child::create([
            'guardian_id' => $otherGuardian->id,
            'first_name' => 'Other',
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => 'Male',
            'date_of_birth' => '2021-04-12',
            'address' => 'Other private address',
            'handedness' => 'right',
            'is_studying' => false,
            'photo_path' => null,
        ]);

        $loginResponse = $this->postJson('/api/v1/guardian/login', [
            'email' => ' GUARDIAN@example.com ',
            'password' => 'Password123@',
        ]);

        $loginResponse->assertOk()
            ->assertJsonStructure([
                'token',
                'expires_at',
                'guardian' => ['id', 'first_name', 'middle_name', 'last_name'],
            ])
            ->assertJsonMissingPath('guardian.contact_number');

        $token = $loginResponse->json('token');
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->getJson('/api/v1/guardian/children', $headers)
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.first_name', 'Jordan')
            ->assertJsonPath('0.photo_url', 'api/v1/guardian/children/'.$child->id.'/photo')
            ->assertJsonMissingPath('0.photo_path')
            ->assertJsonMissingPath('0.address');

        $dashboardResponse = $this->getJson(
            '/api/v1/guardian/children/'.$child->id.'/dashboard',
            $headers
        );
        $dashboardResponse
            ->assertOk()
            ->assertJsonPath('child.id', $child->id)
            ->assertJsonPath('child.photo_url', 'api/v1/guardian/children/'.$child->id.'/photo')
            ->assertJsonPath('progress.status', 'in_progress')
            ->assertJsonPath('progress.latest_round', 1)
            ->assertJsonPath('progress.rounds.1.completed_domains', 0)
            ->assertJsonPath('progress.rounds.1.domains.gross_motor.label', 'Gross Motor')
            ->assertJsonPath('progress.rounds.1.domains.gross_motor.raw_score', null);

        $this->getJson('/api/v1/guardian/children/'.$otherChild->id.'/dashboard', $headers)
            ->assertNotFound();

        $this->get('/api/v1/guardian/children/'.$child->id.'/photo', $headers)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/api/v1/guardian/children/'.$otherChild->id.'/photo', $headers)
            ->assertNotFound();

        $this->postJson('/api/v1/guardian/logout', [], $headers)
            ->assertOk()
            ->assertJson(['message' => 'Logged out']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => (int) explode('|', $token, 2)[0],
        ]);
        Auth::forgetGuards();

        $this->getJson('/api/v1/guardian/children', $headers)
            ->assertUnauthorized();
    }

    public function test_guardian_email_verification_link_marks_the_account_verified(): void
    {
        $user = User::factory()->create([
            'email' => 'guardian@example.com',
            'role' => 'guardian',
            'email_verified_at' => null,
        ]);

        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $response = $this->get($verificationUrl);

        $response->assertRedirect(route('guardian.verification.confirmation'));
        $this->assertNotNull($user->fresh()->email_verified_at);

        $this->get(route('guardian.verification.confirmation'))
            ->assertOk()
            ->assertSee('Welcome to LittleStep!')
            ->assertSee('LittleStep mobile app');
    }
}
