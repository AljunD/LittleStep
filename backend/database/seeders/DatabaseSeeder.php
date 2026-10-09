<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\Child;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {
        $teacherUser = User::create([
            'email'    => 'aljundalman15@gmail.com',
            'password' => bcrypt('Password123@'),
            'role'     => 'teacher',
        ]);

        Teacher::create([
            'user_id'        => $teacherUser->id,
            'first_name'     => 'Aljun',
            'middle_name'    => 'Bequillos',
            'last_name'      => 'Dalman',
            'sex'            => 'Male',
            'contact_number' => '09192888483',
            'address'        => 'Quezon City',
        ]);

        $guardianUser = User::create([
            'email'    => 'ischowalter@example.net',
            'password' => bcrypt('Password123@'),
            'role'     => 'guardian',
        ]);

        $guardian = Guardian::create([
            'user_id'              => $guardianUser->id,
            'first_name'           => 'Isabella',
            'middle_name'          => 'Cruz',
            'last_name'            => 'Chowalter',
            'sex'                  => 'Female',
            'contact_number'       => '09283746501',
            'address'              => 'Antipolo City',
            'relationship_to_child'=> 'Mother',
        ]);

        Child::create([
            'guardian_id'        => $guardian->id,
            'first_name'         => 'Juan',
            'middle_name'        => 'Carlos',
            'last_name'          => 'Chowalter',
            'sex'                => 'Male',
            'date_of_birth'      => '2018-03-15',
            'address'            => 'Antipolo City',
            'handedness'         => 'right',
            'is_studying'        => true,
            'school_name'        => 'KidWatch Academy',
            'fathers_name'       => 'Jose Chowalter',
            'fathers_age'        => 40,
            'fathers_occupation' => 'Engineer',
            'fathers_education'  => 'College',
            'mothers_name'       => 'Isabella Chowalter',
            'mothers_age'        => 38,
            'mothers_occupation' => 'Teacher',
            'mothers_education'  => 'College',
            'number_of_siblings' => 2,
            'birth_order'        => 1,
            'photo_path'         => 'storage/photos/childs/juan.jpg', 
        ]);
    }
}
