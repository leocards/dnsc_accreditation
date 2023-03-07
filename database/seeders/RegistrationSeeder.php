<?php

namespace Database\Seeders;

use App\Models\Registration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $registrations = collect([
            [
                'first_name' => 'Anna',
                'last_name' => 'Doe',
                'username' => 'annausername',
                'password' => '?Anna@1234!!',
            ],
            [
                'first_name' => 'Josh',
                'last_name' => 'Washington',
                'username' => 'jwusername',
                'password' => '?JWash@1234!!',
            ],
            [
                'first_name' => 'Janna',
                'last_name' => 'Does',
                'username' => 'jdusername',
                'password' => 'JD!!@1234',
            ],
        ]);

        foreach ($registrations as $value) {
            Registration::create([
                'first_name' => $value['first_name'],
                'last_name' => $value['last_name'],
                'username' => $value['username'],
                'password' => Hash::make($value['password']),
            ]);
        }
        
    }
}
