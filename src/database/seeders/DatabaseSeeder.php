<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 何度実行しても重複しないように
        User::updateOrCreate(
            ['email' => 'test@example.com'], // 一意キー
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
         $this->call(ItemSeeder::class);
        // 追加のダミー（必要なら）
        // User::factory(10)->create(); // ← fakerはunique()を使うとより安全
    }
}
