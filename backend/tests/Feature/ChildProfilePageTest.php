<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Domain;
use App\Models\DomainObservation;
use App\Models\DomainResult;
use App\Models\EccdChecklistItem;
use App\Models\Guardian;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use App\Services\EccdExpertSystem;
use Database\Seeders\ECCDChecklistSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ChildProfilePageTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private Teacher $teacher;

    private Child $child;

    private ProgressRecord $record;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-10-19 09:00:00');

        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        $this->teacher = $this->makeTeacher($this->teacherUser);
        $guardian = Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()])->id,
            'first_name' => 'Casey', 'middle_name' => null, 'last_name' => 'Guardian', 'sex' => 'Female',
            'contact_number' => '09170000001', 'address' => 'Guardian address', 'relationship_to_child' => 'mother',
        ]);
        $this->child = Child::create([
            'guardian_id' => $guardian->id, 'first_name' => 'Jordan', 'middle_name' => 'Lee', 'last_name' => 'Child', 'sex' => 'Female',
            'date_of_birth' => '2022-06-15', 'address' => 'San Isidro, Antipolo City, Rizal, Region IV-A', 'handedness' => 'right',
            'is_studying' => true, 'school_name' => 'Little Learners', 'number_of_siblings' => 2, 'birth_order' => 2,
        ]);
        $this->record = ProgressRecord::create([
            'child_id' => $this->child->id, 'teacher_id' => $this->teacher->id, 'evaluation_date' => '2026-10-01',
            'evaluation_date_round_1' => '2026-10-01', 'evaluation_number' => 1, 'status' => 'in_progress',
        ]);
    }

    private function makeTeacher(User $user): Teacher
    {
        return Teacher::create([
            'user_id' => $user->id, 'first_name' => 'Taylor', 'middle_name' => null, 'last_name' => 'Teacher',
            'sex' => 'Female', 'contact_number' => '09170000000', 'address' => 'Teacher address',
        ]);
    }

    /** Marks every official checklist item in the 1st evaluation, with the first $hyphens Gross Motor items marked —. */
    private function scoreFirstEvaluation(int $hyphens = 0): void
    {
        $this->seed(ECCDChecklistSeeder::class);

        foreach (EccdChecklistItem::inOfficialOrder()->get()->groupBy('domain') as $domainKey => $items) {
            foreach ($items->values() as $index => $item) {
                $domain = Domain::create(['progress_record_id' => $this->record->id, 'checklist_item_id' => $item->id, 'evaluation_round' => 1, 'status' => 'completed']);
                $present = $domainKey === 'gross_motor' && $index < $hyphens ? 'hyphen' : 'check';
                DomainResult::create(['domain_id' => $domain->id, 'present' => $present]);
            }
        }
    }

    public function test_the_profile_shows_age_details_and_the_evaluation_overview(): void
    {
        $this->actingAs($this->teacherUser)->get(route('children.show', $this->child->id))
            ->assertOk()
            ->assertSee('Jordan Lee Child')
            // 2022-06-15 to 2026-10-19 in 30-day months.
            ->assertSee('4 years, 4 months old')
            ->assertSee('ECCD age band 4.1–5.0')
            ->assertSee('1 of 3')
            ->assertSee('2nd child')
            ->assertSee('San Isidro')
            ->assertSee('Region IV-A')
            ->assertSee('Little Learners')
            ->assertSee('Email verified')
            ->assertSee('#CHD-'.str_pad((string) $this->child->id, 5, '0', STR_PAD_LEFT))
            ->assertSee('Not yet tested')
            ->assertSee('No observation notes yet.')
            ->assertSee(route('progress.show', $this->record->id))
            ->assertSee(route('progress.add-observation', ['child_id' => $this->child->id]));
    }

    public function test_scores_match_the_expert_system_for_the_latest_evaluation(): void
    {
        $this->scoreFirstEvaluation(hyphens: 1);
        $computed = app(EccdExpertSystem::class)->compute($this->record->fresh(), 1);
        $this->assertNotNull($computed['standard_score']);

        $html = $this->actingAs($this->teacherUser)->get(route('children.show', $this->child->id))
            ->assertOk()
            ->assertSee('Domain Results · 1st Evaluation')
            ->assertSee('7/7 domains')
            ->assertSee($computed['interpretation']['overall'])
            ->getContent();

        $this->assertMatchesRegularExpression('/Latest Standard Score<\/p>\s*<p[^>]*>'.$computed['standard_score'].'<\/p>/', $html);
        // One Gross Motor item is marked —, so one skill to practice.
        $this->assertMatchesRegularExpression('/Skills to Practice<\/p>\s*<p[^>]*>1<\/p>/', $html);
    }

    public function test_observation_notes_are_listed_and_escaped(): void
    {
        DomainObservation::create([
            'progress_record_id' => $this->record->id,
            'place_administered' => 'Day care center',
            'home_environment_notes' => '<script>alert(1)</script> Reads with family',
        ]);

        $this->actingAs($this->teacherUser)->get(route('children.show', $this->child->id))
            ->assertOk()
            ->assertSee('Day care center')
            ->assertSee('Home environment')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt; Reads with family', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Edit Notes');
    }

    public function test_an_archived_record_is_not_shown_as_current_progress(): void
    {
        $this->scoreFirstEvaluation();
        $this->record->delete();

        $this->actingAs($this->teacherUser)->get(route('children.show', $this->child->id))
            ->assertOk()
            ->assertSee('This child has not been evaluated yet.')
            ->assertSee('Start Evaluation')
            ->assertDontSee(route('progress.show', $this->record->id));
    }

    public function test_another_teachers_child_is_not_found(): void
    {
        /** @var User $otherUser */
        $otherUser = User::factory()->create(['role' => 'teacher']);
        $this->makeTeacher($otherUser);

        $this->actingAs($otherUser)->get(route('children.show', $this->child->id))->assertNotFound();
    }
}
