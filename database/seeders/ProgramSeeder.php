<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Program::create([
            'abbreviation' => 'BSIT', 
            'program_name' => 'Bachelor of Science in Information Technology', 
            'instituteId' => 1, 
        ]);

        Program::create([
            'abbreviation' => 'BSIS', 
            'program_name' => 'Bachelor of Science in Information System', 
            'instituteId' => 1, 
        ]);

        Program::create([
            'abbreviation' => 'BSeD', 
            'program_name' => 'Bachelor of Secondary Education', 
            'instituteId' => 2, 
        ]);

        Program::create([
            'abbreviation' => 'BACOMM', 
            'program_name' => 'Bachelor of Arts in Communication', 
            'instituteId' => 2, 
        ]);
    }
}
