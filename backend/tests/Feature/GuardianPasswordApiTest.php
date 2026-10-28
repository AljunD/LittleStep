<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GuardianPasswordApiTest extends TestCase
{
    use RefreshDatabase;

    private const TEMPORARY_PASSWORD = 'k7mq-p2rw-z9tx';

    private const NEW_PASSWORD = 'A-much-better-passphrase';

    private User $user;

    private Guardian $guardian;

    protected function setUp(): void
    {
        parent::setUp();

        // An account as a teacher creates it: a temporary password the guardian should change.
        $this->user = User::factory()->create([
            'email' => 'guardian@example.com',
            'password' => Hash::make(self::TEMPORARY_PASSWORD),
            'role' => 'guardian',
            'email_verified_at' => now(),
        ]);
        $this->user->forceFill(['must_change_password' => true])->save();
        $this->guardian = Guardian::factory()->create([
            'user_id' => $this->user->id,
            'sex' => 'Female',
            'contact_number' => '09171234567',
            'address' => 'Test guardian address',
        ]);
    }

    private function login(string $password): TestResponse
    {
        return $this->postJson('/api/v1/guardian/login', ['email' => 'guardian@example.com', 'password' => $password]);
    }

    private function changePassword(string $token, array $data): TestResponse
    {
        return $this->withToken($token)->putJson('/api/v1/guardian/password', $data);
    }

    public function test_login_reports_when_the_temporary_password_is_still_in_use(): void
    {
        $this->login(self::TEMPORARY_PASSWORD)->assertOk()->assertJsonPath('must_change_password', true);

        $this->user->forceFill(['must_change_password' => false])->save();

        $this->login(self::TEMPORARY_PASSWORD)->assertOk()->assertJsonPath('must_change_password', false);
    }

    public function test_the_old_shared_default_password_is_not_special_any_more(): void
    {
        $this->user->forceFill(['password' => Hash::make('Password123@'), 'must_change_password' => false])->save();

        $this->login('Password123@')->assertOk()->assertJsonPath('must_change_password', false);
    }

    public function test_guardian_changes_the_temporary_password_and_other_devices_are_signed_out(): void
    {
        $otherDevice = $this->user->createToken('guardian-mobile')->plainTextToken;
        $token = $this->login(self::TEMPORARY_PASSWORD)->json('token');

        $this->changePassword($token, [
            'current_password' => self::TEMPORARY_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertOk()->assertExactJson(['message' => 'Your password has been changed.']);

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $this->user->fresh()->password));
        $this->assertFalse($this->user->fresh()->must_change_password);
        $this->assertDatabaseHas('logs', ['action' => 'password_changed', 'entity_id' => $this->guardian->id, 'user_id' => $this->user->id]);

        $this->assertSame(1, $this->user->tokens()->count());
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/guardian/profile')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($otherDevice)->getJson('/api/v1/guardian/profile')->assertUnauthorized();

        $this->login(self::NEW_PASSWORD)->assertOk()->assertJsonPath('must_change_password', false);
    }

    public function test_the_new_password_is_validated(): void
    {
        $token = $this->login(self::TEMPORARY_PASSWORD)->json('token');
        $cases = [
            'wrong current password' => [['current_password' => 'not-my-password'], 'current_password', 'Your current password is incorrect.'],
            'too short' => [['password' => 'short', 'password_confirmation' => 'short'], 'password', null],
            'not confirmed' => [['password_confirmation' => 'something-else-entirely'], 'password', null],
            'a longer variant of the temporary password' => [['password' => self::TEMPORARY_PASSWORD.'!', 'password_confirmation' => self::TEMPORARY_PASSWORD.'!'], null, null],
        ];

        foreach ($cases as $case => [$overrides, $field, $message]) {
            $response = $this->changePassword($token, array_merge([
                'current_password' => self::TEMPORARY_PASSWORD,
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ], $overrides));

            if ($field === null) {
                $response->assertOk();

                continue;
            }

            $response->assertUnprocessable()->assertJsonValidationErrors($field);
            if ($message) {
                $response->assertJsonPath("errors.{$field}.0", $message);
            }
            $this->assertTrue(Hash::check(self::TEMPORARY_PASSWORD, $this->user->fresh()->password), $case);
            $this->assertTrue($this->user->fresh()->must_change_password, $case);
        }
    }

    public function test_reusing_the_current_password_is_rejected(): void
    {
        $this->user->forceFill(['password' => Hash::make(self::NEW_PASSWORD)])->save();
        $token = $this->user->createToken('guardian-mobile')->plainTextToken;

        $this->changePassword($token, [
            'current_password' => self::NEW_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertUnprocessable()->assertJsonPath('errors.password.0', 'The new password must be different from your current password.');
    }

    public function test_changing_the_password_requires_a_signed_in_guardian(): void
    {
        $this->putJson('/api/v1/guardian/password', [
            'current_password' => self::TEMPORARY_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertUnauthorized();

        $teacher = User::factory()->create(['role' => 'teacher', 'email_verified_at' => now()]);
        $this->withToken($teacher->createToken('x')->plainTextToken)->putJson('/api/v1/guardian/password', [
            'current_password' => 'password',
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertForbidden();
    }
}
