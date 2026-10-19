<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Domain;
use App\Models\DomainScore;
use App\Models\EccdChecklistItem;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use App\Services\DashboardInsightsService;
use App\Services\EccdExpertSystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardInsightsTest extends TestCase
{
    use RefreshDatabase;

    private array $items = [];

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-19 09:00:00');

        foreach (array_keys(EccdExpertSystem::DOMAIN_LABELS) as $domain) {
            $this->items[$domain] = EccdChecklistItem::create(['domain' => $domain, 'activity' => 'Item', 'item_number' => 1]);
        }
    }

    private function makeTeacher(string $firstName): array
    {
        /** @var User $user */
        $user = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $user->id, 'first_name' => $firstName, 'middle_name' => null, 'last_name' => 'Teacher',
            'sex' => 'Female', 'contact_number' => '09170000000', 'address' => 'Teacher address',
        ]);

        return [$user, $teacher];
    }

    /** A record whose rounds are given as [round => [evaluation date, interpretation code, scaled score for every domain]]. */
    private function scoredChild(Teacher $teacher, string $firstName, array $rounds): ProgressRecord
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
        $record = ProgressRecord::create(['child_id' => $child->id, 'teacher_id' => $teacher->id, 'evaluation_date' => $rounds[1][0], 'evaluation_number' => count($rounds), 'status' => 'completed']);

        foreach ($rounds as $round => [$date, $code, $scaled]) {
            $record->update(["evaluation_date_round_{$round}" => $date]);
            foreach ($this->items as $item) {
                $domain = Domain::create(['progress_record_id' => $record->id, 'checklist_item_id' => $item->id, 'evaluation_round' => $round]);
                DomainScore::create([
                    'progress_record_id' => $record->id, 'domain_id' => $domain->id, 'raw_score' => 1,
                    'scaled_score' => $scaled, 'standard_score' => 100, 'interpretation_code' => $code,
                ]);
            }
        }

        return $record;
    }

    public function test_the_dashboard_shows_class_insights_instead_of_recent_activity(): void
    {
        [$user] = $this->makeTeacher('Taylor');

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Recent Activity')
            ->assertSee('Class Insights')
            ->assertSee('No scored evaluations yet.');
    }

    public function test_insights_use_each_childs_latest_round_and_only_the_teachers_active_records(): void
    {
        [$user, $teacher] = $this->makeTeacher('Taylor');
        [, $otherTeacher] = $this->makeTeacher('Other');

        // Average in round 1, then significant delay in round 2: only round 2 counts. Re-check is due 3 months after it.
        $ana = $this->scoredChild($teacher, 'Ana', [1 => ['2026-03-01', 'average', 10], 2 => ['2026-06-01', 'monitor_3m', 3]]);
        // Slight delay evaluated recently: re-check due in 6 months, not overdue.
        $ben = $this->scoredChild($teacher, 'Ben', [1 => ['2026-09-01', 'monitor_6m', 5]]);
        $this->scoredChild($teacher, 'Cara', [1 => ['2026-09-15', 'slightly_advanced', 15]]);
        $this->scoredChild($otherTeacher, 'Dana', [1 => ['2026-01-01', 'monitor_3m', 2]]);
        $this->scoredChild($teacher, 'Eli', [1 => ['2026-01-01', 'monitor_3m', 2]])->delete();

        $insights = app(DashboardInsightsService::class)->for($teacher);

        $this->assertSame(3, $insights['scored_children']);
        $this->assertSame(
            ['monitor_3m' => 1, 'monitor_6m' => 1, 'average' => 0, 'slightly_advanced' => 1, 'highly_advanced' => 0],
            array_map(fn (array $band) => $band['count'], $insights['overall'])
        );

        $domain = $insights['domains'][0];
        $this->assertSame(3, $domain['scored']);
        $this->assertSame(2, $domain['needs_support']);
        $this->assertSame(round((3 + 5 + 15) / 3, 1), $domain['average']);

        $this->assertSame(2, $insights['follow_ups_total']);
        [$first, $second] = $insights['follow_ups']->all();
        $this->assertSame([$ana->id, '2026-09-01', true], [$first->record_id, $first->due_on->toDateString(), $first->overdue]);
        $this->assertSame([$ben->id, '2027-03-01', false], [$second->record_id, $second->due_on->toDateString(), $second->overdue]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Based on the latest scored evaluation of 3 children.')
            ->assertSee('2 of 3 need support')
            ->assertSee('Overdue · Sep 01, 2026')
            ->assertSee('Due Mar 01, 2027')
            ->assertSee(route('progress.show', $ana->id))
            ->assertDontSee('Dana Child')
            ->assertDontSee('Eli Child');
    }
}
