<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Domain;
use App\Models\DomainResult;
use App\Models\EccdChecklistItem;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GuardianDomainApiTest extends TestCase
{
    use RefreshDatabase;

    private function createGuardianWithChild(string $email): array
    {
        $user = User::factory()->create([
            'email' => $email,
            'role' => 'guardian',
            'email_verified_at' => now(),
        ]);
        $guardian = Guardian::factory()->create([
            'user_id' => $user->id,
            'sex' => 'Female',
            'contact_number' => '09171234567',
            'address' => 'Guardian address',
        ]);
        $child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => 'Jordan',
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2022-01-10',
            'address' => 'Private address',
            'handedness' => 'right',
            'is_studying' => false,
        ]);

        return [$user, $child];
    }

    private function createProgressRecord(Child $child): ProgressRecord
    {
        $teacher = Teacher::create([
            'user_id' => User::factory()->create(['role' => 'teacher'])->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '555-0100',
            'address' => 'Teacher address',
        ]);

        return ProgressRecord::create([
            'child_id' => $child->id,
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_date_round_1' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'in_progress',
        ]);
    }

    private function recordResult(ProgressRecord $record, EccdChecklistItem $item, string $present, ?string $comments = null): void
    {
        $domain = Domain::create([
            'progress_record_id' => $record->id,
            'checklist_item_id' => $item->id,
            'evaluation_round' => 1,
            'status' => 'completed',
        ]);
        DomainResult::create([
            'domain_id' => $domain->id,
            'present' => $present,
            'comments' => $comments,
        ]);
    }

    public function test_guardian_sees_checklist_results_for_a_domain_of_their_child(): void
    {
        [$user, $child] = $this->createGuardianWithChild('guardian@example.com');
        $record = $this->createProgressRecord($child);
        $climbs = EccdChecklistItem::create(['domain' => 'gross_motor', 'activity' => 'Climbs on chair', 'item_number' => 1]);
        $walks = EccdChecklistItem::create(['domain' => 'gross_motor', 'activity' => 'Walks backwards', 'item_number' => 2]);
        $kicks = EccdChecklistItem::create(['domain' => 'gross_motor', 'activity' => 'Kicks a ball', 'item_number' => 3]);
        EccdChecklistItem::create(['domain' => 'fine_motor', 'activity' => 'Uses fingers to pick up', 'item_number' => 1]);
        $this->recordResult($record, $climbs, 'check');
        $this->recordResult($record, $walks, 'hyphen', 'Needs support');

        Sanctum::actingAs($user, ['*']);

        $this->getJson("/api/v1/guardian/children/{$child->id}/domains/gross_motor")
            ->assertOk()
            ->assertJsonPath('domain.key', 'gross_motor')
            ->assertJsonPath('domain.label', 'Gross Motor')
            ->assertJsonPath('progress.latest_round', 1)
            ->assertJsonPath('progress.rounds.1.assessed_items', 2)
            ->assertJsonPath('progress.rounds.2.assessed_items', 0)
            ->assertJsonCount(3, 'items')
            ->assertJsonPath('items.0.activity', 'Climbs on chair')
            ->assertJsonPath('items.0.results.1.present', 'check')
            ->assertJsonPath('items.1.results.1.present', 'hyphen')
            ->assertJsonPath('items.1.results.1.comments', 'Needs support')
            ->assertJsonPath('items.2.id', $kicks->id)
            ->assertJsonPath('items.2.results.1', null)
            ->assertJsonMissingPath('child.address');
    }

    public function test_domain_detail_is_scoped_to_the_guardians_own_children_and_known_domains(): void
    {
        [$user, $child] = $this->createGuardianWithChild('guardian@example.com');
        [, $otherChild] = $this->createGuardianWithChild('other@example.com');

        Sanctum::actingAs($user, ['*']);

        $this->getJson("/api/v1/guardian/children/{$otherChild->id}/domains/gross_motor")
            ->assertNotFound();
        $this->getJson("/api/v1/guardian/children/{$child->id}/domains/not_a_domain")
            ->assertNotFound();

        // No progress record yet: empty results, not an error.
        $this->getJson("/api/v1/guardian/children/{$child->id}/domains/self_help")
            ->assertOk()
            ->assertJsonPath('domain.label', 'Self-Help')
            ->assertJsonPath('progress.status', 'not_started')
            ->assertJsonPath('progress.last_updated_at', null)
            ->assertJsonPath('progress.rounds.1.assessed_items', 0);
    }

    public function test_dashboard_lists_all_domains_with_the_most_recently_updated_first(): void
    {
        [$user, $child] = $this->createGuardianWithChild('guardian@example.com');
        $record = $this->createProgressRecord($child);
        $grossItem = EccdChecklistItem::create(['domain' => 'gross_motor', 'activity' => 'Climbs on chair', 'item_number' => 1]);
        $fineItem = EccdChecklistItem::create(['domain' => 'fine_motor', 'activity' => 'Uses fingers to pick up', 'item_number' => 1]);

        $this->recordResult($record, $grossItem, 'check');
        $this->travel(5)->minutes();
        $this->recordResult($record, $fineItem, 'check');

        Sanctum::actingAs($user, ['*']);

        $this->getJson("/api/v1/guardian/children/{$child->id}/dashboard")
            ->assertOk()
            ->assertJsonCount(7, 'progress.recent_activity')
            ->assertJsonPath('progress.recent_activity.0.domain', 'fine_motor')
            ->assertJsonPath('progress.recent_activity.1.domain', 'gross_motor')
            // Untouched domains follow in the official order.
            ->assertJsonPath('progress.recent_activity.2.domain', 'self_help')
            ->assertJsonPath('progress.recent_activity.2.last_updated_at', null)
            ->assertJsonPath('progress.rounds.1.domains.social_emotional.label', 'Social-Emotional');
    }
}
