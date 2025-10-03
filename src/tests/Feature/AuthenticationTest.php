<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // ★会員登録関連のテスト★

    /**
     * @test
     * 会員登録時に、名前・メール・パスワードが空の場合、バリデーションエラーが返ってくることを確認
     */
    public function it_validates_registration_input(): void
    {

        //準備
        $testData = [
            'name'                  => '',
            'email'                 => '',
            'password'              => '',
            'password_confirmation' => 'password123',
        ];

        //実行
        $response = $this->post('/register', $testData);

        //検証
        $response->assertInvalid([
        'name' => 'お名前を入力してください',
        'email' => 'メールアドレスを入力してください',
        'password' => 'パスワードを入力してください'
        ]);

    }

    /**
     * @test
     * パスワードが7文字以下の場合、バリデーションメッセージが表示される
     */
    public function registration_fails_if_password_is_too_short(): void
    {
        //準備
        $testData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ];

        //実行
        $response = $this->post('/register', $testData);

        //検証
        $response->assertInvalid([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /**
     * @test
     * 確認用パスワードが一致しない場合、バリデーションメッセージが返ってくる
     */
    public function registration_fails_if_passwords_do_not_match(): void
    {
        //準備
        $testData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        //実行
        $response = $this->post('/register', $testData);

        //検証
        $response->assertInvalid([
            'password_confirmation' => 'パスワードと一致しません',
        ]);
    }

    /**
     * @test
     * 全ての項目が正しい場合、ユーザーが作成され、プロフィール設定画面にリダイレクトされる
     */
    public function user_can_register_successfully(): void
    {
        //準備
        $testData = [
            'name' => 'テスト太郎',
            'email' => 'success@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        //実行
        $response = $this->post('/register', $testData);

        //検証
        //バリデーションエラーが「ない」ことを確認
        $response->assertValid();

        //データベースに、このユーザーが作成されたことを確認
        $this->assertDatabaseHas('users', [
            'email' => 'success@example.com',
        ]);

        //ユーザーが「ログイン済み」状態になっていることを確認
        $this->assertAuthenticated();

        //プロフィール設定ページにリダイレクトされたことを確認
        $response->assertRedirect('/mypage/profile');
    }

    // ★ログイン関連のテスト★

    /**
     * @test
     * ログイン時に、メールアドレスとパスワードが空の場合、バリデーションメッセージが返ってくる
     */
    public function login_fails_with_missing_credentials(): void
    {
        //実行
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        //検証
        $response->assertInvalid([
            'email' => 'メールアドレスを入力してください',
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * @test
     * 入力情報が間違っている場合、バリデーションメッセージが表示される
     */
    public function login_fails_with_incorrect_credentials(): void
    {
        //準備
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        //実行
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        //検証
        $response->assertInvalid([
            'email' => 'ログイン情報が登録されていません',
        ]);

        // ログイン「できていない」ことを確認
        $this->assertGuest();
    }

    /**
     * @test
     * 正しい情報が入力された場合、ログイン処理が実行される
     */
    public function user_can_login_successfully(): void
    {
        //準備
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        //実行
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'correct-password',
        ]);

        //検証
        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    /**
     * @test
     * ログインしているユーザーは、ログアウトできる
     */
    public function authenticated_user_can_logout(): void
    {
        //準備
        $user = User::factory()->create();
        //ログイン状態にする
        $this->actingAs($user);

        //実行
        $response = $this->post('/logout');

        //検証
        $this->assertGuest();
        $response->assertRedirect('/');
    }

    // ★メール認証機能

    /**
     * @test
     * 会員登録後、認証メールが送信される
     */
    public function a_user_can_register_and_verify_their_email(): void
    {
        //準備
        //NotificationファサードとEventファサードをモック化
        Notification::fake();
        Event::fake();

        $userData = [
            'name'                  => '認証テストユーザー',
            'email'                 => 'verify@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ];

        // 1.「会員登録とメール送信」のテスト

        //実行
        $this->post('/register', $userData);

        //検証
        $this->assertDatabaseHas('users', ['email' => 'verify@example.com']);

        $user = User::where('email', 'verify@example.com')->first();

        //ユーザー宛に、認証メールが「送信された」ことを確認
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);

        // 2.遷移テスト：メール認証サイトのメール認証を完了すると、商品一覧ページに遷移する

        //準備
        //Laravelが生成する「認証用URL」を作成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', // ルート名
            now()->addMinutes(60), // 有効期限
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        //実行
        $response = $this->actingAs($user)->get($verificationUrl);

        //検証
        //ユーザーが「認証済み」になったことを確認
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        //認証完了のイベントが発生したことを確認
        Event::assertDispatched(Verified::class);

        //トップページにリダイレクトされたことを確認
        $response->assertRedirect('/?verified=1');
    }
}
