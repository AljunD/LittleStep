<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoutingTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id, 'first_name' => 'Taylor', 'middle_name' => null, 'last_name' => 'Teacher',
            'sex' => 'Female', 'contact_number' => '09170000000', 'address' => 'Teacher address',
        ]);
    }

    private function childWithRecord(string $firstName, string $status): ProgressRecord
    {
        $guardian = Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian'])->id, 'first_name' => 'Casey', 'middle_name' => null,
            'last_name' => 'Guardian', 'sex' => 'Female', 'contact_number' => '09170000001', 'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);
        $child = Child::create([
            'guardian_id' => $guardian->id, 'first_name' => $firstName, 'middle_name' => null, 'last_name' => 'Child',
            'sex' => 'Female', 'date_of_birth' => '2022-01-15', 'address' => 'Child address', 'handedness' => 'right', 'is_studying' => false,
        ]);

        return ProgressRecord::create([
            'child_id' => $child->id, 'teacher_id' => $this->teacher->id, 'evaluation_date' => '2026-10-01',
            'evaluation_number' => 1, 'status' => $status,
        ]);
    }

    public function test_the_site_root_opens_the_sign_in_page_or_the_dashboard(): void
    {
        $this->get('/')->assertRedirect(route('login'));
        $this->actingAs($this->teacherUser)->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_signed_in_teachers_skip_the_guest_pages(): void
    {
        $this->actingAs($this->teacherUser);

        foreach (['login', 'auth.register.form', 'auth.password.request'] as $route) {
            $this->get(route($route))->assertRedirect(route('dashboard'));
        }
        $this->get(route('password.reset', 'some-token'))->assertRedirect(route('dashboard'));
    }

    public function test_signing_in_lands_on_the_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'teacher', 'password' => Hash::make('Secret-Password-1')]);
        Teacher::create([
            'user_id' => $user->id, 'first_name' => 'Sam', 'middle_name' => null, 'last_name' => 'Teacher',
            'sex' => 'Male', 'contact_number' => '09170000002', 'address' => 'Teacher address',
        ]);

        $this->post(route('auth.login.submit'), ['email' => $user->email, 'password' => 'Secret-Password-1'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_dashboard_cards_and_lists_link_to_their_pages(): void
    {
        $pending = $this->childWithRecord('Ana', 'pending');
        $inProgress = $this->childWithRecord('Ben', 'in_progress');

        $html = $this->actingAs($this->teacherUser)->get(route('dashboard'))->assertOk()->getContent();

        foreach ([
            route('guardians.index'),
            route('children.index'),
            route('progress.index', ['status' => 'completed']),
            route('progress.index', ['status' => 'unfinished']),
        ] as $href) {
            $this->assertStringContainsString('<a href="'.e($href).'" data-dashboard-stat', $html);
        }
        // Needs Attention: the pending record starts an evaluation, the in-progress one opens its record.
        $this->assertStringContainsString('href="'.e(route('progress.select-domain', ['child_id' => $pending->child_id])).'"', $html);
        $this->assertStringContainsString('href="'.e(route('progress.show', $inProgress->id)).'"', $html);
        // Recent Evaluations rows have a three-dot menu.
        $this->assertMatchesRegularExpression('/<a href="'.preg_quote(e(route('children.show', $pending->child_id)), '/').'" role="menuitem"/', $html);
    }

    public function test_the_progress_list_filters_by_record_status(): void
    {
        $this->childWithRecord('Ana', 'completed');
        $this->childWithRecord('Ben', 'in_progress');
        $this->childWithRecord('Cara', 'pending');

        $this->actingAs($this->teacherUser);

        $this->get(route('progress.index', ['status' => 'completed']))->assertOk()
            ->assertSee('Ana Child')->assertDontSee('Ben Child')->assertDontSee('Cara Child');
        $this->get(route('progress.index', ['status' => 'unfinished']))->assertOk()
            ->assertDontSee('Ana Child')->assertSee('Ben Child')->assertSee('Cara Child');
        $this->get(route('progress.index', ['status' => 'bogus']))->assertSessionHasErrors('status');
    }

    public function test_progress_links_without_an_id_return_to_the_progress_list(): void
    {
        $this->actingAs($this->teacherUser);

        foreach (['progress.show', 'progress.edit', 'progress.select-domain', 'progress.create', 'progress.add-observation'] as $route) {
            $this->get(route($route))
                ->assertRedirect(route('progress.index'))
                ->assertSessionHas('error');
        }
    }

    public function test_progress_records_are_opened_by_record_id_only(): void
    {
        $record = $this->childWithRecord('Ana', 'pending');
        $other = $this->childWithRecord('Ben', 'pending');
        $other->forceDelete();

        $this->actingAs($this->teacherUser);
        $this->get(route('progress.show', $record->id))->assertOk()->assertSee('Ana');
        // A deleted record's id must not fall back to a child with the same id.
        $this->get(route('progress.show', $other->id))->assertNotFound();
    }
}
