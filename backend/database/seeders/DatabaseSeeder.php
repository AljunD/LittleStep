<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Domain;
use App\Models\DomainObservation;
use App\Models\DomainResult;
use App\Models\EccdChecklistItem;
use App\Models\Guardian;
use App\Models\Log;
use App\Models\ProgressRecord;
use App\Models\Teacher;
use App\Models\User;
use App\Services\EccdExpertSystem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ECCDChecklistSeeder::class);

        if (app()->environment('production')) {
            return;
        }

        $teacherUser = User::firstOrCreate(
            ['email' => 'aljundalman15@gmail.com'],
            [
                'password' => bcrypt('Password123@'),
                'role' => 'teacher',
            ]
        );

        $teacher = Teacher::firstOrCreate(
            ['user_id' => $teacherUser->id],
            [
                'first_name' => 'Aljun',
                'middle_name' => 'Bequillos',
                'last_name' => 'Dalman',
                'sex' => 'Male',
                'contact_number' => '09192888483',
                'address' => 'Quezon City',
            ]
        );

        $guardianUser = User::firstOrCreate(
            ['email' => 'ischowalter@example.net'],
            [
                'password' => bcrypt('Password123@'),
                'role' => 'guardian',
            ]
        );

        $guardian = Guardian::firstOrCreate(
            ['user_id' => $guardianUser->id],
            [
                'first_name' => 'Isabella',
                'middle_name' => 'Cruz',
                'last_name' => 'Chowalter',
                'sex' => 'Female',
                'contact_number' => '09283746501',
                'address' => 'Antipolo City',
                'relationship_to_child' => 'Mother',
            ]
        );

        $child = Child::firstOrCreate(
            [
                'guardian_id' => $guardian->id,
                'first_name' => 'Juan',
                'last_name' => 'Chowalter',
            ],
            [
                'middle_name' => 'Carlos',
                'sex' => 'Male',
                'date_of_birth' => '2022-06-15',
                'address' => 'Antipolo City',
                'handedness' => 'right',
                'is_studying' => true,
                'school_name' => 'KidWatch Academy',
                'fathers_name' => 'Jose Chowalter',
                'fathers_age' => 40,
                'fathers_occupation' => 'Engineer',
                'fathers_education' => 'College',
                'mothers_name' => 'Isabella Chowalter',
                'mothers_age' => 38,
                'mothers_occupation' => 'Teacher',
                'mothers_education' => 'College',
                'number_of_siblings' => 2,
                'birth_order' => 1,
                'photo_path' => null,
            ]
        );

        $today = Carbon::now()->toDateString();

        $progressRecord = ProgressRecord::firstOrCreate(
            ['child_id' => $child->id],
            [
                'teacher_id' => $teacher->id,
                'evaluation_date' => $today,
                'evaluation_date_round_1' => $today,
                'evaluation_date_round_2' => null,
                'evaluation_date_round_3' => null,
                'evaluation_number' => 1,
                'status' => 'in_progress',
            ]
        );

        DomainObservation::firstOrCreate(
            ['progress_record_id' => $progressRecord->id],
            [
                'place_administered' => 'KidWatch Academy – Classroom A',
                'background_observations' => 'The child was cooperative and slightly shy at the beginning of the evaluation. He warmed up after a few minutes of play.',
                'family_environment_notes' => 'Lives with both parents and two younger siblings. Supportive household.',
                'stimulating_activities_notes' => 'Parents engage in daily outdoor play, storytelling, and reading sessions.',
                'home_environment_notes' => 'Safe indoor environment with age-appropriate toys and learning materials available.',
                'other_notes' => 'No significant medical or developmental concerns reported by the mother.',
                'status' => 'completed',
            ]
        );

        $alreadyFilled = Domain::where('progress_record_id', $progressRecord->id)
            ->where('evaluation_round', 1)
            ->exists();

        if (! $alreadyFilled) {
            $checkCounts = [
                'gross_motor' => 9,
                'fine_motor' => 8,
                'self_help' => 18,
                'receptive_language' => 4,
                'expressive_language' => 5,
                'cognitive' => 14,
                'social_emotional' => 16,
            ];

            $domainKeys = [
                'gross_motor',
                'fine_motor',
                'self_help',
                'receptive_language',
                'expressive_language',
                'cognitive',
                'social_emotional',
            ];

            foreach ($domainKeys as $domainKey) {
                $items = EccdChecklistItem::where('domain', $domainKey)
                    ->orderBy('item_number')
                    ->orderBy('id')
                    ->get();

                if ($items->isEmpty()) {
                    continue;
                }

                $checkLimit = $checkCounts[$domainKey] ?? 0;
                $checkedSoFar = 0;

                foreach ($items as $item) {
                    $domain = Domain::create([
                        'progress_record_id' => $progressRecord->id,
                        'checklist_item_id' => $item->id,
                        'evaluation_round' => 1,
                        'status' => 'completed',
                    ]);

                    $present = ($checkedSoFar < $checkLimit) ? 'check' : 'hyphen';
                    if ($present === 'check') {
                        $checkedSoFar++;
                    }

                    DomainResult::create([
                        'domain_id' => $domain->id,
                        'present' => $present,
                        'comments' => null,
                    ]);
                }
            }
        }

        try {
            $expert = new EccdExpertSystem;
            $computed = $expert->compute($progressRecord, 1);
            $expert->saveScores($progressRecord, 1, $computed);
        } catch (\Exception $e) {
            $this->command->warn('Could not compute scores: '.$e->getMessage());
        }

        Log::create([
            'user_id' => $teacherUser->id,
            'action' => 'seeded_sample_evaluation',
            'entity_type' => 'ProgressRecord',
            'entity_id' => $progressRecord->id,
            'details' => 'Sample ECCD evaluation data seeded for child Juan Chowalter (Round 1 – all 7 domains).',
        ]);

        $this->command->info('All tables seeded successfully!');
        $this->command->info("   Teacher   : {$teacher->first_name} {$teacher->last_name}");
        $this->command->info("   Guardian  : {$guardian->first_name} {$guardian->last_name}");
        $this->command->info("   Child     : {$child->first_name} {$child->last_name} (ID: {$child->id})");
        $this->command->info("   Progress  : #{$progressRecord->id}");
        $this->command->info('   Domains   : All 7 domains filled (Round 1)');
        $this->command->info('   Observation notes : filled');
        $this->command->info('   Domain scores     : computed');
    }
}
