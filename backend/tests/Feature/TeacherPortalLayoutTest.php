<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherPortalLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_records_and_marks_the_active_sidebar_link(): void
    {
        $teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Teacher address',
        ]);
        $guardian = Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian'])->id,
            'first_name' => 'Casey',
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);
        $child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => 'Jordan',
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2022-01-15',
            'address' => 'Child address',
            'handedness' => 'right',
            'is_studying' => false,
        ]);
        ProgressRecord::create([
            'child_id' => $child->id,
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'pending',
        ]);

        $html = $this->actingAs($teacherUser)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Taylor!')
            ->assertSee('Jordan Child')
            ->assertSee('Action required')
            ->assertDontSee('KidWatch')
            ->getContent();

        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('dashboard'), '/').'"\s+aria-current="page"/', $html);
        $this->assertDoesNotMatchRegularExpression('/href="'.preg_quote(route('guardians.index'), '/').'"\s+aria-current="page"/', $html);
    }
}
