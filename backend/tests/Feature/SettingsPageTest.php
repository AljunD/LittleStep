<?php

namespace Tests\Feature;

use App\Models\Teacher;
use App\Models\User;
use App\Services\EccdExpertSystem;
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
            'address' => 'Balite, Rodriguez, Rizal, CALABARZON',
        ]);

        return $user;
    }

    private function validProfile(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Morgan',
            'middle_name' => '',
            'last_name' => 'Reyes',
            'sex' => 'Male',
            'contact_number' => '09281234567',
            'barangay' => 'San Jose',
            'municipality' => 'Antipolo',
            'province' => 'Rizal',
            'region' => 'CALABARZON',
        ], $overrides);
    }

    public function test_edit_profile_form_is_prefilled_and_keeps_the_email_read_only(): void
    {
        $html = $this->actingAs($this->makeTeacherUser())->get(route('settings.profile.edit'))
            ->assertOk()
            ->assertSee('id="editTeacherProfileForm"', false)
            ->assertSee('value="Taylor"', false)
            ->assertSee('value="09170000000"', false)
            ->assertSee('value="Balite"', false)
            ->assertSee('value="Rodriguez"', false)
            ->assertSee('value="Rizal"', false)
            ->assertSee('value="CALABARZON"', false)
            ->assertSee('<option value="Female" selected>Female</option>', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/<input type="email" id="email" value="taylor@example.com" disabled/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="email"/', $html);
        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('settings.profile'), '/').'"\s+aria-current="page"/', $html);
    }

    public function test_teacher_can_update_their_profile_but_not_their_email(): void
    {
        $user = $this->makeTeacherUser();

        $this->actingAs($user)
            ->put(route('settings.profile.update'), $this->validProfile(['email' => 'attacker@example.com']))
            ->assertRedirect(route('settings.profile'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Your profile has been updated.');

        $teacher = $user->teacher->refresh();
        $this->assertSame(['Morgan', null, 'Reyes', 'Male', '09281234567', 'San Jose, Antipolo, Rizal, CALABARZON'], [
            $teacher->first_name, $teacher->middle_name, $teacher->last_name, $teacher->sex, $teacher->contact_number, $teacher->address,
        ]);
        $this->assertSame('taylor@example.com', $user->refresh()->email);
        $this->assertDatabaseHas('logs', [
            'user_id' => $user->id,
            'action' => 'updated',
            'entity_type' => 'Teacher',
            'entity_id' => $teacher->id,
        ]);
    }

    public function test_saving_an_unchanged_profile_does_nothing(): void
    {
        $user = $this->makeTeacherUser();

        $this->actingAs($user)->put(route('settings.profile.update'), [
            'first_name' => 'Taylor',
            'middle_name' => 'Quinn',
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'barangay' => 'Balite',
            'municipality' => 'Rodriguez',
            'province' => 'Rizal',
            'region' => 'CALABARZON',
        ])
            ->assertRedirect(route('settings.profile'))
            ->assertSessionHas('status', 'No changes to save.');

        $this->assertDatabaseMissing('logs', ['entity_type' => 'Teacher', 'action' => 'updated']);
    }

    public function test_profile_updates_are_validated_with_the_shared_form_rules(): void
    {
        $user = $this->makeTeacherUser();
        $invalidInputs = [
            'first_name' => ['first_name' => 'Taylor1'],
            'last_name' => ['last_name' => ''],
            'sex' => ['sex' => 'Unknown'],
            'contact_number' => ['contact_number' => '12345'],
            'barangay' => ['barangay' => ''],
            'municipality' => ['municipality' => str_repeat('a', 256)],
            'province' => ['province' => ''],
            'region' => ['region' => ''],
        ];

        foreach ($invalidInputs as $field => $overrides) {
            $this->actingAs($user)->from(route('settings.profile.edit'))
                ->put(route('settings.profile.update'), $this->validProfile($overrides))
                ->assertRedirect(route('settings.profile.edit'))
                ->assertSessionHasErrors($field);
        }

        $this->assertSame('Taylor', $user->teacher->refresh()->first_name);
    }

    public function test_help_and_about_tab_lists_the_guides_and_official_domains(): void
    {
        $html = $this->actingAs($this->makeTeacherUser())->get(route('settings.help'))
            ->assertOk()
            ->assertSee('About LittleStep')
            ->assertSee('Recording an evaluation')
            ->assertSee('How scores are computed')
            ->assertSee('every month counted as 30 days')
            ->getContent();

        foreach (EccdExpertSystem::DOMAIN_LABELS as $label) {
            $this->assertStringContainsString(e($label), $html);
        }
        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('settings.help'), '/').'"\s+aria-current="page"/', $html);
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
            ->assertSee('Balite, Rodriguez, Rizal, CALABARZON')
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
        foreach (['settings.profile', 'settings.profile.edit', 'settings.display', 'settings.help'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
        $this->put(route('settings.display.update'), ['theme' => 'dark'])->assertRedirect(route('login'));
        $this->put(route('settings.profile.update'), $this->validProfile())->assertRedirect(route('login'));

        /** @var User $guardian */
        $guardian = User::factory()->create(['role' => 'guardian']);
        foreach (['settings.profile', 'settings.profile.edit', 'settings.display', 'settings.help'] as $route) {
            $this->actingAs($guardian)->get(route($route))->assertForbidden();
        }
        $this->put(route('settings.display.update'), ['theme' => 'dark'])->assertForbidden();
        $this->put(route('settings.profile.update'), $this->validProfile())->assertForbidden();
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
            ->assertSee('<html lang="en" data-theme="dark" class="dark">', false)
            // Primary buttons hover to a darker blue on the dark theme, where blue-700 is remapped to a light tint.
            ->assertSee('dark:hover:bg-blue-500', false);

        $css = file_get_contents(resource_path('css/theme-dark.css'));
        $this->assertStringContainsString('.dark :is(.text-blue-500, .text-blue-600, .hover\:text-blue-600:hover):not(.keep-light *)', $css);
        $this->assertStringContainsString('--tw-ring-offset-color: var(--color-white);', $css);
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
