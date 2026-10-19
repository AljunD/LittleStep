<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Guardian;
use App\Models\Log;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class SystemLogsTest extends TestCase
{
    use RefreshDatabase;

    private const CHROME_ON_WINDOWS = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36';

    private User $teacherUser;

    private Teacher $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        [$this->teacherUser, $this->teacher] = $this->makeTeacher('taylor@example.com');
    }

    private function makeTeacher(string $email): array
    {
        $user = User::factory()->create(['role' => 'teacher', 'email' => $email]);
        $teacher = Teacher::create([
            'user_id' => $user->id,
            'first_name' => 'Taylor',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'sex' => 'Female',
            'contact_number' => '09170000000',
            'address' => 'Balite, Rodriguez, Rizal, CALABARZON',
        ]);

        return [$user, $teacher];
    }

    private function makeChild(Teacher $teacher, string $firstName): Child
    {
        $guardian = Guardian::create([
            'user_id' => User::factory()->create(['role' => 'guardian'])->id,
            'first_name' => 'Casey',
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);
        $child = Child::create([
            'guardian_id' => $guardian->id,
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
            'teacher_id' => $teacher->id,
            'evaluation_date' => '2026-10-01',
            'evaluation_number' => 1,
            'status' => 'pending',
        ]);

        return $child;
    }

    /** Creates an entry directly (bypassing the request) so tests control every field, including its age. */
    private function makeLog(array $attributes): Log
    {
        $log = (new Log)->forceFill(array_merge([
            'user_id' => $this->teacherUser->id,
            'action' => 'viewed',
            'entity_type' => 'Child',
            'entity_id' => null,
            'details' => 'Entry',
        ], $attributes));
        $log->save();

        return $log;
    }

    public function test_page_lists_only_your_own_entries_with_readable_labels_device_and_ip(): void
    {
        $this->makeLog(['action' => 'deleted', 'details' => 'Child archived: Mine', 'ip_address' => '203.0.113.9', 'user_agent' => self::CHROME_ON_WINDOWS]);
        [$otherUser] = $this->makeTeacher('other@example.com');
        $this->makeLog(['user_id' => $otherUser->id, 'details' => 'Someone else entry']);

        $this->actingAs($this->teacherUser)->get(route('logs.index'))
            ->assertOk()
            ->assertSee('Child archived: Mine')
            ->assertSee('Archived')
            ->assertSee('Chrome on Windows')
            ->assertSee('203.0.113.9')
            ->assertDontSee('Someone else entry')
            ->assertSee('cannot be edited or deleted');
    }

    public function test_entries_record_the_ip_address_and_device_of_the_request(): void
    {
        $this->withHeaders(['User-Agent' => self::CHROME_ON_WINDOWS])
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.7'])
            ->post(route('auth.login.submit'), ['email' => 'taylor@example.com', 'password' => 'Password123@'])
            ->assertRedirect();

        $log = Log::where('action', 'login')->firstOrFail();
        $this->assertSame([$this->teacherUser->id, '198.51.100.7', self::CHROME_ON_WINDOWS], [$log->user_id, $log->ip_address, $log->user_agent]);
    }

    public function test_failed_sign_ins_are_shown_to_the_targeted_account_without_secrets(): void
    {
        $this->post(route('auth.login.submit'), ['email' => 'taylor@example.com', 'password' => 'Wrong-Password-123']);
        $this->post(route('auth.login.submit'), ['email' => 'nobody@example.com', 'password' => 'Wrong-Password-123']);

        $targeted = Log::where('action', 'login_failed')->where('user_id', $this->teacherUser->id)->firstOrFail();
        $this->assertSame($this->teacherUser->id, $targeted->entity_id);
        $this->assertStringNotContainsString('Wrong-Password-123', (string) $targeted->details);

        $unknown = Log::where('action', 'login_failed')->whereNull('user_id')->firstOrFail();
        $this->assertNull($unknown->entity_id);
        $this->assertStringNotContainsString('nobody@example.com', (string) $unknown->details);

        $this->actingAs($this->teacherUser)->get(route('logs.index'))
            ->assertOk()
            ->assertSee('Failed sign-in attempt on the web portal.')
            ->assertSee('failed or blocked sign-in attempt')
            ->assertSee(route('settings.profile').'#change-password', false);
    }

    public function test_the_failed_sign_in_alert_only_covers_the_last_seven_days(): void
    {
        $this->makeLog(['action' => 'login_failed', 'entity_type' => 'User', 'created_at' => now()->subDays(10)]);

        $this->actingAs($this->teacherUser)->get(route('logs.index'))
            ->assertOk()
            ->assertDontSee('failed or blocked sign-in attempt');
    }

    public function test_guardian_app_sign_ins_are_logged(): void
    {
        $guardianUser = User::factory()->create(['role' => 'guardian', 'email' => 'parent@example.com']);
        Guardian::create([
            'user_id' => $guardianUser->id,
            'first_name' => 'Casey',
            'middle_name' => null,
            'last_name' => 'Guardian',
            'sex' => 'Female',
            'contact_number' => '09170000001',
            'address' => 'Guardian address',
            'relationship_to_child' => 'Mother',
        ]);

        $this->postJson('/api/v1/guardian/login', ['email' => 'parent@example.com', 'password' => 'Wrong-Password-1'])->assertUnauthorized();
        $this->postJson('/api/v1/guardian/login', ['email' => 'parent@example.com', 'password' => 'Password123@'])->assertOk();

        $this->assertDatabaseHas('logs', ['user_id' => $guardianUser->id, 'action' => 'login_failed']);
        $this->assertDatabaseHas('logs', ['user_id' => $guardianUser->id, 'action' => 'login', 'details' => 'Guardian signed in to the mobile app.']);
    }

    public function test_visiting_the_dashboard_no_longer_fills_the_log(): void
    {
        $this->actingAs($this->teacherUser)->get(route('dashboard'))->assertOk();

        $this->assertDatabaseMissing('logs', ['entity_type' => 'Dashboard']);
    }

    public function test_entries_are_append_only(): void
    {
        $log = $this->makeLog(['details' => 'Original']);

        try {
            $log->update(['details' => 'Tampered']);
            $this->fail('Updating an audit log entry should not be allowed.');
        } catch (LogicException) {
        }

        try {
            $log->delete();
            $this->fail('Deleting an audit log entry should not be allowed.');
        } catch (LogicException) {
        }

        $this->assertDatabaseHas('logs', ['id' => $log->id, 'details' => 'Original', 'deleted_at' => null]);
    }

    public function test_category_tabs_and_filters_narrow_the_list(): void
    {
        $this->makeLog(['action' => 'login', 'entity_type' => 'User', 'details' => 'Teacher logged in.']);
        $this->makeLog(['action' => 'updated', 'entity_type' => 'Guardian', 'details' => 'Guardian updated']);
        $this->actingAs($this->teacherUser);

        $this->get(route('logs.index', ['category' => 'security']))
            ->assertOk()->assertSee('Teacher logged in.')->assertDontSee('Guardian updated');

        $this->get(route('logs.index', ['entity_type' => 'Guardian']))
            ->assertOk()->assertSee('Guardian updated')->assertDontSee('Teacher logged in.');

        $this->get(route('logs.index', ['category' => 'everything']))->assertSessionHasErrors('category');
    }

    public function test_records_are_linked_only_when_you_still_own_them(): void
    {
        $ownChild = $this->makeChild($this->teacher, 'Jordan');
        $archivedChild = $this->makeChild($this->teacher, 'Riley');
        $archivedChild->delete();
        [, $otherTeacher] = $this->makeTeacher('other@example.com');
        $otherChild = $this->makeChild($otherTeacher, 'Sam');

        $this->makeLog(['entity_id' => $ownChild->id, 'details' => 'Own child']);
        $archivedLog = $this->makeLog(['entity_id' => $archivedChild->id, 'details' => 'Archived child']);
        $this->makeLog(['entity_id' => $otherChild->id, 'details' => 'Other teacher child']);

        $this->actingAs($this->teacherUser)->get(route('logs.index'))
            ->assertOk()
            ->assertSee('href="'.route('children.show', $ownChild->id).'"', false)
            ->assertDontSee('href="'.route('children.show', $archivedChild->id).'"', false)
            ->assertDontSee('href="'.route('children.show', $otherChild->id).'"', false);

        $this->get(route('logs.show', $archivedLog->id))
            ->assertOk()
            ->assertSee('This record is archived or no longer available.');
    }

    public function test_entry_details_are_only_visible_to_their_owner(): void
    {
        $mine = $this->makeLog(['action' => 'login_failed', 'entity_type' => 'User', 'details' => 'Failed sign-in attempt on the web portal.', 'ip_address' => '203.0.113.9', 'user_agent' => self::CHROME_ON_WINDOWS]);
        [$otherUser] = $this->makeTeacher('other@example.com');
        $theirs = $this->makeLog(['user_id' => $otherUser->id]);

        $this->actingAs($this->teacherUser)->get(route('logs.show', $mine->id))
            ->assertOk()
            ->assertSee('Log Entry #'.$mine->id)
            ->assertSee('203.0.113.9')
            ->assertSee(self::CHROME_ON_WINDOWS)
            ->assertSee('Change Password');

        $this->get(route('logs.show', $theirs->id))->assertNotFound();
    }

    public function test_csv_export_contains_only_your_filtered_entries_and_neutralizes_formulas(): void
    {
        $this->makeLog(['action' => 'updated', 'entity_type' => 'Guardian', 'details' => '=HYPERLINK("http://evil.example","click")']);
        $this->makeLog(['action' => 'login', 'entity_type' => 'User', 'details' => 'Teacher logged in.']);
        [$otherUser] = $this->makeTeacher('other@example.com');
        $this->makeLog(['user_id' => $otherUser->id, 'details' => 'Someone else entry']);

        $response = $this->actingAs($this->teacherUser)->get(route('logs.export', ['category' => 'changes']));
        $response->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=littlestep-activity-', $response->headers->get('Content-Disposition'));

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Date,Time,Action,Category,Record,"Record ID",Details,"IP Address",Device', $csv);
        $this->assertStringContainsString('"\'=HYPERLINK(', $csv);
        $this->assertStringNotContainsString('Teacher logged in.', $csv);
        $this->assertStringNotContainsString('Someone else entry', $csv);

        $this->assertDatabaseHas('logs', ['user_id' => $this->teacherUser->id, 'action' => 'exported', 'details' => 'Exported 1 log entry to CSV.']);
    }

    public function test_csv_export_is_rate_limited(): void
    {
        $this->actingAs($this->teacherUser);

        foreach (range(1, 10) as $attempt) {
            $this->get(route('logs.export'))->assertOk();
        }

        $this->get(route('logs.export'))->assertStatus(429);
    }

    public function test_retention_prunes_only_old_entries_and_is_off_by_default(): void
    {
        $old = $this->makeLog(['details' => 'Old entry', 'created_at' => now()->subDays(40)]);
        $recent = $this->makeLog(['details' => 'Recent entry']);

        $this->artisan('model:prune', ['--model' => [Log::class]])->assertSuccessful();
        $this->assertDatabaseHas('logs', ['id' => $old->id]);

        config(['littlestep.audit_log_retention_days' => 30]);
        $this->artisan('model:prune', ['--model' => [Log::class]])->assertSuccessful();

        $this->assertDatabaseMissing('logs', ['id' => $old->id]);
        $this->assertDatabaseHas('logs', ['id' => $recent->id]);
    }

    public function test_only_signed_in_teachers_can_open_system_logs(): void
    {
        $entry = $this->makeLog([]);

        $this->get(route('logs.index'))->assertRedirect(route('login'));
        $this->get(route('logs.export'))->assertRedirect(route('login'));

        /** @var User $guardian */
        $guardian = User::factory()->create(['role' => 'guardian']);
        $this->actingAs($guardian)->get(route('logs.index'))->assertForbidden();
        $this->actingAs($guardian)->get(route('logs.show', $entry->id))->assertForbidden();
        $this->actingAs($guardian)->get(route('logs.export'))->assertForbidden();
    }
}
