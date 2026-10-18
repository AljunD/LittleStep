<?php

namespace Tests\Feature;

use App\Models\Guardian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Guardians who sign in with the default password are told to change it (`must_change_password`),
 * and can change it from the mobile app with PUT /api/v1/guardian/password.
 */
class GuardianPasswordApiTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_PASSWORD = 'Password123@';

    private const NEW_PASSWORD = 'A-much-better-passphrase';

    private User $user;

    private Guardian $guardian;

    protected function setUp(): void
    {
        parent::setUp();

        config(['littlestep.guardian_default_password' => self::DEFAULT_PASSWORD]);

        $this->user = User::factory()->create([
            'email' => 'guardian@example.com',
            'password' => Hash::make(self::DEFAULT_PASSWORD),
            'role' => 'guardian',
            'email_verified_at' => now(),
        ]);
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

    public function test_login_reports_when_the_default_password_was_used(): void
    {
        $this->login(self::DEFAULT_PASSWORD)->assertOk()->assertJsonPath('must_change_password', true);

        $this->user->forceFill(['password' => Hash::make(self::NEW_PASSWORD)])->save();

        $this->login(self::NEW_PASSWORD)->assertOk()->assertJsonPath('must_change_password', false);
    }

    public function test_guardian_changes_the_default_password_and_other_devices_are_signed_out(): void
    {
        $otherDevice = $this->user->createToken('guardian-mobile')->plainTextToken;
        $token = $this->login(self::DEFAULT_PASSWORD)->json('token');

        $this->changePassword($token, [
            'current_password' => self::DEFAULT_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertOk()->assertExactJson(['message' => 'Your password has been changed.']);

        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $this->user->fresh()->password));
        $this->assertDatabaseHas('logs', ['action' => 'password_changed', 'entity_id' => $this->guardian->id, 'user_id' => $this->user->id]);

        // This device stays signed in; the other one is signed out.
        $this->assertSame(1, $this->user->tokens()->count());
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/guardian/profile')->assertOk();
        $this->app['auth']->forgetGuards();
        $this->withToken($otherDevice)->getJson('/api/v1/guardian/profile')->assertUnauthorized();

        // The new password works for the next sign-in, without the reminder.
        $this->login(self::NEW_PASSWORD)->assertOk()->assertJsonPath('must_change_password', false);
    }

    public function test_the_new_password_is_validated(): void
    {
        $token = $this->login(self::DEFAULT_PASSWORD)->json('token');
        $cases = [
            'wrong current password' => [['current_password' => 'not-my-password'], 'current_password', 'Your current password is incorrect.'],
            'too short' => [['password' => 'short', 'password_confirmation' => 'short'], 'password', null],
            'not confirmed' => [['password_confirmation' => 'something-else-entirely'], 'password', null],
            'a longer variant of the default' => [['current_password' => self::DEFAULT_PASSWORD, 'password' => self::DEFAULT_PASSWORD.'!', 'password_confirmation' => self::DEFAULT_PASSWORD.'!'], null, null],
        ];

        foreach ($cases as $case => [$overrides, $field, $message]) {
            $response = $this->changePassword($token, array_merge([
                'current_password' => self::DEFAULT_PASSWORD,
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ], $overrides));

            if ($field === null) {
                // A longer variant of the default is a different password, so it is allowed.
                $response->assertOk();

                continue;
            }

            $response->assertUnprocessable()->assertJsonValidationErrors($field);
            if ($message) {
                $response->assertJsonPath("errors.{$field}.0", $message);
            }
            $this->assertTrue(Hash::check(self::DEFAULT_PASSWORD, $this->user->fresh()->password), $case);
        }
    }

    public function test_reusing_the_current_or_default_password_is_rejected(): void
    {
        $this->user->forceFill(['password' => Hash::make(self::NEW_PASSWORD)])->save();
        $token = $this->user->createToken('guardian-mobile')->plainTextToken;

        $this->changePassword($token, [
            'current_password' => self::NEW_PASSWORD,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertUnprocessable()->assertJsonPath('errors.password.0', 'The new password must be different from your current password.');

        $this->changePassword($token, [
            'current_password' => self::NEW_PASSWORD,
            'password' => self::DEFAULT_PASSWORD,
            'password_confirmation' => self::DEFAULT_PASSWORD,
        ])->assertUnprocessable()->assertJsonValidationErrors(['password' => 'Choose a password other than the default password.']);
    }

    public function test_changing_the_password_requires_a_signed_in_guardian(): void
    {
        $this->putJson('/api/v1/guardian/password', [
            'current_password' => self::DEFAULT_PASSWORD,
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
