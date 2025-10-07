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
     * ログインしているユーザーは、コメントを投稿できる＆コメント数が増加する
     */
    public function an_authenticated_user_can_post_a_comment(): void
    {
        //準備
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $commentData = ['content' => 'これはテストコメントです。'];

        //実行
        $response = $this->actingAs($user)
            ->from('/item/' . $item->id)
            ->post('/item/' . $item->id . '/comments', $commentData);

        //検証
        $response->assertRedirect('/item/' . $item->id);

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'これはテストコメントです。',
        ]);

        $response = $this->get('/item/' . $item->id)
            ->assertSee('コメント（1）', false);
    }

    /**
     * @test
     * ゲストは、コメントを送信できない
     */
    public function a_guest_cannot_post_a_comment(): void
    {
        //準備
        $item = Item::factory()->create();
        $commentData = ['content' => 'このコメントは保存されないはず'];

        //実行
        $response = $this->from('/item/' . $item->id)
            ->post('/item/' . $item->id . '/comments', $commentData);

        //検証
        $response->assertRedirect('/item/' . $item->id);

        //「ログインが必要です」というエラーメッセージがセッションにあるか
        $response->assertSessionHas('comment_error', 'コメントを投稿するには、ログインが必要です');

        //commentsテーブルに、データが保存「されていない」ことを確認
        $this->assertDatabaseMissing('comments', $commentData);
    }

    /**
     * @test
     * コメントが入力されていない場合、バリデーションメッセージが表示される
     * コメントが255字以上の場合、バリデーションメッセージが表示される
     */
    public function comment_submission_is_validated(): void
    {
        //準備
        $user = User::factory()->create();
        $item = Item::factory()->create();

        //コメントが空の場合
        $response = $this->actingAs($user)->post('/item/' . $item->id . '/comments', ['content' => '']);
        $response->assertInvalid(['content' => 'コメントを入力してください']);

        //コメントが255文字を超える場合
        $longComment = str_repeat('a', 256);
        $response = $this->actingAs($user)->post('/item/' . $item->id . '/comments', ['content' => $longComment]);
        $response->assertInvalid(['content' => 'コメントは255文字以内で入力してください']);
    }
}
