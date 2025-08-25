<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items_data = [
            [
                'user_id' => 1,
                'name' => '腕時計',
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'img' => 'images/Armani+Mens+Clock.jpg',
                'condition_id' => 1,
                'price' => 15000,
                'categories' => [1, 5, 12]
            ],
            [
                'user_id' => 1,
                'name' => 'HDD',
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'img' => 'images/HDD+Hard+Disk.jpg',
                'condition_id' => 2,
                'price' => 5000,
                'categories' => [2]
            ],
            [
                'user_id' => 2,
                'name' => '玉ねぎ3束',
                'brand' => 'なし',
                'description' => '新鮮な玉ねぎ3束のセット',
                'img' => 'images/iLoveIMG+d.jpg',
                'condition_id' => 3,
                'price' => 300,
                'categories' => [10]
            ],
            [
                'user_id' => 1,
                'name' => '革靴',
                'brand' => '',
                'description' => 'クラシックなデザインの靴',
                'img' => 'images/Leather+Shoes+Product+Photo.jpg',
                'condition_id' => 4,
                'price' => 4000,
                'categories' => [1, 5]
            ],
            [
                'user_id' => 1,
                'name' => 'ノートPC',
                'brand' => '',
                'description' => '高性能なノートパソコン',
                'img' => 'images/Living+Room+Laptop.jpg',
                'condition_id' => 1,
                'price' => 45000,
                'categories' => [2]
            ],
            [
                'user_id' => 1,
                'name' => 'マイク',
                'brand' => 'なし',
                'description' => '高音質のレコーディング用マイク',
                'img' => 'images/Music+Mic+4632231.jpg',
                'condition_id' => 2,
                'price' => 8000,
                'categories' => [2]
            ],
            [
                'user_id' => 2,
                'name' => 'ショルダーバッグ',
                'brand' => '',
                'description' => 'おしゃれなショルダーバッグ',
                'img' => 'images/Purse+fashion+pocket.jpg',
                'condition_id' => 3,
                'price' => 3500,
                'categories' => [1, 4]
            ],
            [
                'user_id' => 2,
                'name' => 'タンブラー',
                'brand' => 'なし',
                'description' => '使いやすいタンブラー',
                'img' => 'images/Tumbler+souvenir.jpg',
                'condition_id' => 4,
                'price' => 500,
                'categories' => [10]
            ],
            [
                'user_id' => 2,
                'name' => 'コーヒーミル',
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'img' => 'images/Waitress+with+Coffee+Grinder.jpg',
                'condition_id' => 1,
                'price' => 4000,
                'categories' => [10]
            ],
            [
                'user_id' => 2,
                'name' => 'メイクセット',
                'brand' => '',
                'description' => '便利なメイクアップセット',
                'img' => 'images/外出メイクアップセット.jpg',
                'condition_id' => 2,
                'price' => 2500,
                'categories' => [4, 6]
            ]
        ];

        foreach ($items_data as $item_data) {
            $category_ids = $item_data['categories'];
            unset($item_data['categories']);

            $item = Item::create($item_data);

            $item->categories()->attach($category_ids);
        }
    }
}
