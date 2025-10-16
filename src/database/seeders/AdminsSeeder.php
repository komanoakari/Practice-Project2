<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->insert([
            'name' => '管理ユーザー',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
        ]);
    }
}
