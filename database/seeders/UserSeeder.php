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
        User::create([
            'email' => 'su@talentfit.id',
            'password' => Hash::make('password'),
            'type' => 'senior',
            'name' => 'senior hrd'
        ]);

        User::create([
            'email' => 'admin@talentfit.id',
            'password' => Hash::make('password'),
            'type' => 'hrd',
            'name' => 'hrd'
        ]);
    }
}
