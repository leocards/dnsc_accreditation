<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seeds = collect([
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'password' => Hash::make('JDoe@12345'),
                'auth' => 1, 
                'username' => 'jdusername', 
                'designation' => 9
            ],[
                'first_name' => 'QA',
                'last_name' => 'Admin',
                'password' => Hash::make('QAadmin@12345'),
                'auth' => 6, 
                'username' => 'QAusername', 
                'designation' => 6
            ],[
                'first_name' => 'Internal',
                'last_name' => 'Accreditor',
                'password' => Hash::make('IAccreditor@12345'),
                'auth' => 5, 
                'username' => 'Internalusername', 
                'designation' => 7
            ],[
                'first_name' => 'External',
                'last_name' => 'Accreditor',
                'password' => Hash::make('EAccreditor@12345'),
                'auth' => 5, 
                'username' => 'Externalusername', 
                'designation' => 8
            ]
        ]);
        
        foreach($seeds as $seed){
            $obj = (object) $seed;
            User::create([
                'first_name' => $obj->first_name,
                'last_name' => $obj->last_name,
                'password' => $obj->password,
                'auth' => $obj->auth, 
                'username' => $obj->username, 
                'designation' => $obj->designation
            ]);
        }
    }
}
