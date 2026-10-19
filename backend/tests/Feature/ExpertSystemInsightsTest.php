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
use App\Services\EccdExpertSystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpertSystemInsightsTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    private User $guardianUser;

    private Child $child;

    private ProgressRecord $record;

    private array $items = [];

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
        $this->guardianUser = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
        $guardian = Guardian::create([
            'user_id' => $this->guardianUser->id,
            'first_name' => 'Casey',
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);
        $this->child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => 'Jordan',
            'middle_name' => null,
            'last_name' => 'Child',
            'sex' => 'Female',
            'date_of_birth' => '2022-01-10',
            'address' => 'Child address',
            'handedness' => 'right',
            'is_studying' => false,
        ]);
        $this->record = ProgressRecord::create([
            'child_id' => $this->child->id,
            'teacher_id' => $this->teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_date_round_1' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'in_progress',
        ]);

        foreach (array_keys(EccdExpertSystem::DOMAIN_LABELS) as $domainKey) {
            foreach ([1, 2] as $number) {
                $this->items[$domainKey][] = EccdChecklistItem::create([
                    'domain' => $domainKey,
                    'activity' => "{$domainKey} activity {$number}",
                    'item_number' => $number,
                ]);
            }
        }
    }

    private function answer(EccdChecklistItem $item, string $present, int $round = 1): void
    {
        $domain = Domain::create([
            'progress_record_id' => $this->record->id,
            'checklist_item_id' => $item->id,
            'evaluation_round' => $round,
            'status' => 'completed',
        ]);
        DomainResult::create(['domain_id' => $domain->id, 'present' => $present]);
    }

    public function test_the_explanation_matches_the_computed_scores_step_by_step(): void
    {
        [$first, $second] = $this->items['gross_motor'];
        $this->answer($first, 'check');
        $this->answer($second, 'hyphen');
        $this->answer($this->items['fine_motor'][0], 'check');

        $expert = app(EccdExpertSystem::class);
        $computed = $expert->compute($this->record->fresh(), 1);
        $explanation = $expert->explainRound($this->record->fresh(), 1);

        foreach (array_keys(EccdExpertSystem::DOMAIN_LABELS) as $domainKey) {
            $this->assertSame($computed['raw_scores'][$domainKey], $explanation['domains'][$domainKey]['raw_score']);
            $this->assertSame($computed['scaled_scores'][$domainKey], $explanation['domains'][$domainKey]['scaled_score']);
        }

        $this->assertSame(1, $explanation['domains']['gross_motor']['raw_score']);
        $this->assertSame(['2/2', '1/2'], [
            $explanation['domains']['gross_motor']['items_answered'].'/'.$explanation['domains']['gross_motor']['items_total'],
            $explanation['domains']['fine_motor']['items_answered'].'/'.$explanation['domains']['fine_motor']['items_total'],
        ]);
        $this->assertNull($explanation['domains']['fine_motor']['raw_score']);
        $this->assertSame('4.1–5.0', $explanation['age']['band']);
        $this->assertSame(1, $explanation['scored_domains']);
        $this->assertCount(6, $explanation['steps']);
        $this->assertStringContainsString('every month counted as 30 days', $explanation['steps'][0]);
        $this->assertStringContainsString('Not all items are answered yet', implode(' ', $explanation['warnings']));
        $this->assertNull($explanation['standard_score']);
    }

    public function test_the_explanation_warns_when_the_age_is_outside_childs_record_2(): void
    {
        $this->child->update(['date_of_birth' => '2025-01-01']);
        $this->answer($this->items['gross_motor'][0], 'check');

        $explanation = app(EccdExpertSystem::class)->explainRound($this->record->fresh(), 1);

        $this->assertNull($explanation['age']['band']);
        $this->assertStringContainsString("outside Child's Record 2", implode(' ', $explanation['warnings']));
    }

    public function test_domain_interpretations_use_the_technical_manual_table_6_wording(): void
    {
        $expert = app(EccdExpertSystem::class);

        $this->assertSame('Suggest significant delay', $expert->interpretScaledScore(2));
        $this->assertSame('Suggest slight delay', $expert->interpretScaledScore(5));
        $this->assertSame('Average development', $expert->interpretScaledScore(10));
        $this->assertSame('Suggest slightly advanced development', $expert->interpretScaledScore(15));
        $this->assertSame('Suggest highly advanced development', $expert->interpretScaledScore(18));
        $this->assertSame('Suggest slight delay', $expert->interpret(null, ['gross_motor' => 4])['per_domain']['gross_motor']);
    }

    public function test_skills_to_practise_come_from_the_latest_round_each_domain_was_assessed(): void
    {
        [$first, $second] = $this->items['gross_motor'];
        $this->answer($first, 'hyphen', 1);
        $this->answer($second, 'check', 1);
        $this->answer($first, 'check', 2);
        $this->answer($second, 'hyphen', 2);
        $this->answer($this->items['cognitive'][0], 'check', 1);

        $skills = app(EccdExpertSystem::class)->skillsToPractice($this->record);

        $this->assertSame(['gross_motor'], array_keys($skills));
        $this->assertSame(2, $skills['gross_motor']['round']);
        $this->assertSame(['gross_motor activity 2'], array_column($skills['gross_motor']['items'], 'activity'));
    }

    public function test_the_live_preview_scores_the_form_without_saving_and_matches_what_is_saved(): void
    {
        $this->answer($this->items['fine_motor'][0], 'check');
        $this->answer($this->items['fine_motor'][1], 'check');
        [$first, $second] = $this->items['gross_motor'];
        $marks = [$first->id => [1 => 'check'], $second->id => [1 => 'hyphen']];
        $domainRowsBefore = Domain::count();

        $preview = $this->actingAs($this->teacherUser)->postJson(route('progress.preview'), [
            'child_id' => $this->child->id,
            'domain' => 'gross_motor',
            'assessment_date' => '2026-10-01',
            'results' => $marks,
        ])->assertOk()
            ->assertJsonPath('round', 1)
            ->assertJsonPath('domain.key', 'gross_motor')
            ->assertJsonPath('domain.items_answered', 2)
            ->assertJsonPath('domain.raw_score', 1)
            ->assertJsonPath('explanation.domains.fine_motor.raw_score', 2)
            ->assertJsonPath('explanation.scored_domains', 2)
            ->json();

        $this->assertSame($domainRowsBefore, Domain::count(), 'The preview must not save anything.');

        $this->actingAs($this->teacherUser)->post(route('progress.store'), [
            'child_id' => $this->child->id,
            'domain' => 'gross_motor',
            'assessment_date' => '2026-10-01',
            'results' => $marks,
        ])->assertRedirect();

        $saved = app(EccdExpertSystem::class)->compute($this->record->fresh(), 1);
        $this->assertSame($saved['raw_scores']['gross_motor'], $preview['domain']['raw_score']);
        $this->assertSame($saved['scaled_scores']['gross_motor'], $preview['domain']['scaled_score']);
    }

    public function test_the_live_preview_is_limited_to_the_teachers_own_children_and_valid_input(): void
    {
        $otherTeacher = User::factory()->create(['role' => 'teacher']);
        Teacher::create([
            'user_id' => $otherTeacher->id,
            'first_name' => 'Other',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Male',
            'contact_number' => '09170000009',
            'address' => 'Elsewhere',
        ]);

        $this->actingAs($otherTeacher)->postJson(route('progress.preview'), [
            'child_id' => $this->child->id,
            'domain' => 'gross_motor',
        ])->assertNotFound();

        $this->actingAs($this->teacherUser)->postJson(route('progress.preview'), [
            'child_id' => $this->child->id,
            'domain' => 'not_a_domain',
            'results' => [$this->items['gross_motor'][0]->id => [1 => 'maybe']],
        ])->assertUnprocessable()->assertJsonValidationErrors(['domain', 'results.'.$this->items['gross_motor'][0]->id.'.1']);

        $this->actingAs($this->teacherUser)->postJson(route('progress.preview'), [
            'child_id' => $this->child->id,
            'domain' => 'gross_motor',
            'assessment_date' => '2021-01-01',
        ])->assertUnprocessable()->assertJsonValidationErrors('assessment_date');
    }

    public function test_the_evaluation_form_and_record_page_show_the_interactive_sections(): void
    {
        $this->answer($this->items['gross_motor'][0], 'check');
        $this->answer($this->items['gross_motor'][1], 'hyphen');

        $this->actingAs($this->teacherUser)
            ->get(route('progress.create', ['child_id' => $this->child->id, 'domain' => 'gross_motor']))
            ->assertOk()
            ->assertSee('id="live-preview"', false)
            ->assertSee(route('progress.preview'), false);

        $this->actingAs($this->teacherUser)
            ->get(route('progress.show', $this->record->id))
            ->assertOk()
            ->assertSee('How Was This Computed?')
            ->assertSee('every month counted as 30 days')
            ->assertSee('Focus Items for the Next Evaluation')
            ->assertSee('gross_motor activity 2');
    }

    public function test_edit_record_is_reachable_from_the_list_the_record_page_and_each_section(): void
    {
        $edit = route('progress.edit', $this->record->id);

        $this->actingAs($this->teacherUser)
            ->get(route('progress.index'))
            ->assertOk()
            ->assertSee('href="'.$edit.'"', false);

        $record = $this->actingAs($this->teacherUser)->get(route('progress.show', $this->record->id))->assertOk();
        $record->assertSee('Edit Record')
            ->assertSee('href="'.$edit.'#assessment-dates"', false)
            ->assertSee('href="'.$edit.'#observation-notes"', false);
        foreach (array_keys(EccdExpertSystem::DOMAIN_LABELS) as $domainKey) {
            $record->assertSee('href="'.$edit.'#domain-'.$domainKey.'"', false);
        }

        $form = $this->actingAs($this->teacherUser)->get($edit)->assertOk();
        $form->assertSee('id="assessment-dates"', false)->assertSee('id="observation-notes"', false);
        foreach (array_keys(EccdExpertSystem::DOMAIN_LABELS) as $domainKey) {
            $form->assertSee('id="domain-'.$domainKey.'"', false);
        }
    }

    public function test_the_record_page_and_the_list_show_the_same_evaluation_status(): void
    {
        foreach ($this->items['gross_motor'] as $item) {
            $this->answer($item, 'check');
        }

        $this->actingAs($this->teacherUser)
            ->get(route('progress.show', $this->record->id))
            ->assertOk()
            ->assertSee('1st Evaluation · In Progress')
            ->assertSee('1/7 domains completed')
            ->assertSee('October 01, 2026')
            ->assertSee('1st of 3')
            ->assertDontSee('In Progress View');

        $this->actingAs($this->teacherUser)
            ->get(route('progress.index'))
            ->assertOk()
            ->assertSee('In Progress')
            ->assertSee('1/7 domains completed')
            ->assertSee('2026-10-01');

        foreach (array_keys(EccdExpertSystem::DOMAIN_LABELS) as $domainKey) {
            foreach ($this->items[$domainKey] as $item) {
                if ($domainKey !== 'gross_motor') {
                    $this->answer($item, 'check');
                }
            }
        }

        $this->actingAs($this->teacherUser)
            ->get(route('progress.show', $this->record->id))
            ->assertSee('1st Evaluation · Completed')
            ->assertSee('7/7 domains completed');
    }

    public function test_guardians_get_the_explanation_and_skills_for_their_own_child_only(): void
    {
        $this->answer($this->items['gross_motor'][0], 'check');
        $this->answer($this->items['gross_motor'][1], 'hyphen');
        Sanctum::actingAs($this->guardianUser, ['*']);

        $this->getJson("/api/v1/guardian/children/{$this->child->id}/insights")
            ->assertOk()
            ->assertJsonPath('latest_round', 1)
            ->assertJsonPath('rounds.0.round', 1)
            ->assertJsonPath('rounds.0.domains.gross_motor.raw_score', 1)
            ->assertJsonPath('rounds.0.age.band', '4.1–5.0')
            ->assertJsonCount(6, 'rounds.0.steps')
            ->assertJsonPath('skills_to_practice.gross_motor.items.0.activity', 'gross_motor activity 2')
            ->assertJsonMissingPath('child.address');

        $this->getJson("/api/v1/guardian/children/{$this->child->id}/domains/gross_motor")
            ->assertOk()
            ->assertJsonPath('skills_to_practice.round', 1)
            ->assertJsonPath('skills_to_practice.items.0.activity', 'gross_motor activity 2')
            ->assertJsonPath('progress.rounds.1.interpretation', fn ($value) => $value === null || is_string($value));

        $otherGuardian = Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian'])->id,
            'first_name' => 'Other',
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Male',
            'contact_number' => '09170000008',
            'address' => 'Elsewhere',
            'relationship_to_child' => 'Father',
        ]);
        $otherChild = $otherGuardian->children()->create([
            'first_name' => 'Riley',
            'last_name' => 'Other',
            'sex' => 'Male',
            'date_of_birth' => '2022-05-01',
            'handedness' => 'left',
            'is_studying' => false,
        ]);

        $this->getJson("/api/v1/guardian/children/{$otherChild->id}/insights")->assertNotFound();
    }

    public function test_insights_for_a_child_without_a_progress_record_are_empty_not_an_error(): void
    {
        $this->record->forceDelete();
        Sanctum::actingAs($this->guardianUser, ['*']);

        $this->getJson("/api/v1/guardian/children/{$this->child->id}/insights")
            ->assertOk()
            ->assertJsonPath('latest_round', null)
            ->assertJsonCount(0, 'rounds')
            ->assertExactJson([
                'child' => [
                    'id' => $this->child->id,
                    'first_name' => 'Jordan',
                    'middle_name' => null,
                    'last_name' => 'Child',
                ],
                'latest_round' => null,
                'rounds' => [],
                'skills_to_practice' => [],
            ]);
    }
}
