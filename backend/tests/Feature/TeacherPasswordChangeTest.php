<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class TeacherPasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private const CURRENT_PASSWORD = 'Password123@';

    private const NEW_PASSWORD = 'NewSecurePass#2026';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'teacher', 'remember_token' => 'old-remember-token']);
        Teacher::create([
            'user_id' => $this->user->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Teacher address',
        ]);
    }

    private function changePassword(array $overrides = [])
    {
        return $this->actingAs($this->user)
            ->from(route('settings.profile'))
            ->put(route('settings.password.update'), array_merge([
                'current_password' => self::CURRENT_PASSWORD,
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ], $overrides));
    }

    public function test_profile_tab_shows_the_change_password_form(): void
    {
        $html = $this->actingAs($this->user)->get(route('settings.profile'))
            ->assertOk()
            ->assertSee('Change Password')
            ->assertSee('action="'.route('settings.password.update').'"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/name="password"[^>]*minlength="12"\s+maxlength="72"/', $html);
    }

    public function test_teacher_can_change_their_password_and_stays_signed_in(): void
    {
        Notification::fake();

        $this->changePassword()
            ->assertRedirect(route('settings.profile'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->user->refresh();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $this->user->password));
        $this->assertFalse(Hash::check(self::CURRENT_PASSWORD, $this->user->password));
        $this->assertNotSame('old-remember-token', $this->user->remember_token);
        $this->assertDatabaseHas('logs', ['user_id' => $this->user->id, 'action' => 'password_changed']);
        Notification::assertSentTo($this->user, PasswordChangedNotification::class);
    }

    public function test_the_confirmation_email_links_to_password_reset_without_revealing_the_password(): void
    {
        config(['app.url' => 'https://portal.example.com']);

        $html = (new PasswordChangedNotification(now()))->toMail($this->user)->render();

        $this->assertStringContainsString('Your password was changed', $html);
        $this->assertStringContainsString('href="https://portal.example.com/auth/forgot-password"', $html);
        $this->assertStringNotContainsString(self::NEW_PASSWORD, $html);
        $this->assertStringNotContainsString(self::CURRENT_PASSWORD, $html);
    }

    public function test_the_browser_that_changed_the_password_stays_signed_in(): void
    {
        $this->actingAs($this->user)->followingRedirects()
            ->put(route('settings.password.update'), [
                'current_password' => self::CURRENT_PASSWORD,
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => self::NEW_PASSWORD,
            ])
            ->assertOk()
            ->assertSee('Change Password');

        $this->assertAuthenticatedAs($this->user);
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        Notification::fake();

        $this->changePassword(['current_password' => 'NotMyPassword#1'])
            ->assertRedirect(route('settings.profile'))
            ->assertSessionHasErrors(['current_password' => 'Your current password is incorrect.']);

        $this->assertTrue(Hash::check(self::CURRENT_PASSWORD, $this->user->refresh()->password));
        $this->assertDatabaseMissing('logs', ['action' => 'password_changed']);
        Notification::assertNothingSent();
    }

    public function test_new_password_must_be_strong_confirmed_and_different(): void
    {
        // More attempts than the throttle allows; rate limiting has its own test.
        $this->withoutMiddleware(ThrottleRequests::class);

        $invalidAttempts = [
            [['password' => 'short', 'password_confirmation' => 'short'], 'password'],
            [['password_confirmation' => 'SomethingElse#2026'], 'password'],
            [['password' => self::CURRENT_PASSWORD.'longer', 'password_confirmation' => self::CURRENT_PASSWORD], 'password'],
            [['password' => str_repeat('a', 73), 'password_confirmation' => str_repeat('a', 73)], 'password'],
            [['current_password' => ''], 'current_password'],
        ];

        foreach ($invalidAttempts as [$overrides, $field]) {
            $this->changePassword($overrides)->assertSessionHasErrors($field);
        }

        $this->changePassword(['password' => self::CURRENT_PASSWORD, 'password_confirmation' => self::CURRENT_PASSWORD])
            ->assertSessionHasErrors(['password' => 'The new password must be different from your current password.']);

        $this->assertTrue(Hash::check(self::CURRENT_PASSWORD, $this->user->refresh()->password));
    }

    public function test_typed_passwords_are_never_flashed_back_to_the_form(): void
    {
        $this->changePassword(['current_password' => 'Typed#Secret#Value', 'password_confirmation' => 'Mismatch#2026'])
            ->assertSessionHasErrors('current_password');

        $this->assertNull(session()->getOldInput('current_password'));
        $this->assertNull(session()->getOldInput('password'));

        $this->actingAs($this->user)->from(route('settings.profile'))->followingRedirects()
            ->put(route('settings.password.update'), [
                'current_password' => 'Typed#Secret#Value',
                'password' => self::NEW_PASSWORD,
                'password_confirmation' => 'Mismatch#2026',
            ])
            ->assertOk()
            ->assertSee('data-validation-errors', false)
            ->assertSee('aria-invalid="true"', false)
            ->assertDontSee('Typed#Secret#Value')
            ->assertDontSee('Mismatch#2026');
    }

    public function test_other_devices_are_signed_out_and_api_tokens_revoked(): void
    {
        config(['session.driver' => 'database']);
        $otherUser = User::factory()->create(['role' => 'teacher']);
        $storedSession = fn (string $id, int $userId) => [
            'id' => $id, 'user_id' => $userId, 'ip_address' => '127.0.0.1', 'user_agent' => 'Test',
            'payload' => base64_encode(serialize([])), 'last_activity' => now()->timestamp,
        ];
        DB::table('sessions')->insert([
            $storedSession('teacher-laptop-session', $this->user->id),
            $storedSession('someone-elses-session', $otherUser->id),
        ]);
        $this->user->createToken('old-device');

        $this->changePassword()->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('sessions', ['id' => 'teacher-laptop-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'someone-elses-session']);
        $this->assertSame(0, $this->user->tokens()->count());
    }

    public function test_a_password_reset_signs_the_account_out_everywhere(): void
    {
        config(['session.driver' => 'database']);
        $otherUser = User::factory()->create(['role' => 'teacher']);
        $storedSession = fn (string $id, int $userId) => [
            'id' => $id, 'user_id' => $userId, 'ip_address' => '127.0.0.1', 'user_agent' => 'Test',
            'payload' => base64_encode(serialize([])), 'last_activity' => now()->timestamp,
        ];
        DB::table('sessions')->insert([
            $storedSession('possibly-stolen-session', $this->user->id),
            $storedSession('someone-elses-session', $otherUser->id),
        ]);
        $this->user->createToken('old-device');

        $this->post(route('auth.password.update'), [
            'token' => Password::createToken($this->user),
            'email' => $this->user->email,
            'password' => self::NEW_PASSWORD,
            'password_confirmation' => self::NEW_PASSWORD,
        ])->assertRedirect(route('login'))->assertSessionHasNoErrors();

        $this->user->refresh();
        $this->assertTrue(Hash::check(self::NEW_PASSWORD, $this->user->password));
        $this->assertNotSame('old-remember-token', $this->user->remember_token);
        $this->assertDatabaseMissing('sessions', ['id' => 'possibly-stolen-session']);
        $this->assertDatabaseHas('sessions', ['id' => 'someone-elses-session']);
        $this->assertSame(0, $this->user->tokens()->count());
        $this->assertDatabaseHas('logs', ['user_id' => $this->user->id, 'action' => 'password_reset']);
    }

    public function test_password_changes_are_rate_limited(): void
    {
        foreach (range(1, 5) as $attempt) {
            $this->changePassword(['current_password' => 'Wrong#Password'.$attempt])->assertSessionHasErrors('current_password');
        }

        $this->changePassword()->assertStatus(429);
        $this->assertTrue(Hash::check(self::CURRENT_PASSWORD, $this->user->refresh()->password));
    }

    public function test_only_signed_in_teachers_can_change_a_password(): void
    {
        $payload = ['current_password' => self::CURRENT_PASSWORD, 'password' => self::NEW_PASSWORD, 'password_confirmation' => self::NEW_PASSWORD];

        $this->put(route('settings.password.update'), $payload)->assertRedirect(route('login'));

        $guardian = User::factory()->create(['role' => 'guardian']);
        $this->actingAs($guardian)->put(route('settings.password.update'), $payload)->assertForbidden();
        $this->assertTrue(Hash::check(self::CURRENT_PASSWORD, $guardian->refresh()->password));
    }
}
