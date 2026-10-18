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
use App\Services\ChildsRecord2Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildsRecord2ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $teacherUser;

    private ProgressRecord $record;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherUser = User::factory()->create(['role' => 'teacher']);
        $teacher = Teacher::create([
            'user_id' => $this->teacherUser->id,
            'first_name' => 'Maria',
            'middle_name' => null,
            'last_name' => 'Santos',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Teacher address',
        ]);
        $guardian = Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian'])->id,
            'first_name' => 'Ana',
            'middle_name' => null,
            'last_name' => 'Perez',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);
        $child = Child::create([
            'guardian_id' => $guardian->id,
            'first_name' => 'Carlos',
            'middle_name' => null,
            'last_name' => 'Perez',
            'sex' => 'Male',
            'date_of_birth' => '2022-03-14',
            'address' => 'Balite, Rodriguez, Rizal, CALABARZON',
            'handedness' => 'left',
            'is_studying' => false,
            'birth_order' => 2,
        ]);
        $this->record = ProgressRecord::create([
            'child_id' => $child->id,
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2025-10-01',
            'evaluation_date_round_1' => '2025-10-01',
            'evaluation_number' => 1,
            'status' => 'in_progress',
        ]);

        foreach ([1 => 'check', 2 => 'hyphen'] as $itemNumber => $present) {
            $item = EccdChecklistItem::create(['domain' => 'gross_motor', 'activity' => "Item {$itemNumber}", 'item_number' => $itemNumber]);
            $domain = Domain::create([
                'progress_record_id' => $this->record->id,
                'checklist_item_id' => $item->id,
                'evaluation_round' => 1,
                'status' => 'completed',
            ]);
            DomainResult::create([
                'domain_id' => $domain->id,
                'present' => $present,
                'comments' => $present === 'hyphen' ? 'Needs a hand on the rail' : null,
            ]);
        }
    }

    private function texts(array $pages, int $page): array
    {
        return array_column($pages[$page - 1]['items'], 'text');
    }

    public function test_the_report_has_the_20_official_pages_with_the_childs_data_placed_on_them(): void
    {
        $pages = app(ChildsRecord2Report::class)->build($this->record);

        $this->assertCount(20, $pages);
        foreach ($pages as $index => $page) {
            $this->assertFileExists($page['template'], 'Missing official form page '.($index + 1));
        }

        $profile = $this->texts($pages, 1);
        $this->assertContains('Carlos Perez', $profile);
        $this->assertContains('March', $profile);
        $this->assertContains('Rodriguez', $profile);
        $this->assertContains('2nd', $profile);
        $checks = array_values(array_filter($pages[0]['items'], fn (array $item) => $item['text'] === '✔'));
        $this->assertCount(2, $checks);
        $this->assertEqualsWithDelta(374.3, $checks[0]['left'] + $checks[0]['width'] / 2, 0.1, 'Handedness "left" box');
        $this->assertEqualsWithDelta(428.5, $checks[1]['left'] + $checks[1]['width'] / 2, 0.1, 'Studying "No" box');

        $this->assertContains('Maria Santos', $this->texts($pages, 2));

        $marks = array_values(array_filter($pages[3]['items'], fn (array $item) => in_array($item['text'], ['✔', '—'], true)));
        $this->assertSame(['✔', '—'], array_column($marks, 'text'));
        $this->assertEqualsWithDelta(415.7, $marks[0]['left'] + $marks[0]['width'] / 2, 0.1);
        $this->assertContains('1st: Needs a hand on the rail', $this->texts($pages, 4));

        $this->assertContains('1', $this->texts($pages, 5));

        $this->assertContains('10/01/25', $this->texts($pages, 18));
        $this->assertNotNull($pages[18]['svg']);
        $this->assertNull($pages[19]['svg']);
    }

    public function test_teachers_download_the_pdf_for_their_own_records_only(): void
    {
        $response = $this->actingAs($this->teacherUser)->get(route('progress.download-pdf', $this->record->id));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->getContent());
        $this->assertSame(20, preg_match_all('/\/Type\s*\/Page[^s]/', $response->getContent()));
        $this->assertDatabaseHas('logs', ['action' => 'downloaded', 'entity_id' => $this->record->id]);

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

        $this->actingAs($otherTeacher)->get(route('progress.download-pdf', $this->record->id))->assertNotFound();
    }
}
