<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Row and record actions sit behind the shared three-dot <x-action-menu>, and the other buttons use the
 * shared <x-button>, so every button in the teacher portal has the same size and design.
 */
class ActionMenusTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Guardian $guardian;

    private Child $child;

    private ProgressRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
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
        $this->child = Child::create([
            'guardian_id' => $this->guardian->id,
            'first_name' => 'Jordan',
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2022-01-15',
            'address' => 'Balite, Rodriguez, Rizal, CALABARZON',
            'handedness' => 'right',
            'is_studying' => false,
        ]);
        $this->record = ProgressRecord::create([
            'child_id' => $this->child->id,
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_date_round_1' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'in_progress',
        ]);
    }

    /** The page has a closed ⋮ menu holding the given action links. */
    private function assertActionMenu(TestResponse $response, array $hrefs): void
    {
        $response->assertOk()
            ->assertSee('data-action-menu-trigger aria-haspopup="menu" aria-expanded="false"', false)
            ->assertSee('data-action-menu-panel role="menu"', false);

        $content = $response->getContent();
        foreach ($hrefs as $href) {
            $this->assertMatchesRegularExpression(
                '/<a href="'.preg_quote(e($href), '/').'" role="menuitem"/',
                $content,
                "{$href} should be an item of the three-dot menu."
            );
        }
    }

    public function test_list_rows_keep_their_actions_in_a_three_dot_menu(): void
    {
        $this->actingAs($this->teacherUser);

        $guardians = $this->get(route('guardians.index'));
        $this->assertActionMenu($guardians, [
            route('guardians.show', $this->guardian->id),
            route('guardians.edit', $this->guardian->id),
            route('guardians.create-child', $this->guardian->id),
            route('guardians.archive-child', $this->guardian->id),
        ]);
        // Archive stays a confirmed DELETE form, now inside the menu.
        $guardians->assertSee('action="'.route('guardians.destroy', $this->guardian->id).'"', false)
            ->assertSee('data-confirm="Are you sure you want to archive Casey Guardian', false);

        $this->assertActionMenu($this->get(route('children.index')), [
            route('children.show', $this->child->id),
            route('children.edit', $this->child->id),
        ]);

        $this->assertActionMenu($this->get(route('progress.index')), [
            route('progress.show', $this->record->id),
            route('progress.edit', $this->record->id),
            route('progress.select-domain', ['child_id' => $this->child->id]),
            route('progress.add-observation', ['child_id' => $this->child->id]),
        ]);

        $this->get(route('guardians.archive-child', $this->guardian->id))->assertOk()
            ->assertSee('data-action-menu-trigger', false)
            ->assertSee('action="'.route('guardians.unlink-child', ['guardianId' => $this->guardian->id, 'childId' => $this->child->id]).'"', false);
    }

    public function test_record_pages_keep_their_actions_in_a_three_dot_menu(): void
    {
        $this->actingAs($this->teacherUser);

        $this->assertActionMenu($this->get(route('guardians.show', $this->guardian->id)), [
            route('guardians.edit', $this->guardian->id),
            route('guardians.create-child', $this->guardian->id),
        ]);
        $this->assertActionMenu($this->get(route('children.show', $this->child->id)), [
            route('children.edit', $this->child->id),
        ]);
        $this->assertActionMenu($this->get(route('progress.show', $this->record->id)), [
            route('progress.edit', $this->record->id),
            route('progress.download-pdf', $this->record->id),
        ]);
    }

    public function test_archived_rows_restore_from_the_three_dot_menu(): void
    {
        $this->guardian->delete();
        $this->child->delete();

        $archives = $this->actingAs($this->teacherUser)->get(route('archives.index', ['tab' => 'children']));

        $archives->assertOk()
            ->assertSee('data-action-menu-trigger', false)
            ->assertSee('action="'.route('children.restore', $this->child->id).'"', false)
            ->assertSee('role="menuitem"', false);
    }

    public function test_buttons_share_one_size_and_the_menu_script_is_loaded(): void
    {
        $page = $this->actingAs($this->teacherUser)->get(route('guardians.index'));

        // <x-button> (Add Guardian) and the ⋮ trigger are both 40px tall (h-10) and use the same radius.
        $page->assertOk()
            ->assertSee('inline-flex h-10 shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl', false)
            ->assertSee('inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl', false)
            ->assertSee('function openActionMenu', false)
            ->assertDontSee('px-3 py-1.5 bg-blue-100', false);

        // The menu script runs under the page's CSP nonce like every other inline script.
        $nonce = $page->baseResponse->headers->get('Content-Security-Policy');
        $this->assertMatchesRegularExpression("/'nonce-([^']+)'/", $nonce);
        preg_match("/'nonce-([^']+)'/", $nonce, $match);
        $this->assertStringContainsString('<script nonce="'.$match[1].'">', $page->getContent());
    }
}
