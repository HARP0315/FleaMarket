<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Address;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * マイページで、プロフィール画像、ユーザー名、出品した商品一覧、購入した商品一覧が正しく表示される
     */
    public function it_displays_user_information_and_items_correctly_on_mypage(): void
    {

        // Storageをテスト用に偽装
        Storage::fake('public');

        //準備
        $file = UploadedFile::fake()->image('profile.jpeg');
        $user = User::factory()->create([
            'name' => 'マイページユーザー',
            'img' => $file->store('profiles', 'public'),
        ]);

        $address = Address::factory()->create(['user_id' => $user->id]);

        $item = Item::factory()->create([
            'user_id' => $user->id,
            'name' => '私が出品した商品',
        ]);

        $purchasedItem = Item::factory()->create(['name' => '私が購入した商品']);
        Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $purchasedItem->id,
            'address_id' => $address->id,
            'price' => $item->price,
            'payment_method' => 1,
            'payment_status' => 0,
            'is_deleted' => 0,
        ]);

        //実行 & 検証

        //「出品した商品」タブ（デフォルト）の検証
        $responseSell = $this->actingAs($user)->get('/mypage');

        $responseSell->assertStatus(200);
        $responseSell->assertSee('マイページユーザー');
        $responseSell->assertSee('私が出品した商品');
        $responseSell->assertDontSee('私が購入した商品');
        $responseSell->assertSee($user->img);

        //「購入した商品」タブの検証
        $responseBuy = $this->actingAs($user)->get('/mypage?page=buy');

        $responseBuy->assertStatus(200);
        $responseBuy->assertSee('マイページユーザー');
        $responseBuy->assertSee('私が購入した商品');
        $responseBuy->assertDontSee('私が出品した商品');
        $responseSell->assertSee($user->img);
    }

    /**
     * @test
     * プロフィール編集ページで、既存のユーザー情報が正しく初期表示される（プロフィール画像、ユーザー名、郵便番号、住所）
     */
    public function it_displays_existing_user_profile_data_on_the_edit_page(): void
    {
        Storage::fake('public');

        //準備
        $file = UploadedFile::fake()->image('profile.jpeg');
        $user = User::factory()->create([
            'img' => $file->store('profiles', 'public'),
            'name' => 'テストユーザー名',
            'post_code' => '123-4567',
            'address' => '東京都テスト区テスト町1-2-3',
        ]);

        //実行
        $response = $this->actingAs($user)->get('/mypage/profile');

        //検証
        $response->assertStatus(200);

        $response->assertSee('value="テストユーザー名"', false);
        $response->assertSee('value="123-4567"', false);
        $response->assertSee('value="東京都テスト区テスト町1-2-3"', false);
        $response->assertSee($user->img);
    }
}
