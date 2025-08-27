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

        // Seeder の実行順：カテゴリ → アイテム
        $this->call([
            CategorySeeder::class, // 先にカテゴリ
            ItemSeeder::class,     // 既存のダミー商品
        ]);

        // ← ここでの ItemSeeder 単体呼び出しは不要（削除）
    }
}
