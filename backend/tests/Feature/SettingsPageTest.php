<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use App\Support\DisplayTheme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPageTest extends TestCase
{
    use RefreshDatabase;

    private function makeTeacherUser(): User
    {
        $user = User::factory()->create(['role' => 'teacher', 'email' => 'taylor@example.com']);
        Teacher::create([
            'user_id' => $user->id,
            'first_name' => 'Taylor',
            'middle_name' => 'Quinn',
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Teacher address',
        ]);

        return $user;
    }

    public function test_settings_opens_the_profile_tab_with_the_teachers_details(): void
    {
        $user = $this->makeTeacherUser();

        $this->actingAs($user)->get(route('settings.index'))
            ->assertRedirect(route('settings.profile'));

        $html = $this->get(route('settings.profile'))
            ->assertOk()
            ->assertSee('Taylor Quinn Teacher')
            ->assertSee('taylor@example.com')
            ->assertSee('09170000000')
            ->assertSee('Teacher address')
            ->assertDontSee($user->password)
            ->getContent();

        $this->assertMatchesRegularExpression('/data-settings-panel\s+class="[^"]*lg:overflow-y-auto/', $html);

        $profileLink = preg_quote(route('settings.profile'), '/');
        $sidebarLink = preg_quote(route('settings.index'), '/');
        $this->assertMatchesRegularExpression('/href="'.$profileLink.'"\s+aria-current="page"/', $html);
        $this->assertMatchesRegularExpression('/href="'.$sidebarLink.'"\s+aria-current="page"/', $html);
    }

    public function test_settings_require_a_signed_in_teacher(): void
    {
        foreach (['settings.profile', 'settings.display'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->put(route('settings.display.update'), ['theme' => 'dark'])->assertRedirect(route('login'));

        $guardian = User::factory()->create(['role' => 'guardian']);
        $this->actingAs($guardian)->get(route('settings.display'))->assertForbidden();
        $this->put(route('settings.display.update'), ['theme' => 'dark'])->assertForbidden();
    }

    public function test_display_tab_lists_the_themes_with_light_selected_by_default(): void
    {
        $html = $this->actingAs($this->makeTeacherUser())->get(route('settings.display'))
            ->assertOk()
            ->assertSee('Light')
            ->assertSee('Dark')
            ->assertSee('System')
            ->assertSee('<html lang="en" data-theme="light" class="">', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/value="light"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('settings.display'), '/').'"\s+aria-current="page"/', $html);
    }

    public function test_saving_a_theme_stores_it_logs_it_and_applies_it_to_the_portal(): void
    {
        $user = $this->makeTeacherUser();

        $this->actingAs($user)->put(route('settings.display.update'), ['theme' => 'dark'])
            ->assertRedirect(route('settings.display'))
            ->assertSessionHas('success', 'Display preference saved.')
            ->assertCookie(DisplayTheme::COOKIE, 'dark');

        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'action' => 'updated',
            'details' => 'Display theme set to Dark.',
        ]);

        $this->withCookie(DisplayTheme::COOKIE, 'dark')->get(route('dashboard'))
            ->assertOk()
            ->assertSee('<html lang="en" data-theme="dark" class="dark">', false);
    }

    public function test_system_theme_follows_the_device_with_a_nonced_script(): void
    {
        $html = $this->actingAs($this->makeTeacherUser())
            ->withCookie(DisplayTheme::COOKIE, 'system')
            ->get(route('settings.display'))
            ->assertOk()
            ->assertSee('data-theme="system"', false)
            ->assertSee("matchMedia('(prefers-color-scheme: dark)')", false)
            ->getContent();

        $this->assertMatchesRegularExpression('/value="system"[^>]*checked/', $html);
        $this->assertMatchesRegularExpression('/<script nonce="[^"]+">\s*\(\(\) => \{\s*const prefersDark/', $html);
    }

    public function test_invalid_themes_are_rejected_and_unknown_cookies_fall_back_to_light(): void
    {
        $this->actingAs($this->makeTeacherUser())
            ->put(route('settings.display.update'), ['theme' => 'neon'])
            ->assertSessionHasErrors('theme')
            ->assertCookieMissing(DisplayTheme::COOKIE);

        $this->withCookie(DisplayTheme::COOKIE, 'neon')->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-theme="light"', false)
            ->assertDontSee('prefers-color-scheme', false);
    }
}
