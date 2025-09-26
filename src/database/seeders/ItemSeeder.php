<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * 日本語 → enum: new / like_new / used
     */
    private function mapCondition(string $label): string
    {
        return match ($label) {
            '良好', '目立った傷や汚れなし' => 'like_new',
            'やや傷や汚れあり', '状態が悪い' => 'used',
            default => 'used',
        };
    }

    public function run(): void
    {
        // 出品者（無ければ作成）
        $seller = User::firstWhere('email', 'test@example.com')
            ?? User::factory()->create([
                'name'     => 'test user',
                'email'    => 'test@example.com',
                'password' => bcrypt('password'),
            ]);

        // 課題指定の10件
        $rows = [
            ['title' => '腕時計','brand'=>null,'price'=>15000,'description'=>'スタイリッシュなデザインのメンズ腕時計','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Armani+Mens+Clock.jpg','cond_jp'=>'良好'],
            ['title' => 'HDD','brand'=>null,'price'=>5000,'description'=>'高速で信頼性の高いハードディスク','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/HDD+Hard+Disk.jpg','cond_jp'=>'目立った傷や汚れなし'],
            ['title' => '玉ねぎ3束','brand'=>null,'price'=>300,'description'=>'新鮮な玉ねぎ3束のセット','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/iLoveIMG+d.jpg','cond_jp'=>'やや傷や汚れあり'],
            ['title' => '革靴','brand'=>null,'price'=>4000,'description'=>'クラシックなデザインの革靴','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Leather+Shoes+Product+Photo.jpg','cond_jp'=>'状態が悪い'],
            ['title' => 'ノートPC','brand'=>null,'price'=>45000,'description'=>'高性能なノートパソコン','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Living+Room+Laptop.jpg','cond_jp'=>'良好'],
            ['title' => 'マイク','brand'=>null,'price'=>8000,'description'=>'高音質のレコーディング用マイク','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Music+Mic+4632231.jpg','cond_jp'=>'目立った傷や汚れなし'],
            ['title' => 'ショルダーバッグ','brand'=>null,'price'=>3500,'description'=>'おしゃれなショルダーバッグ','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Purse+fashion+pocket.jpg','cond_jp'=>'やや傷や汚れあり'],
            ['title' => 'タンブラー','brand'=>null,'price'=>500,'description'=>'使いやすいタンブラー','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Tumbler+souvenir.jpg','cond_jp'=>'状態が悪い'],
            ['title' => 'コーヒーミル','brand'=>null,'price'=>4000,'description'=>'手動のコーヒーミル','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/Waitress+with+Coffee+Grinder.jpg','cond_jp'=>'良好'],
            ['title' => 'メイクセット','brand'=>null,'price'=>2500,'description'=>'便利なメイクアップセット','image'=>'https://coachtech-matter.s3.ap-northeast-1.amazonaws.com/image/%E5%A4%96%E5%87%BA%E3%83%A1%E3%82%A4%E3%82%AF%E3%82%A2%E3%83%83%E3%83%95%E3%82%9A%E3%82%BB%E3%83%83%E3%83%88.jpg','cond_jp'=>'目立った傷や汚れなし'],
        ];

        foreach ($rows as $r) {
            Item::updateOrCreate(
                ['title' => $r['title']],
                [
                    'user_id'     => $seller->id,
                    'title'       => $r['title'],
                    'brand'       => $r['brand'],
                    'description' => $r['description'],
                    'price'       => (int) $r['price'],
                    'condition'   => $this->mapCondition($r['cond_jp']),
                    'image_path'  => $r['image'],
                ]
            );
        }
    }
}
