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

    public function test_an_archived_guardian_can_be_viewed_read_only_with_their_archived_children(): void
    {
        $this->archivedChild('Jordan');
        $this->guardian->delete();

        $this->actingAs($this->teacherUser)->get(route('guardians.show', $this->guardian->id))
            ->assertOk()
            ->assertSee('Casey')
            ->assertSee('Jordan')
            ->assertSee('Back to Archives')
            ->assertDontSee('Edit Details')
            ->assertDontSee('Link Child');
    }

    public function test_an_archived_child_can_be_viewed_read_only_from_the_archives(): void
    {
        $child = $this->archivedChild('Jordan');

        $this->actingAs($this->teacherUser)->get(route('archives.index', ['tab' => 'children']))
            ->assertOk()
            ->assertSee(route('children.show', $child->id));

        $this->actingAs($this->teacherUser)->get(route('children.show', $child->id))
            ->assertOk()
            ->assertSee('Jordan')
            ->assertSee('Back to Archives')
            ->assertDontSee('Edit Profile')
            ->assertDontSee('Archive Record')
            ->assertDontSee('Add Notes');
    }

    public function test_restoring_a_guardian_brings_back_the_children_archived_with_them(): void
    {
        $this->travelTo(now()->subDay());
        $archivedEarlier = $this->archivedChild('Avery');
        $this->travelBack();
        $child = Child::create([
            'guardian_id' => $this->guardian->id,
            'first_name' => 'Jordan',
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

        $this->actingAs($this->teacherUser)->delete(route('guardians.destroy', $this->guardian->id));
        $this->assertSoftDeleted($this->guardian);
        $this->assertSoftDeleted($child);

        $this->actingAs($this->teacherUser)
            ->patch(route('guardians.restore', $this->guardian->id))
            ->assertRedirect(route('archives.index'));

        $this->assertNotSoftDeleted($this->guardian);
        $this->assertNotSoftDeleted($child);
        $this->assertSoftDeleted($archivedEarlier);
        $this->actingAs($this->teacherUser)->get(route('guardians.index'))->assertOk()->assertSee('Casey');
    }

    public function test_other_teachers_cannot_view_archived_records(): void
    {
        $child = $this->archivedChild('Jordan');
        $this->guardian->delete();
        $otherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $otherUser->id,
            'first_name' => 'Other',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Male',
            'contact_number' => '09170000002',
            'address' => 'Other address',
        ]);

        $this->actingAs($otherUser)->get(route('guardians.show', $this->guardian->id))->assertNotFound();
        $this->actingAs($otherUser)->get(route('children.show', $child->id))->assertNotFound();
    }
}
