<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProgressObservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_save_and_reload_child_observations(): void
    {
        $user = User::factory()->create([
            'role' => 'teacher',
        ]);
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '555-0100',
            'address' => 'Test address',
        ]);

        $guardianUser = User::factory()->create([
            'role' => 'guardian',
        ]);
        $guardian = Guardian::create([
            'user_id' => $guardianUser->id,
            'first_name' => 'Casey',
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '555-0101',
            'address' => 'Test address',
            'relationship_to_child' => 'Mother',
        ]);
        $child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => 'Jordan',
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2020-01-15',
            'address' => 'Test address',
            'handedness' => 'right',
            'is_studying' => false,
        ]);
        ProgressRecord::create([
            'child_id' => $child->id,
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2026-10-12',
            'evaluation_date_round_1' => '2026-10-12',
            'evaluation_number' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get(route('progress.add-observation', ['child_id' => $child->id]))
            ->assertOk()
            ->assertSee('Taylor Teacher');

        $response = $this->post(route('progress.observation.store'), [
            'child_id' => $child->id,
            'date_administered' => '2026-10-12',
            'place_administered' => 'Classroom',
            'background_observations' => 'Participated well.',
            'family_environment_notes' => 'Supportive home.',
            'stimulating_activities_notes' => 'Reads together daily.',
            'home_environment_notes' => 'Quiet study space.',
            'other_notes' => 'No additional concerns.',
        ]);

        $response->assertRedirect(route('progress.add-observation', ['child_id' => $child->id]))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('progress_records', [
            'child_id' => $child->id,
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2026-10-12',
        ]);
        $this->assertDatabaseHas('domain_observations', [
            'place_administered' => 'Classroom',
            'background_observations' => 'Participated well.',
            'family_environment_notes' => 'Supportive home.',
            'stimulating_activities_notes' => 'Reads together daily.',
            'home_environment_notes' => 'Quiet study space.',
            'other_notes' => 'No additional concerns.',
            'status' => 'completed',
        ]);

        $this->get(route('progress.add-observation', ['child_id' => $child->id]))
            ->assertOk()
            ->assertSee('Participated well.')
            ->assertSee('Classroom');
    }

    public function test_teacher_cannot_access_another_teachers_child_observation(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $teacherUser->id,
            'first_name' => 'Teacher',
            'last_name' => 'One',
            'sex' => 'Female',
            'contact_number' => '555-0100',
            'address' => 'Test address',
        ]);
        $otherTeacherUser = User::factory()->create(['role' => 'teacher']);
        $otherTeacher = Teacher::create([
            'user_id' => $otherTeacherUser->id,
            'first_name' => 'Teacher',
            'last_name' => 'Two',
            'sex' => 'Female',
            'contact_number' => '555-0102',
            'address' => 'Test address',
        ]);
        $guardianUser = User::factory()->create(['role' => 'guardian']);
        $guardian = Guardian::create([
            'user_id' => $guardianUser->id,
            'first_name' => 'Casey',
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '555-0101',
            'address' => 'Test address',
            'relationship_to_child' => 'Mother',
        ]);
        $child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => 'Jordan',
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2020-01-15',
            'handedness' => 'right',
            'is_studying' => false,
        ]);
        ProgressRecord::create([
            'child_id' => $child->id,
            'teacher_id' => $otherTeacher->id,
            'evaluation_date' => '2026-10-12',
            'evaluation_number' => 1,
            'status' => 'pending',
        ]);

        $this->actingAs($teacherUser)
            ->get(route('progress.add-observation', ['child_id' => $child->id]))
            ->assertNotFound();

        $this->actingAs($teacherUser)
            ->get(route('children.show', $child->id))
            ->assertNotFound();

        $this->actingAs($teacherUser, 'sanctum')
            ->getJson('/api/v1/guardian/profile')
            ->assertForbidden();
    }

    public function test_security_headers_are_added_to_web_responses(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_child_photos_are_served_from_private_storage_to_the_owning_teacher_only(): void
    {
        Storage::fake('local');

        $user = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'first_name' => 'Taylor',
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '555-0100',
            'address' => 'Test address',
        ]);
        $otherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $otherUser->id,
            'first_name' => 'Casey',
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '555-0101',
            'address' => 'Test address',
        ]);
        $guardianUser = User::factory()->create(['role' => 'guardian']);
        $guardian = Guardian::create([
            'user_id' => $guardianUser->id,
            'first_name' => 'Jordan',
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '555-0102',
            'address' => 'Test address',
            'relationship_to_child' => 'Mother',
        ]);
        $child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => 'Riley',
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2020-01-15',
            'handedness' => 'right',
            'is_studying' => false,
        ]);
        ProgressRecord::create([
            'child_id' => $child->id,
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2026-10-12',
            'evaluation_number' => 1,
            'status' => 'pending',
        ]);
        $photoPath = 'photos/children/child.gif';
        Storage::disk('local')->put($photoPath, base64_decode('R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=', true));
        $child->update(['photo_path' => $photoPath]);

        $this->actingAs($user)
            ->get(route('children.photo', $child->id))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->get('/storage/'.$photoPath)->assertNotFound();

        $this->actingAs($otherUser)
            ->get(route('children.photo', $child->id))
            ->assertNotFound();
    }
}
