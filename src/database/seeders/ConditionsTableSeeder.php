<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Condition;

class ConditionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contents = [
            '良好',
            '目立った傷や汚れなし',
            'やや傷や汚れあり',
            '状態が悪い',
        ];

        foreach ($contents as $content) {
            Condition::create(['content' => $content]);
        }
    }
}
