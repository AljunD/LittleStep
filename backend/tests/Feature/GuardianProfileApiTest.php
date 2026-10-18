<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GuardianProfileApiTest extends TestCase
{
    use RefreshDatabase;

    private function createGuardian(string $email): Guardian
    {
        $user = User::factory()->create([
            'email' => $email,
            'role' => 'guardian',
            'email_verified_at' => now(),
        ]);

        return Guardian::factory()->create([
            'user_id' => $user->id,
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'sex' => 'Female',
            'contact_number' => '09171234567',
            'address' => 'Balite, Rodriguez, Rizal, CALABARZON',
            'relationship_to_child' => 'Mother',
        ]);
    }

    private function createChild(Guardian $guardian, string $firstName, ?string $photoPath = null): Child
    {
        return Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => 'Santos',
            'sex' => 'Female',
            'date_of_birth' => '2022-01-10',
            'address' => 'Child address',
            'handedness' => 'right',
            'is_studying' => true,
            'school_name' => 'Barangay Balite Day Care Center',
            'fathers_name' => 'Jose Santos',
            'mothers_name' => 'Maria Santos',
            'number_of_siblings' => 2,
            'birth_order' => 1,
            'photo_path' => $photoPath,
        ]);
    }

    public function test_profile_returns_the_guardians_full_details_and_email(): void
    {
        $guardian = $this->createGuardian('maria@example.com');
        Sanctum::actingAs($guardian->user, ['*']);

        $this->getJson('/api/v1/guardian/profile')
            ->assertOk()
            ->assertJsonPath('email', 'maria@example.com')
            ->assertJsonPath('contact_number', '09171234567')
            ->assertJsonPath('relationship_to_child', 'Mother')
            ->assertJsonPath('address', 'Balite, Rodriguez, Rizal, CALABARZON')
            ->assertJsonMissingPath('user_id');
    }

    public function test_child_details_are_shaped_and_scoped_to_the_guardian(): void
    {
        $guardian = $this->createGuardian('maria@example.com');
        $child = $this->createChild($guardian, 'Ana', 'photos/children/ana.jpg');
        $otherChild = $this->createChild($this->createGuardian('other@example.com'), 'Other');
        Sanctum::actingAs($guardian->user, ['*']);

        $this->getJson("/api/v1/guardian/children/{$child->id}")
            ->assertOk()
            ->assertJsonPath('first_name', 'Ana')
            ->assertJsonPath('is_studying', true)
            ->assertJsonPath('school_name', 'Barangay Balite Day Care Center')
            ->assertJsonPath('fathers_name', 'Jose Santos')
            ->assertJsonPath('number_of_siblings', 2)
            ->assertJsonPath('photo_url', "api/v1/guardian/children/{$child->id}/photo")
            ->assertJsonMissingPath('photo_path')
            ->assertJsonMissingPath('guardian_id');

        $this->getJson("/api/v1/guardian/children/{$otherChild->id}")
            ->assertNotFound();
    }

    public function test_contacts_list_each_childs_teacher_without_private_details(): void
    {
        $guardian = $this->createGuardian('maria@example.com');
        $assessed = $this->createChild($guardian, 'Ana');
        $this->createChild($guardian, 'Bea');
        $otherChild = $this->createChild($this->createGuardian('other@example.com'), 'Other');
        $teacher = Teacher::create([
            'user_id' => User::factory()->create(['role' => 'teacher'])->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09998887777',
            'address' => 'Teacher home address',
        ]);
        foreach ([$assessed, $otherChild] as $child) {
            ProgressRecord::create([
                'child_id' => $child->id,
                'teacher_id' => $teacher->id,
                'evaluation_date' => '2026-10-01',
                'evaluation_number' => 1,
                'status' => 'pending',
            ]);
        }
        Sanctum::actingAs($guardian->user, ['*']);

        $this->getJson('/api/v1/guardian/contacts')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.child.first_name', 'Ana')
            ->assertJsonPath('0.teacher.first_name', 'Taylor')
            ->assertJsonPath('0.teacher.contact_number', '09998887777')
            ->assertJsonMissingPath('0.teacher.address')
            ->assertJsonMissingPath('0.teacher.user_id')
            ->assertJsonPath('1.child.first_name', 'Bea')
            ->assertJsonPath('1.teacher', null);
    }
}
