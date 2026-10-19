<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class FlashMessagesTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    private Guardian $guardian;

    private Child $child;

    private ProgressRecord $progressRecord;

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
            'address' => 'Balite, Rodriguez, Rizal, CALABARZON',
            'handedness' => 'right',
            'is_studying' => false,
        ]);
        $this->progressRecord = ProgressRecord::create([
            'child_id' => $this->child->id,
            'teacher_id' => $this->teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_date_round_1' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'in_progress',
        ]);
    }

    private function assertFlashShownOnce(TestResponse $response, string $type, string $message): void
    {
        $response->assertOk()->assertSee('data-flash="'.$type.'"', false);
        $this->assertSame(
            1,
            substr_count($response->getContent(), e($message)),
            "Expected the {$type} message to appear exactly once: {$message}"
        );
    }

    public function test_unverified_teacher_is_told_why_login_was_refused(): void
    {
        User::factory()->create([
            'email' => 'new.teacher@example.com',
            'password' => Hash::make('Password123@'),
            'role' => 'teacher',
            'email_verified_at' => null,
        ]);

        $response = $this->from(route('login'))->followingRedirects()->post(route('auth.login.submit'), [
            'email' => 'new.teacher@example.com',
            'password' => 'Password123@',
        ]);

        $response->assertOk()
            ->assertSee('Welcome Back')
            ->assertSee('You must verify your email before logging in.')
            ->assertSee('value="new.teacher@example.com"', false);
        $this->assertGuest();
    }

    public function test_child_update_success_is_shown_on_the_profile_page(): void
    {
        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->put(route('children.update', $this->child->id), [
                'first_name' => 'Jordan',
                'last_name' => 'Updated',
                'sex' => 'Female',
                'date_of_birth' => '2022-01-15',
                'barangay' => 'Balite',
                'municipality' => 'Rodriguez',
                'handedness' => 'right',
                'is_studying' => '0',
            ]);

        $this->assertFlashShownOnce($response, 'success', 'Child profile updated successfully.');
    }

    public function test_child_update_validation_errors_keep_the_form_visible(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->from(route('children.edit', $this->child->id))
            ->followingRedirects()
            ->put(route('children.update', $this->child->id), [
                'first_name' => '',
                'last_name' => 'Child',
                'sex' => 'Female',
                'date_of_birth' => '2022-01-15',
            ]);

        $response->assertOk()
            ->assertSee('data-validation-errors', false)
            ->assertSee('The first name field is required.')
            ->assertSee('id="editChildForm"', false);
    }

    public function test_child_archive_success_is_shown_on_the_children_list(): void
    {
        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->delete(route('children.destroy', $this->child->id));

        $this->assertFlashShownOnce($response, 'success', 'Child archived successfully.');
    }

    public function test_child_restore_success_is_shown_once_on_the_archive(): void
    {
        $this->child->delete();

        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->patch(route('children.restore', $this->child->id));

        $this->assertFlashShownOnce($response, 'success', 'Child record restored successfully.');
    }

    public function test_password_change_success_is_shown_on_the_profile_tab(): void
    {
        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->put(route('settings.password.update'), [
                'current_password' => 'Password123@',
                'password' => 'NewSecurePass#2026',
                'password_confirmation' => 'NewSecurePass#2026',
            ]);

        $this->assertFlashShownOnce($response, 'success', 'Your password has been changed. Other devices signed in to your account have been signed out.');
    }

    public function test_display_preference_success_is_shown_on_the_display_tab(): void
    {
        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->put(route('settings.display.update'), ['theme' => 'dark']);

        $this->assertFlashShownOnce($response, 'success', 'Display preference saved.');
    }

    public function test_progress_shortcuts_without_a_selection_explain_the_error(): void
    {
        $this->assertFlashShownOnce(
            $this->actingAs($this->teacherUser)->followingRedirects()->get(route('progress.add-observation')),
            'error',
            'Select a child before adding observation notes.'
        );

        $this->assertFlashShownOnce(
            $this->actingAs($this->teacherUser)->followingRedirects()->get(route('progress.edit')),
            'error',
            'Select a progress record to edit.'
        );
    }

    public function test_progress_update_success_is_shown_on_the_record_page(): void
    {
        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->put(route('progress.update', $this->progressRecord->id), [
                'assessment_dates' => [1 => '2026-10-01'],
            ]);

        $this->assertFlashShownOnce($response, 'success', 'Progress record updated successfully.');
    }

    public function test_guardian_update_success_banner_is_shown_once(): void
    {
        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->put(route('guardians.update', $this->guardian->id), [
                'first_name' => 'Casey',
                'last_name' => 'Guardian',
                'sex' => 'Female',
                'contact_number' => '09170000001',
                'relationship_to_child' => 'Mother',
                'email' => $this->guardian->user->email,
            ]);

        $this->assertFlashShownOnce($response, 'success', 'Guardian updated successfully.');
    }

    public function test_guardian_registration_success_is_shown_on_the_guardians_list(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->post(route('guardians.store'), [
                'email' => 'new.guardian@example.com',
                'password' => 'Password123@456',
                'password_confirmation' => 'Password123@456',
                'first_name' => 'Morgan',
                'last_name' => 'Guardian',
                'sex' => 'Male',
                'contact_number' => '09170000002',
                'relationship_to_child' => 'Father',
                'child_first_name' => 'Riley',
                'child_last_name' => 'Guardian',
                'child_sex' => 'Male',
                'child_date_of_birth' => '2022-03-01',
                'child_handedness' => 'right',
            ]);

        $this->assertFlashShownOnce($response, 'success', 'Guardian and child registered successfully.');
        $response->assertSee('Morgan')->assertDontSee('Registration Portal');
    }

    public function test_guardian_update_validation_errors_keep_the_edit_form_visible(): void
    {
        $response = $this->actingAs($this->teacherUser)
            ->from(route('guardians.edit', $this->guardian->id))
            ->followingRedirects()
            ->put(route('guardians.update', $this->guardian->id), [
                'first_name' => 'Casey1',
                'contact_number' => '12345',
            ]);

        $response->assertOk()
            ->assertSee('data-validation-errors', false)
            ->assertSee('The first name may only contain letters, spaces, periods, apostrophes and hyphens.')
            ->assertSee('id="editGuardianForm"', false)
            ->assertSee('value="Casey1"', false);
    }

    public function test_guardian_archive_success_is_shown_on_the_guardians_list(): void
    {
        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->delete(route('guardians.destroy', $this->guardian->id));

        $this->assertFlashShownOnce($response, 'success', 'Guardian and assigned children archived successfully.');
        $this->assertSoftDeleted($this->guardian);
    }

    public function test_linking_a_child_to_a_guardian_shows_the_banner_on_the_guardians_list(): void
    {
        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->post(route('guardians.store-child', $this->guardian->id), [
                'first_name' => 'Avery',
                'last_name' => 'Child',
                'sex' => 'Male',
                'date_of_birth' => '2023-02-01',
                'handedness' => 'left',
            ]);

        $this->assertFlashShownOnce($response, 'success', 'Child profile created successfully.');
    }

    public function test_archiving_a_linked_child_shows_the_banner_and_keeps_the_teacher_on_a_valid_page(): void
    {
        $sibling = $this->guardian->children()->create([
            'first_name' => 'Avery',
            'last_name' => 'Child',
            'sex' => 'Male',
            'date_of_birth' => '2023-02-01',
            'handedness' => 'left',
            'is_studying' => false,
        ]);
        ProgressRecord::create([
            'child_id' => $sibling->id,
            'teacher_id' => $this->teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->delete(route('guardians.unlink-child', ['guardianId' => $this->guardian->id, 'childId' => $this->child->id]));
        $this->assertFlashShownOnce($response, 'success', 'Child profile archived successfully.');
        $response->assertSee('Archive Child Record');
        $this->assertSoftDeleted($this->child);

        $response = $this->actingAs($this->teacherUser)->followingRedirects()
            ->delete(route('guardians.unlink-child', ['guardianId' => $this->guardian->id, 'childId' => $sibling->id]));
        $this->assertFlashShownOnce($response, 'success', 'Child profile archived successfully.');
        $response->assertSee('Manage and view all registered child guardians.');
    }

    public function test_guardian_pages_use_only_the_shared_message_components(): void
    {
        $this->actingAs($this->teacherUser);

        foreach ([
            route('guardians.index'),
            route('guardians.create'),
            route('guardians.edit', $this->guardian->id),
            route('guardians.archive-child', $this->guardian->id),
            route('children.edit', $this->child->id),
        ] as $url) {
            $this->get($url)->assertOk()
                ->assertDontSee('id="success-message"', false)
                ->assertDontSee('id="failed-message"', false)
                ->assertDontSee('alert(', false);
        }
    }

    public function test_password_reset_request_and_completion_messages_use_the_shared_banner(): void
    {
        $response = $this->from(route('auth.password.request'))->followingRedirects()
            ->post(route('auth.password.email'), ['email' => 'nobody@example.com']);

        $this->assertFlashShownOnce(
            $response,
            'success',
            'If an account exists for that email, a password reset link will be sent.'
        );
    }

    public function test_children_create_route_is_not_exposed(): void
    {
        $this->actingAs($this->teacherUser)->get('/children/create')->assertNotFound();
    }
}
