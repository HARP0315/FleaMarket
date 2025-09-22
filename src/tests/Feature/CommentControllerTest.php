<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Comment;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * ログインしているユーザーは、コメントを投稿できる
     */
    public function an_authenticated_user_can_post_a_comment(): void
    {
        // --- 準備 (Arrange) ---
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $commentData = ['content' => 'これはテストコメントです。'];

        // --- 実行 (Act) ---
        // ログインした状態で、コメント投稿URLにPOSTリクエストを送信
        $response = $this->actingAs($user)
                         ->from('/item/' . $item->id) // どこから来たかを設定
                         ->post('/item/' . $item->id . '/comments', $commentData);

        // --- 検証 (Assert) ---
        // 1. 正しく元のページ（商品詳細ページ）にリダイレクトされるか
        $response->assertRedirect('/item/' . $item->id);

        // 2. commentsテーブルに、ちゃんとデータが保存されたか
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'これはテストコメントです。',
        ]);
    }

    /**
     * @test
     * ゲストは、コメントを投稿しようとすると、メッセージ付きで元のページに戻される
     */
    public function a_guest_cannot_post_a_comment(): void
    {
        // --- 準備 (Arrange) ---
        $item = Item::factory()->create();
        $commentData = ['content' => 'このコメントは保存されないはず'];

        // --- 実行 (Act) ---
        // ゲストとしてコメントを投稿しようとする
        $response = $this->from('/item/' . $item->id)
                         ->post('/item/' . $item->id . '/comments', $commentData);

        // --- 検証 (Assert) ---
        // 1. 正しく元のページ（商品詳細ページ）にリダイレクトされるか
        $response->assertRedirect('/item/' . $item->id);

        // 2. 「ログインが必要です」というエラーメッセージがセッションにあるか
        $response->assertSessionHas('comment_error', 'コメントを投稿するには、ログインが必要です');

        // 3. commentsテーブルに、データが保存「されていない」ことを確認
        $this->assertDatabaseMissing('comments', $commentData);
    }

    /**
     * @test
     * コメントのバリデーションが正しく機能する
     */
    public function comment_submission_is_validated(): void
    {
        // --- 準備 (Arrange) ---
        $user = User::factory()->create();
        $item = Item::factory()->create();

        // === ケース1: コメントが空の場合 ===
        $response = $this->actingAs($user)->post('/item/' . $item->id . '/comments', ['content' => '']);
        $response->assertInvalid(['content' => 'コメントを入力してください']);

        // === ケース2: コメントが255文字を超える場合 ===
        $longComment = str_repeat('a', 256); // 256文字の'a'を生成
        $response = $this->actingAs($user)->post('/item/' . $item->id . '/comments', ['content' => $longComment]);
        $response->assertInvalid(['content' => 'コメントは255文字以内で入力してください']);
    }
}
