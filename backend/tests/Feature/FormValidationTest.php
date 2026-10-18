<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Domain;
use App\Models\EccdChecklistItem;
use App\Models\Guardian;
use App\Models\Log;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use App\Notifications\CustomVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Teacher web forms: shared validation rules (App\Support\FormRules), the default guardian
 * password, and the bugs fixed during the form review.
 */
class FormValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    private Guardian $guardian;

    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Teacher address',
        ]);
        $this->guardian = Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian'])->id,
            'first_name' => 'Casey',
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);
        $this->child = Child::create([
            'guardian_id' => $this->guardian->id,
            'first_name' => 'Jordan',
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2022-01-15',
            'address' => 'Child address',
            'handedness' => 'right',
            'is_studying' => false,
            'fathers_education' => 'Some College',
        ]);
        ProgressRecord::create([
            'child_id' => $this->child->id,
            'teacher_id' => $this->teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_date_round_1' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'pending',
        ]);
    }

    private function guardianRegistration(array $overrides = []): array
    {
        return array_merge([
            'email' => 'new.guardian@example.com',
            'first_name' => 'María',
            'middle_name' => 'Dela Peña',
            'last_name' => "O'Neil-Cruz",
            'sex' => 'Female',
            'contact_number' => '09170000002',
            'relationship_to_child' => 'Mother',
            'child_first_name' => 'Niño',
            'child_last_name' => "O'Neil-Cruz",
            'child_sex' => 'Male',
            'child_date_of_birth' => '2022-03-01',
            'child_handedness' => 'right',
            'is_studying' => '0',
        ], $overrides);
    }

    private function childProfile(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Avery',
            'last_name' => 'Child',
            'sex' => 'Male',
            'date_of_birth' => '2023-02-01',
            'handedness' => 'left',
            'is_studying' => '0',
        ], $overrides);
    }

    public function test_guardian_registered_without_a_password_gets_the_default_password(): void
    {
        Notification::fake();

        $this->actingAs($this->teacherUser)
            ->post(route('guardians.store'), $this->guardianRegistration())
            ->assertRedirect(route('guardians.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'default password'));

        $user = User::where('email', 'new.guardian@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Password123@', $user->password));
        $this->assertSame('guardian', $user->role);
        Notification::assertSentTo($user, CustomVerifyEmail::class);

        // Names with ñ, accents, apostrophes and hyphens are accepted.
        $this->assertDatabaseHas('guardians', ['user_id' => $user->id, 'first_name' => 'María', 'last_name' => "O'Neil-Cruz"]);
        $this->assertDatabaseHas('children', ['first_name' => 'Niño']);
    }

    public function test_the_default_password_is_read_from_config(): void
    {
        Notification::fake();
        config(['littlestep.guardian_default_password' => 'Another-Default-123']);

        $this->actingAs($this->teacherUser)->post(route('guardians.store'), $this->guardianRegistration());

        $user = User::where('email', 'new.guardian@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Another-Default-123', $user->password));
    }

    public function test_a_typed_guardian_password_is_used_and_still_validated(): void
    {
        Notification::fake();

        $this->actingAs($this->teacherUser)
            ->post(route('guardians.store'), $this->guardianRegistration([
                'password' => 'short',
                'password_confirmation' => 'short',
            ]))
            ->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'new.guardian@example.com']);

        $this->actingAs($this->teacherUser)
            ->post(route('guardians.store'), $this->guardianRegistration([
                'password' => 'Custom-Password-42',
                'password_confirmation' => 'Custom-Password-42',
            ]))
            ->assertSessionHas('success', 'Guardian and child registered successfully.');

        $user = User::where('email', 'new.guardian@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('Custom-Password-42', $user->password));
        $this->assertFalse(Hash::check('Password123@', $user->password));
    }

    public function test_the_registration_form_explains_the_default_password(): void
    {
        $this->actingAs($this->teacherUser)->get(route('guardians.create'))
            ->assertOk()
            ->assertSee('Leave both password fields empty to use the default password')
            ->assertSee('Password123@')
            ->assertDontSee('id="guardianPassword" name="password" minlength="12" maxlength="72" autocomplete="new-password" required', false);
    }

    public function test_names_with_digits_and_future_birth_dates_are_rejected(): void
    {
        $this->actingAs($this->teacherUser)
            ->post(route('guardians.store'), $this->guardianRegistration([
                'first_name' => 'Maria2',
                'child_date_of_birth' => now()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors(['first_name', 'child_date_of_birth']);

        $this->actingAs($this->teacherUser)
            ->post(route('guardians.store-child', $this->guardian->id), $this->childProfile([
                'date_of_birth' => now()->addDay()->toDateString(),
                'fathers_age' => 500,
                'fathers_education' => 'Anything',
            ]))
            ->assertSessionHasErrors(['date_of_birth', 'fathers_age', 'fathers_education']);
    }

    public function test_linked_child_photo_and_studying_fields_are_normalised(): void
    {
        $this->actingAs($this->teacherUser)
            ->post(route('guardians.store-child', $this->guardian->id), $this->childProfile([
                'is_studying' => '0',
                'school_name' => 'Should be cleared',
                'fathers_education' => 'College',
            ]))
            ->assertRedirect(route('guardians.index'));

        $this->assertDatabaseHas('children', [
            'first_name' => 'Avery',
            'is_studying' => false,
            'school_name' => null,
            'fathers_education' => 'College',
        ]);
    }

    public function test_child_update_without_handedness_is_a_validation_error_not_a_crash(): void
    {
        $this->actingAs($this->teacherUser)
            ->from(route('children.edit', $this->child->id))
            ->put(route('children.update', $this->child->id), $this->childProfile(['handedness' => '']))
            ->assertRedirect(route('children.edit', $this->child->id))
            ->assertSessionHasErrors('handedness');

        $this->assertSame('right', $this->child->fresh()->handedness);
    }

    public function test_child_update_keeps_an_education_value_saved_before_the_dropdown(): void
    {
        $this->actingAs($this->teacherUser)->get(route('children.edit', $this->child->id))
            ->assertOk()
            ->assertSee('<option value="Some College" selected>Some College</option>', false);

        $this->actingAs($this->teacherUser)
            ->put(route('children.update', $this->child->id), $this->childProfile([
                'first_name' => 'Jordan',
                'fathers_education' => 'Some College',
            ]))
            ->assertRedirect(route('children.show', $this->child->id));

        $this->assertSame('Some College', $this->child->fresh()->fathers_education);
    }

    public function test_changing_a_guardian_email_requires_verifying_the_new_address(): void
    {
        Notification::fake();
        $user = $this->guardian->user;
        $user->createToken('mobile');

        $this->actingAs($this->teacherUser)
            ->put(route('guardians.update', $this->guardian->id), ['email' => 'Casey.New@Example.com'])
            ->assertRedirect(route('guardians.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'verification link'));

        $user->refresh();
        $this->assertSame('casey.new@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->assertSame(0, $user->tokens()->count());
        Notification::assertSentTo($user, CustomVerifyEmail::class);
    }

    public function test_saving_the_same_guardian_email_keeps_the_account_verified(): void
    {
        Notification::fake();
        $user = $this->guardian->user;

        $this->actingAs($this->teacherUser)
            ->put(route('guardians.update', $this->guardian->id), ['email' => $user->email, 'first_name' => 'Casey'])
            ->assertSessionHas('success', 'Guardian updated successfully.');

        $this->assertNotNull($user->fresh()->email_verified_at);
        Notification::assertNothingSent();
    }

    public function test_reopening_a_verification_link_does_not_repeat_the_verified_email(): void
    {
        $user = User::factory()->create(['role' => 'guardian', 'email_verified_at' => null]);
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)->assertRedirect();
        $this->get($url)->assertRedirect();

        $this->assertSame(1, Log::where('action', 'email_verified')->where('entity_id', $user->id)->count());
    }

    public function test_a_progress_comment_needs_a_response_before_it_is_saved(): void
    {
        $item = EccdChecklistItem::create(['domain' => 'gross_motor', 'activity' => 'Climbs on chair', 'item_number' => 1]);

        $this->actingAs($this->teacherUser)
            ->from(route('progress.create', ['child_id' => $this->child->id, 'domain' => 'gross_motor']))
            ->post(route('progress.store'), [
                'child_id' => $this->child->id,
                'domain' => 'gross_motor',
                'assessment_date' => '2026-10-01',
                'comments' => [$item->id => 'Needed help'],
            ])
            ->assertSessionHasErrors("comments.{$item->id}");

        $this->assertSame(0, Domain::count());
    }

    public function test_log_filters_accept_a_single_date_and_literal_wildcards(): void
    {
        $this->actingAs($this->teacherUser);
        recordLog('viewed', 'Child', 1, 'Plain entry');
        recordLog('viewed', 'Child', 2, 'Progress 100% done');

        $this->get(route('logs.index', ['to_date' => now()->toDateString()]))
            ->assertOk()
            ->assertSessionHasNoErrors()
            ->assertSee('Plain entry');

        $this->get(route('logs.index', ['from_date' => now()->addDay()->toDateString()]))
            ->assertOk()
            ->assertDontSee('Plain entry');

        $this->get(route('logs.index', ['search' => '100%']))
            ->assertOk()
            ->assertSee('Progress 100% done')
            ->assertDontSee('Plain entry');

        $this->get(route('logs.index', ['search' => '%']))
            ->assertOk()
            ->assertDontSee('Plain entry');
    }

    public function test_the_unused_child_store_endpoint_is_removed(): void
    {
        $this->actingAs($this->teacherUser)
            ->post('/children', ['guardian_id' => $this->guardian->id, 'first_name' => 'X'])
            ->assertStatus(405);
    }

    public function test_teacher_registration_uses_the_shared_contact_and_name_rules(): void
    {
        $this->post(route('auth.register.submit'), [
            'first_name' => 'Taylor1',
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '555-0100',
            'address' => 'Test address',
            'email' => 'teacher2@example.com',
            'password' => 'a-secure-test-password',
            'password_confirmation' => 'a-secure-test-password',
        ])->assertSessionHasErrors(['first_name', 'contact_number']);

        $this->assertDatabaseMissing('users', ['email' => 'teacher2@example.com']);
    }
}
