<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use App\Models\Purchase;

class PurchaseControllerTest extends TestCase
{
    use RefreshDatabase;

    // ★商品購入 関連のテスト★

    /**
     * @test
     * ログインユーザーは商品を購入でき、購入後は正しく表示が更新される
     */
    public function a_user_can_purchase_an_item_and_it_is_reflected_correctly(): void
    {
        // --- 準備 (Arrange) ---
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => 'テスト購入用商品',
            'price' => 5000,
        ]);

        // ▼▼▼ 代わりに、フォームで送信する「住所データ（配列）」を用意する ▼▼▼
        $addressTest = [
            'post_code' => '123-4567',
            'address' => '東京都テスト区テスト町',
            'building' => 'テストビル101',
        ];

        // --- 実行 (Act) ---
        // ▼▼▼ POSTデータに、住所データを追加して送信する ▼▼▼
        $response = $this->actingAs($buyer)->post('/purchase/' . $item->id, [
            'payment_method' => '1',
            'post_code' => $addressTest['post_code'],
            'address' => $addressTest['address'],
            'building' => $addressTest['building'],
        ]);

        // --- 検証 (Assert) ---
        $response->assertRedirect('/');

        // 1. まず、addressesテーブルに新しい住所が作成されたかを確認
        $this->assertDatabaseHas('addresses', $addressTest);

        // 2. 作成された住所のIDを取得する
        $createdAddress = Address::first();

        // 3. purchasesテーブルに、その新しい住所IDが使われているかを確認
        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'address_id' => $createdAddress->id, // ★ここが重要！
            'price' => 5000,
        ]);


        // ▼▼▼ テスト30: 商品一覧で「SOLD」と表示されるかの検証 ▼▼▼
        // 1. 購入後、改めて商品一覧ページにアクセスする
        $indexResponse = $this->get('/');

        // 2. HTMLの中に「SOLD」という文字が含まれているか
        $indexResponse->assertSee('SOLD');


        // ▼▼▼ テスト31: マイページの購入履歴に追加されるかの検証 ▼▼▼
        // 1. 購入者として、マイページの「購入した商品」タブにアクセスする
        $mypageResponse = $this->actingAs($buyer)->get('/mypage?page=buy');

        // 2. HTMLの中に、購入した商品の名前が含まれているか
        $mypageResponse->assertSee('テスト購入用商品');
    }
}
