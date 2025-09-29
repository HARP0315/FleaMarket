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
     * 認証誘導画面のボタンをクリックすると、新しいタブでMailHogが開く
     */
    public function verification_notice_link_opens_mailhog_in_new_tab(): void
    {

        $user = User::factory()->create([
            'name' => '認証テストユーザー',
            'email' => 'verify@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => null,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            // actingAs相当でセッションにログイン状態をセット
            $browser->loginAs($user)
                ->visit('/email/verify')
                ->assertSee('認証はこちらから')
                ->clickLink('認証はこちらから');

            // WebDriver 経由で新しいタブに切り替え
            $handles = $browser->driver->getWindowHandles(); // 開いているタブ一覧
            $browser->driver->switchTo()->window(end($handles)); // 最後のタブに切り替え

            $browser->assertUrlIs('http://localhost:8025/');

        });
    }
}
