<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Userモデルをインポート
use Illuminate\Support\Facades\Hash; // Hashファサードをインポート

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'テスト太郎',
            'email' => 'test1@1234',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'テスト花子',
            'email' => 'test2@1234',
            'password' => Hash::make('password'),
        ]);
    }
}
