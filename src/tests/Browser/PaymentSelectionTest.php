<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Item;

class PaymentSelectionTest extends DuskTestCase
{

    /**
     * @test
     * 小計画面で変更が反映される
     */
    public function payment_method_selection_updates_summary_in_real_time(): void
    {

        $this->browse(function (Browser $browser) {
        // ユーザー作成
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        // 商品作成
        $item = Item::factory()->create();

        $browser->visit('/login')
                ->type('email', $user->email)
                ->type('password', 'password')
                ->press('ログインする')
                ->pause(1000)
                ->assertPathIs('/')

                ->visit('/purchase/' . $item->id)
                ->assertPathIs('/purchase/' . $item->id)
                ->select('#payment', '1')
                ->pause(500)
                ->assertSeeIn('#payment-display', 'コンビニ払い');
        });
    }
}
