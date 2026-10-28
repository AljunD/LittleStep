<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use App\Support\TemporaryPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GuardianTemporaryPasswordTest extends TestCase
{
    use RefreshDatabase;

    private const OWN_PASSWORD = 'Guardian-own-password-1';

    private User $teacherUser;

    private Teacher $teacher;

    private Guardian $guardian;

    private Child $child;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->teacherUser, $this->teacher] = $this->createTeacher('09170000000');
        $this->guardian = Guardian::create([
            'user_id' => User::factory()->create([
                'email' => 'casey@example.com',
                'role' => 'guardian',
                'password' => Hash::make(self::OWN_PASSWORD),
            ])->id,
            'first_name' => 'Casey',
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);
        $this->child = $this->createChild($this->guardian, $this->teacher, 'Jordan');
    }

    private function createTeacher(string $contactNumber): array
    {
        $user = User::factory()->create(['role' => 'teacher']);

        return [$user, Teacher::create([
            'user_id' => $user->id,
            'first_name' => 'Taylor',
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => $contactNumber,
            'address' => 'Teacher address',
        ])];
    }

    private function createChild(Guardian $guardian, Teacher $teacher, string $firstName): Child
    {
        $child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => $firstName,
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2022-01-15',
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

        return $child;
    }

    public function test_temporary_passwords_are_random_and_easy_to_read(): void
    {
        $passwords = array_map(fn () => TemporaryPassword::generate(), range(1, 50));

        $this->assertCount(50, array_unique($passwords));
        foreach ($passwords as $password) {
            $this->assertMatchesRegularExpression('/^[a-hjkmnp-z2-9]{4}-[a-hjkmnp-z2-9]{4}-[a-hjkmnp-z2-9]{4}$/', $password);
        }
    }

    public function test_teacher_resets_a_guardians_password_and_the_guardian_is_signed_out(): void
    {
        $appToken = $this->guardian->user->createToken('guardian-mobile')->plainTextToken;

        $this->actingAs($this->teacherUser)
            ->post(route('guardians.reset-password', $this->guardian->id))
            ->assertRedirect(route('guardians.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'Casey Guardian, who was signed out of the app'));

        $temporaryPassword = session('temporary_password');
        $user = $this->guardian->user->fresh();
        $this->assertTrue(Hash::check($temporaryPassword, $user->password));
        $this->assertFalse(Hash::check(self::OWN_PASSWORD, $user->password));
        $this->assertTrue($user->must_change_password);
        $this->assertSame(0, $user->tokens()->count());
        $this->assertDatabaseHas('logs', ['action' => 'password_reset', 'entity_type' => 'Guardian', 'entity_id' => $this->guardian->id, 'user_id' => $this->teacherUser->id]);

        $this->app['auth']->forgetGuards();
        $this->withToken($appToken)->getJson('/api/v1/guardian/profile')->assertUnauthorized();
        $this->postJson('/api/v1/guardian/login', ['email' => 'casey@example.com', 'password' => self::OWN_PASSWORD])->assertUnauthorized();
        $this->postJson('/api/v1/guardian/login', ['email' => 'casey@example.com', 'password' => $temporaryPassword])
            ->assertOk()
            ->assertJsonPath('must_change_password', true);
    }

    public function test_teachers_cannot_reset_passwords_of_guardians_they_do_not_teach(): void
    {
        [$otherTeacherUser] = $this->createTeacher('09170000009');

        $this->actingAs($otherTeacherUser)
            ->post(route('guardians.reset-password', $this->guardian->id))
            ->assertNotFound();

        $this->assertTrue(Hash::check(self::OWN_PASSWORD, $this->guardian->user->fresh()->password));
        $this->assertDatabaseMissing('logs', ['action' => 'password_reset']);
    }

    public function test_a_guardian_shared_with_another_teacher_keeps_their_password(): void
    {
        [, $otherTeacher] = $this->createTeacher('09170000009');
        $this->createChild($this->guardian, $otherTeacher, 'Sibling');

        $this->actingAs($this->teacherUser)
            ->post(route('guardians.reset-password', $this->guardian->id))
            ->assertForbidden();

        $this->assertTrue(Hash::check(self::OWN_PASSWORD, $this->guardian->user->fresh()->password));
    }

    public function test_the_guardians_menu_offers_a_confirmed_password_reset(): void
    {
        $this->actingAs($this->teacherUser)->get(route('guardians.index'))
            ->assertOk()
            ->assertSee('action="'.route('guardians.reset-password', $this->guardian->id).'"', false)
            ->assertSee('data-confirm="Create a new temporary password for Casey Guardian?', false)
            ->assertSee('Reset Password');
    }

    public function test_a_password_the_teacher_types_on_the_edit_form_is_also_temporary(): void
    {
        $this->actingAs($this->teacherUser)
            ->put(route('guardians.update', $this->guardian->id), [
                'password' => 'Teacher-typed-password',
                'password_confirmation' => 'Teacher-typed-password',
            ])
            ->assertRedirect(route('guardians.index'));

        $user = $this->guardian->user->fresh();
        $this->assertTrue(Hash::check('Teacher-typed-password', $user->password));
        $this->assertTrue($user->must_change_password);
    }

    public function test_adding_guardians_is_rate_limited_because_it_sends_email(): void
    {
        $this->actingAs($this->teacherUser);

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $this->post(route('guardians.store'), [])->assertSessionHasErrors('email');
        }

        $this->post(route('guardians.store'), [])->assertTooManyRequests();
    }
}
