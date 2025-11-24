<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Manager 1',
            'email' => 'manager1@example.com',
            'password' => bcrypt('manager1'),
            'role' => 'MANAGER',
        ]);
        
        User::create([
            'name' => 'Employee 1',
            'email' => 'employee1@example.com',
            'password' => bcrypt('employee1'),
            'role' => 'EMPLOYEE',
        ]); 

        User::create([
            'name' => 'Employee 2',
            'email' => 'employee2@example.com',
            'password' => bcrypt('employee2'),
            'role' => 'EMPLOYEE',
        ]); 
    }
}
