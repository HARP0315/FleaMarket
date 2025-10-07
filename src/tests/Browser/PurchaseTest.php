<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use App\Models\Purchase;
use Stripe\Checkout\Session;
use Mockery;

class PurchaseTest extends DuskTestCase
{
    /**
     * 「購入する」ボタンを押下すると購入が完了する
     * 購入した商品は商品一覧画面にて「sold」と表示される
     * 「プロフィール/購入した商品一覧」に追加されている
     * @return void
     */
    public function test_purchase_creates_pending_record()
    {
        //準備
        $user = User::factory()->create([
            'post_code' => '123-4567',
            'address' => '東京都渋谷区',
            'building' => 'ヒカリエ',
        ]);
        $seller = User::factory()->create();
        $item = Item::factory()->create(['price' => 15000, 'user_id' => $seller->id]);

        // StripeのSession::createをモック
        $sessionMock = (object)['url' => '/fake-checkout-url'];
        $mock = Mockery::mock('alias:' . Session::class);
        $mock->shouldReceive('create')->andReturn($sessionMock);

        //実行
        $this->browse(function ($browser) use ($user, $item) {
            $browser->loginAs($user)
                ->visit("/purchase/{$item->id}")
                ->select('payment_method', 2)
                ->press('購入する');

            //検証
            //商品一覧画面を表示
            $browser->visit('/');
            //購入した商品に "SOLD" 表示があることを確認
            $browser->assertSeeIn(".item-list__item a[href='/item/{$item->id}'] .item-list__sold-box", 'SOLD');

            //プロフィール画面を表示
            $browser->visit('/mypage?page=buy');
            //購入した商品が購入済みタブに表示されていることを確認
            $browser->assertSeeIn(
                ".mypage-list__item .mypage-list__item-name",
                $item->name
            );
        });

        // 仮注文が作られているか確認
        $this->assertDatabaseHas('purchases', [
            'item_id' => $item->id,
            'user_id' => $user->id,
            'payment_status' => 0,
        ]);

        $purchase = Purchase::firstWhere([
            'item_id' => $item->id,
            'user_id' => $user->id,
        ]);

        $payload = [
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'metadata' => [
                        'item_id' => $item->id,
                        'user_id' => $user->id,
                        'post_code' => $user->post_code,
                        'address' => $user->address,
                        'building' => $user->building,
                        'payment_method' => 2,
                        'purchase_id' => $purchase->id,
                    ],
                    'amount_total' => $item->price,
                ],
            ],
        ];

        $response = $this->postJson('/stripe/webhook', $payload);
        $response->assertStatus(200);

        // 住所と purchase 更新を確認
        $this->assertDatabaseHas('addresses', ['address' => '東京都渋谷区']);
        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'address_id' => Address::first()->id,
        ]);

    }
}
