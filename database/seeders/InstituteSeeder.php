<?php

namespace Database\Seeders;

use App\Models\Institute;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstituteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Institute::create([
            'abbreviation' => 'IC', 
            'institute_name' => 'Institute of Computing', 
            'dean' => null,
            'established' => null
        ]);

        Institute::create([
            'abbreviation' => 'ITEd', 
            'institute_name' => 'Institute of Teacher Education',
            'dean' => null,
            'established' => null
        ]);
    }
}
