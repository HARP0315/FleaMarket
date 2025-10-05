<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * 送付先住所変更画面にて登録した住所が商品購入画面に反映、購入情報に紐づく
    */
    public function user_can_change_shipping_address_and_purchase_with_webhook(): void
    {
        putenv('STRIPE_USE_FAKE=true');
        Stripe::setApiKey('sk_test_dummy');

        //準備
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        //送付先変更ページで作成される住所情報
        $newAddressData = [
            'post_code' => '987-6543',
            'address' => '新しいテスト住所',
            'building' => '新しいテストビル',
        ];

        //実行＆検証
        $updateResponse = $this->actingAs($buyer)
            ->post("/purchase/address/{$item->id}", $newAddressData);

        $updateResponse->assertRedirect("/purchase/{$item->id}");
        $this->assertEquals(session('temporary_address'), $newAddressData);

        //送付先住所が商品購入画面で表示されているか確認
        $this->actingAs($buyer)
             ->get("/purchase/{$item->id}")
             ->assertSee($newAddressData['address'])
             ->assertSee($newAddressData['post_code'])
             ->assertSee($newAddressData['building']);

        //Stripe Checkout モック
        $mockSession = Mockery::mock('overload:' . Session::class);
        $mockSession->shouldReceive('create')
            ->once()
            ->andReturn((object)['url' => '/purchase/success']);

        //購入実行
        $purchaseResponse = $this->actingAs($buyer)
            ->withSession(['temporary_address' => $newAddressData])
            ->post("/purchase/{$item->id}", [
                'price' => $item->price,
                'payment_method' => 1,
                'post_code' => $newAddressData['post_code'],
                'address' => $newAddressData['address'],
                'building' => $newAddressData['building'],
            ]);

        $purchaseResponse->assertRedirect('/purchase/success');

        // 仮注文レコード取得
        $purchase = Purchase::firstWhere([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
        ]);

        //Webhook 擬似呼び出し
        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'metadata' => [
                        'item_id' => $item->id,
                        'user_id' => $buyer->id,
                        'purchase_id' => $purchase->id,
                        'post_code' => $newAddressData['post_code'],
                        'address' => $newAddressData['address'],
                        'building' => $newAddressData['building'],
                        'payment_method' => 1,
                    ],
                    'amount_total' => $item->price,
                ],
            ],
        ];

        $response = $this->postJson('/stripe/webhook', $payload);
        $response->assertStatus(200);

        //DB確認: Address レコードを取得
        $address = Address::where($newAddressData)->first();

        // その住所が紐づく Purchase があるか確認
        $purchase = Purchase::where('address_id', $address->id)->first();

    }
}
