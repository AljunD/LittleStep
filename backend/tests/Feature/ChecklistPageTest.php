<?php

namespace Tests\Feature;

use App\Models\EccdChecklistItem;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChecklistPageTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private EccdChecklistItem $grossMotorItem;

    private EccdChecklistItem $cognitiveItem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $this->teacherUser->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Balite, Rodriguez, Rizal, CALABARZON',
        ]);

        // Created out of order to prove the list follows the official domain order.
        $this->cognitiveItem = EccdChecklistItem::create([
            'domain' => 'cognitive',
            'activity' => 'Sorts objects by color',
            'materials_and_procedure' => 'Colored blocks',
            'item_number' => 1,
        ]);
        $this->grossMotorItem = EccdChecklistItem::create([
            'domain' => 'gross_motor',
            'activity' => 'Climbs on chair',
            'materials_and_procedure' => null,
            'item_number' => 1,
        ]);
    }

    private function update(EccdChecklistItem $item, array $data)
    {
        return $this->actingAs($this->teacherUser)
            ->from(route('checklist.edit', $item))
            ->put(route('checklist.update', $item), $data);
    }

    public function test_checklist_lists_items_in_official_order_and_sits_between_progress_and_archives(): void
    {
        $html = $this->actingAs($this->teacherUser)->get(route('checklist.index'))
            ->assertOk()
            ->assertSeeInOrder(['Gross Motor', 'Climbs on chair', 'Cognitive', 'Sorts objects by color'])
            ->assertSee('Parental report will suffice.')
            ->assertSee('href="'.route('checklist.edit', $this->grossMotorItem).'"', false)
            ->getContent();

        $this->assertMatchesRegularExpression('/href="'.preg_quote(route('checklist.index'), '/').'"\s+aria-current="page"/', $html);
        $this->assertMatchesRegularExpression(
            '/'.preg_quote(route('progress.index'), '/').'.*'.preg_quote(route('checklist.index'), '/').'.*'.preg_quote(route('archives.index'), '/').'/s',
            $html
        );
    }

    public function test_checklist_can_be_searched_and_filtered_by_domain(): void
    {
        $this->actingAs($this->teacherUser);

        $this->get(route('checklist.index', ['search' => 'blocks']))
            ->assertOk()->assertSee('Sorts objects by color')->assertDontSee('Climbs on chair');

        $this->get(route('checklist.index', ['domain' => 'gross_motor']))
            ->assertOk()->assertSee('Climbs on chair')->assertDontSee('Sorts objects by color');

        $this->get(route('checklist.index', ['domain' => 'not_a_domain']))->assertSessionHasErrors('domain');
    }

    public function test_edit_form_shows_the_official_domain_and_number_as_read_only(): void
    {
        $html = $this->actingAs($this->teacherUser)->get(route('checklist.edit', $this->cognitiveItem))
            ->assertOk()
            ->assertSee('id="editChecklistItemForm"', false)
            ->assertSee('value="Sorts objects by color"', false)
            ->assertSee('Colored blocks')
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/name="(domain|item_number)"/', $html);
    }

    public function test_teacher_can_fix_an_items_wording_but_not_its_domain_or_number(): void
    {
        $this->update($this->grossMotorItem, [
            'activity' => 'Climbs on a chair',
            'materials_and_procedure' => "Chair\nAsk the child to climb.",
            'domain' => 'cognitive',
            'item_number' => 99,
        ])
            ->assertRedirect(route('checklist.index', ['domain' => 'gross_motor']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Checklist item updated.');

        $item = $this->grossMotorItem->refresh();
        $this->assertSame(['Climbs on a chair', "Chair\nAsk the child to climb.", 'gross_motor', 1], [
            $item->activity, $item->materials_and_procedure, $item->domain, (int) $item->item_number,
        ]);
        $this->assertDatabaseHas('logs', [
            'user_id' => $this->teacherUser->id,
            'action' => 'updated',
            'entity_type' => 'ChecklistItem',
            'entity_id' => $item->id,
            'details' => 'Checklist item updated: Gross Motor #1 (activity, materials and procedure).',
        ]);
        $this->assertSame(2, EccdChecklistItem::count());
    }

    public function test_saving_unchanged_wording_does_nothing(): void
    {
        $this->update($this->cognitiveItem, ['activity' => 'Sorts objects by color', 'materials_and_procedure' => 'Colored blocks'])
            ->assertRedirect(route('checklist.index', ['domain' => 'cognitive']))
            ->assertSessionHas('status', 'No changes to save.');

        $this->assertDatabaseMissing('logs', ['entity_type' => 'ChecklistItem']);
    }

    public function test_wording_is_validated(): void
    {
        $this->update($this->cognitiveItem, ['activity' => '', 'materials_and_procedure' => 'Colored blocks'])
            ->assertRedirect(route('checklist.edit', $this->cognitiveItem))
            ->assertSessionHasErrors('activity');

        $this->update($this->cognitiveItem, ['activity' => str_repeat('a', 256)])->assertSessionHasErrors('activity');
        $this->update($this->cognitiveItem, ['activity' => 'Sorts', 'materials_and_procedure' => str_repeat('a', 5001)])
            ->assertSessionHasErrors('materials_and_procedure');

        $this->assertSame('Sorts objects by color', $this->cognitiveItem->refresh()->activity);
    }

    public function test_archived_items_cannot_be_edited(): void
    {
        $this->cognitiveItem->delete();

        $this->actingAs($this->teacherUser)->get(route('checklist.edit', $this->cognitiveItem))->assertNotFound();
        $this->update($this->cognitiveItem, ['activity' => 'Changed'])->assertNotFound();
    }

    public function test_only_signed_in_teachers_can_use_the_checklist(): void
    {
        $this->get(route('checklist.index'))->assertRedirect(route('login'));
        $this->put(route('checklist.update', $this->cognitiveItem), ['activity' => 'Changed'])->assertRedirect(route('login'));

        /** @var User $guardian */
        $guardian = User::factory()->create(['role' => 'guardian']);
        $this->actingAs($guardian)->get(route('checklist.index'))->assertForbidden();
        $this->actingAs($guardian)->get(route('checklist.edit', $this->cognitiveItem))->assertForbidden();
        $this->actingAs($guardian)->put(route('checklist.update', $this->cognitiveItem), ['activity' => 'Changed'])->assertForbidden();

        $this->assertSame('Sorts objects by color', $this->cognitiveItem->refresh()->activity);
    }
}
