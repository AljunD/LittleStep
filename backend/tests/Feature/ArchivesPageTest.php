<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivesPageTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    private Guardian $guardian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        $this->teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Teacher address',
        ]);
        $this->guardian = Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian'])->id,
            'first_name' => 'Casey',
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);
    }

    private function archivedChild(string $firstName): Child
    {
        $child = Child::create([
            'guardian_id' => $this->guardian->id,
            'first_name' => $firstName,
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
            'teacher_id' => $this->teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'pending',
        ]);
        $child->delete();

        return $child;
    }

    private function isPanelHidden(string $html, string $panel): bool
    {
        return (bool) preg_match('/data-archive-panel="'.$panel.'"\s+hidden\b/', $html);
    }

    public function test_guardians_tab_is_open_by_default_and_alpine_is_not_required(): void
    {
        $html = $this->actingAs($this->teacherUser)->get(route('archives.index'))
            ->assertOk()
            ->assertDontSee('x-data', false)
            ->assertDontSee('x-show', false)
            ->assertDontSee('@click', false)
            ->getContent();

        $this->assertFalse($this->isPanelHidden($html, 'guardians'));
        $this->assertTrue($this->isPanelHidden($html, 'children'));
        $this->assertStringContainsString('id="archive-tab-guardians" data-archive-tab="guardians"', $html);
        $this->assertMatchesRegularExpression('/id="archive-tab-guardians"[^>]*aria-selected="true"/', $html);
    }

    public function test_children_tab_opens_from_the_query_string_and_unknown_tabs_fall_back(): void
    {
        $html = $this->actingAs($this->teacherUser)->get(route('archives.index', ['tab' => 'children']))
            ->assertOk()
            ->getContent();

        $this->assertTrue($this->isPanelHidden($html, 'guardians'));
        $this->assertFalse($this->isPanelHidden($html, 'children'));

        $fallback = $this->actingAs($this->teacherUser)->get(route('archives.index', ['tab' => 'unknown']))
            ->assertOk()
            ->getContent();

        $this->assertFalse($this->isPanelHidden($fallback, 'guardians'));
    }

    public function test_restoring_a_child_returns_to_the_children_tab(): void
    {
        $child = $this->archivedChild('Jordan');

        $this->actingAs($this->teacherUser)
            ->patch(route('children.restore', $child->id))
            ->assertRedirect(route('archives.index', ['tab' => 'children']));

        $this->assertNotSoftDeleted($child);
    }

    public function test_children_pagination_links_keep_the_children_tab(): void
    {
        foreach (range(1, 11) as $number) {
            $this->archivedChild('Child'.$number);
        }

        $html = $this->actingAs($this->teacherUser)->get(route('archives.index', ['tab' => 'children']))
            ->assertOk()
            ->getContent();

        $this->assertMatchesRegularExpression('/href="[^"]*tab=children[^"]*children_page=2/', html_entity_decode($html));
    }
}
