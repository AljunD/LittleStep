<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->teacherUser, $this->teacher] = $this->makeTeacher('Taylor');
    }

    private function makeTeacher(string $firstName): array
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Teacher address',
        ]);

        return [$user, $teacher];
    }

    private function makeGuardian(string $firstName, string $email): Guardian
    {
        return Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian', 'email' => $email])->id,
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);
    }

    private function makeChild(Guardian $guardian, string $firstName, string $sex = 'Female', ?Teacher $teacher = null): Child
    {
        $child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => $firstName,
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => $sex,
            'date_of_birth' => '2022-01-15',
            'address' => 'Child address',
            'handedness' => 'right',
            'is_studying' => false,
        ]);
        ProgressRecord::create([
            'child_id' => $child->id,
            'teacher_id' => ($teacher ?? $this->teacher)->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'pending',
        ]);

        return $child;
    }

    public function test_guardian_search_matches_name_and_email_within_the_teachers_guardians_only(): void
    {
        $this->makeChild($this->makeGuardian('Casey', 'casey@example.com'), 'Jordan');
        $this->makeChild($this->makeGuardian('Morgan', 'morgan@example.com'), 'Riley');
        [, $otherTeacher] = $this->makeTeacher('Other');
        $this->makeChild($this->makeGuardian('Casey', 'other-casey@example.com'), 'Sam', 'Male', $otherTeacher);

        $this->actingAs($this->teacherUser)->get(route('guardians.index', ['search' => 'casey guardian']))
            ->assertOk()
            ->assertSee('casey@example.com')
            ->assertDontSee('morgan@example.com')
            ->assertDontSee('other-casey@example.com');

        $this->get(route('guardians.index', ['search' => 'morgan@example']))
            ->assertOk()
            ->assertSee('morgan@example.com')
            ->assertDontSee('casey@example.com');
    }

    public function test_children_can_be_searched_and_filtered_by_sex(): void
    {
        $guardian = $this->makeGuardian('Casey', 'casey@example.com');
        $this->makeChild($guardian, 'Jordan', 'Female');
        $this->makeChild($guardian, 'Riley', 'Male');

        $this->actingAs($this->teacherUser)->get(route('children.index', ['search' => 'jor']))
            ->assertOk()
            ->assertSee('Jordan')
            ->assertDontSee('Riley');

        $this->get(route('children.index', ['sex' => 'Male']))
            ->assertOk()
            ->assertSee('Riley')
            ->assertDontSee('Jordan');

        $this->get(route('children.index', ['sex' => 'Unknown']))
            ->assertSessionHasErrors('sex');
    }

    public function test_progress_search_and_wildcards_are_matched_literally(): void
    {
        $guardian = $this->makeGuardian('Casey', 'casey@example.com');
        $this->makeChild($guardian, 'Jordan');
        $this->makeChild($guardian, 'Riley');

        $this->actingAs($this->teacherUser)->get(route('progress.index', ['search' => 'riley']))
            ->assertOk()
            ->assertSee('Riley')
            ->assertDontSee('Jordan');

        $this->get(route('progress.index', ['search' => '%']))
            ->assertOk()
            ->assertDontSee('Riley')
            ->assertSee('No children match your search.');
    }

    public function test_archive_search_keeps_the_active_tab(): void
    {
        $guardian = $this->makeGuardian('Casey', 'casey@example.com');
        $this->makeChild($guardian, 'Jordan')->delete();
        $this->makeChild($guardian, 'Riley')->delete();

        $this->actingAs($this->teacherUser)->get(route('archives.index', ['tab' => 'children', 'search' => 'jordan']))
            ->assertOk()
            ->assertSee('<input type="hidden" name="tab" value="children">', false)
            ->assertSee('Jordan')
            ->assertDontSee('Riley');
    }

    public function test_overlong_search_terms_are_rejected(): void
    {
        $this->actingAs($this->teacherUser)
            ->get(route('guardians.index', ['search' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('search');
    }

    public function test_index_pages_share_the_search_bar_and_pagination(): void
    {
        $guardian = $this->makeGuardian('Casey', 'casey@example.com');
        foreach (range(1, 11) as $number) {
            $this->makeChild($guardian, 'Child'.$number);
        }

        $this->actingAs($this->teacherUser);

        foreach (['children.index', 'progress.index'] as $route) {
            $this->get(route($route, ['search' => 'child']))
                ->assertOk()
                ->assertSee('role="search"', false)
                ->assertSee('data-pagination', false)
                ->assertSee('aria-current="page"', false)
                ->assertSee('search=child&amp;page=2', false);
        }

        foreach (['guardians.index', 'archives.index', 'logs.index'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('role="search"', false);
        }
    }

    public function test_system_logs_page_has_no_back_to_dashboard_button(): void
    {
        $this->actingAs($this->teacherUser)->get(route('logs.index'))
            ->assertOk()
            ->assertDontSee('Back to Dashboard');
    }
}
