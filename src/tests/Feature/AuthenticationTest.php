<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

        // --- 準備 (Arrange) ---
        // テストで送信するための、名前(name)だけが空のデータを用意する
        $testData = [
            'name'                  => '',
            'email'                 => '',
            'password'              => '',
            'password_confirmation' => 'password123',
        ];

        // --- 実行 (Act) ---
        // 会員登録ページ('/register')に、用意したデータをPOSTリクエストで送信する
        $response = $this->post('/register', $testData);

        // --- 検証 (Assert) ---
        // バリデーションエラーがあり、かつ、そのメッセージが「お名前を入力してください」であることを一度に確認する
        $response->assertInvalid([
        'name' => 'お名前を入力してください',
        'email' => 'メールアドレスを入力してください',
        'password' => 'パスワードを入力してください'
        ]);

        // 3. ユーザーがデータベースに作成「されていない」ことも、念のため確認する
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
    }

    /**
     * @test
     * パスワードが短すぎる場合、バリデーションエラーが返ってくる
     */
    public function registration_fails_if_password_is_too_short(): void
    {
        // --- 準備 (Arrange) ---
        $testData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567', // 7文字のパスワード
            'password_confirmation' => '1234567',
        ];

        // --- 実行 (Act) ---
        $response = $this->post('/register', $testData);

        // --- 検証 (Assert) ---
        $response->assertInvalid([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /**
     * @test
     * 確認用パスワードが一致しない場合、バリデーションエラーが返ってくる
     */
    public function registration_fails_if_passwords_do_not_match(): void
    {
        // --- 準備 (Arrange) ---
        $testData = [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        // --- 実行 (Act) ---
        $response = $this->post('/register', $testData);

        // --- 検証 (Assert) ---
        // RegisterRequestで'confirmed'ルールを使っている場合、
        // エラーキーは'password'になります。
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
        // --- 準備 (Arrange) ---
        $testData = [
            'name' => 'テスト太郎',
            'email' => 'success@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // --- 実行 (Act) ---
        $response = $this->post('/register', $testData);

        // --- 検証 (Assert) ---
        // 1. バリデーションエラーが「ない」ことを確認
        $response->assertValid();

        // 2. データベースに、このユーザーが作成されたことを確認
        $this->assertDatabaseHas('users', [
            'email' => 'success@example.com',
        ]);

        // 3. ユーザーが「ログイン済み」状態になっていることを確認
        $this->assertAuthenticated();

        // 4. 正しくプロフィール設定ページにリダイレクトされたことを確認
        $response->assertRedirect('/mypage/profile');
    }

    // ★ログイン関連のテスト★

    /**
     * @test
     * ログイン時に、メールアドレスとパスワードが空の場合、バリデーションエラーが返ってくる
     */
    public function login_fails_with_missing_credentials(): void
    {
        // --- 実行 (Act) ---
        // 空のデータでログインを試みる
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        // --- 検証 (Assert) ---
        // LoginRequestで設定したエラーメッセージが出力されることを確認
        $response->assertInvalid([
            'email' => 'メールアドレスを入力してください',
            'password' => 'パスワードを入力してください',
        ]);
    }

    /**
     * @test
     * 登録されていない情報でログインしようとした場合、エラーが返ってくる
     */
    public function login_fails_with_incorrect_credentials(): void
    {
        // --- 準備 (Arrange) ---
        // テスト用のユーザーを1人作成しておく
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        // --- 実行 (Act) ---
        // 作成したユーザーのメールアドレスと、「間違った」パスワードでログインを試みる
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // --- 検証 (Assert) ---
        // Fortifyが返す認証エラーは'email'キーに紐づく
        // メッセージはLaravel標準のものを期待値とする（変更している場合は、そのメッセージに合わせる）
        $response->assertInvalid([
            'email' => 'ログイン情報が登録されていません',
        ]);

        // ログイン「できていない」ことを確認
        $this->assertGuest();
    }

    /**
     * @test
     * 正しい情報でログインできる
     */
    public function user_can_login_successfully(): void
    {
        // --- 準備 (Arrange) ---
        // テスト用のユーザーを1人作成しておく
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        // --- 実行 (Act) ---
        // 作成したユーザーの正しい情報でログインを試みる
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'correct-password',
        ]);

        // --- 検証 (Assert) ---
        // ログイン「できている」ことを確認
        $this->assertAuthenticated();

        // 正しくホームページ('/')にリダイレクトされたことを確認
        $response->assertRedirect('/');
    }

    /**
     * @test
     * ログインしているユーザーは、正しくログアウトできる
     */
    public function authenticated_user_can_logout(): void
    {
        // --- 準備 (Arrange) ---
        // テスト用のユーザーを1人作成する
        $user = User::factory()->create();

        // このユーザーとして「ログインした状態」を再現する
        $this->actingAs($user);

        // --- 実行 (Act) ---
        // ログアウトのURL('/logout')に、POSTリクエストを送信する
        $response = $this->post('/logout');

        // --- 検証 (Assert) ---
        // 1. ユーザーが「ログアウト済み（ゲスト）」状態になっていることを確認する
        $this->assertGuest();

        // 2. ログアウト後に、正しくログインページにリダイレクトされたことを確認する
        $response->assertRedirect('/');
    }

}
