<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}