<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use App\Models\Purchase;
use Mockery;
use Stripe\Checkout\Session;
use Stripe\Stripe;

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
        //準備
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

        // --- Stripe モック ---
        $mockSession = Mockery::mock('overload:' . Session::class);
        $mockSession->shouldReceive('create')
            ->once()
            ->andReturn((object)['url' => '/purchase/success']);

        Stripe::setApiKey('sk_test_xxx'); // ダミーでOK

        // --- 実行 (Act) ---
        // ▼▼▼ POSTデータに、住所データを追加して送信する ▼▼▼
        $response = $this->actingAs($buyer)->post('/purchase/' . $item->id, [
            'payment_method' => '1',
            'post_code' => $addressTest['post_code'],
            'address' => $addressTest['address'],
            'building' => $addressTest['building'],
        ]);

        // --- 検証 (Assert) ---
        // Stripe Checkout へのリダイレクトを確認
        $response->assertRedirect('/purchase/success');

        // --- Act: 決済成功後の処理 ---
        $successResponse = $this->actingAs($buyer)->get('/purchase/success');

        // 最終的にトップページにリダイレクトされること
        $successResponse->assertRedirect('/');

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

    /**
     * @test
     * ユーザーは配送先住所を変更でき、その新しい住所で購入が完了できる
     */
    public function a_user_can_change_shipping_address_and_purchase_with_it(): void
    {
        //準備
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $newAddressData = [
            'post_code' => '987-6543',
            'address' => '新しいテスト住所',
            'building' => '新しいテストビル',
        ];

        //実行＆検証（商品購入画面への反映）
        $updateResponse = $this->actingAs($buyer)
            ->post('/purchase/address/' . $item->id, $newAddressData);
        $updateResponse->assertRedirect('/purchase/' . $item->id);
        $updateResponse->assertSessionHas('temporary_address', $newAddressData);


        //「変更した住所で購入」のテスト

        //準備
        $mockSession = Mockery::mock('overload:' . Session::class);
        $mockSession->shouldReceive('create')
            ->once()
            ->andReturn((object)['url' => '/purchase/success']);
        Stripe::setApiKey('sk_test_dummy');

        //実行
        $purchaseResponse = $this->actingAs($buyer)
            ->withSession(['temporary_address' => $newAddressData])
            ->post('/purchase/' . $item->id, [
                'payment_method' => '1',
                'post_code' => $newAddressData['post_code'],
                'address' => $newAddressData['address'],
                'building' => $newAddressData['building'],
            ]);

        //検証
        $purchaseResponse->assertRedirect('/purchase/success');

        $createdAddress = Address::where('post_code', '987-6543')->first();
        $successResponse = $this->actingAs($buyer)
                                ->withSession([
                                    'purchase_item_id' => $item->id,
                                    'purchase_address_id' => $createdAddress->id,
                                    'payment_method' => '1',
                                ])
                                ->get('/purchase/success');

        $successResponse->assertRedirect('/');

        $this->assertDatabaseHas('addresses', $newAddressData);
        $this->assertDatabaseHas('purchases', [
            'user_id' => $buyer->id,
            'item_id' => $item->id,
            'address_id' => $createdAddress->id,
        ]);
    }
}
