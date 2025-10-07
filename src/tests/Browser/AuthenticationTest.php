<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends DuskTestCase
{

    /**
     * @test
     * メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     */
    public function verification_notice_link_opens_mailhog_in_new_tab(): void
    {

        //準備
        $user = User::factory()->create([
            'name' => '認証テストユーザー',
            'email' => 'verify@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
        ]);

        //実行
        $this->browse(function (Browser $browser) use ($user) {
            // actingAs相当でセッションにログイン状態をセット
            $browser->loginAs($user)
                ->visit('/email/verify')
                ->assertSee('認証はこちらから')
                ->clickLink('認証はこちらから');

            // 検証：WebDriver 経由で新しいタブに切り替え
            $handles = $browser->driver->getWindowHandles(); // 開いているタブ一覧
            $browser->driver->switchTo()->window(end($handles)); // 最後のタブに切り替え

            $browser->assertUrlIs('http://localhost:8025/');

        });
    }
}
