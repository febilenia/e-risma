<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserUsernameSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->updateOrInsert(
            ['username' => 'superadmin'],
            [
                'name' => 'Super Administrator',
                'username' => 'superadmin',
                'password' => Hash::make('superadmin'),
                'role' => 'superadmin',
                'opd_id' => null,
                'password_changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
