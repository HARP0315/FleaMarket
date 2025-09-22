<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class LikeControllerTest extends TestCase
{

        use RefreshDatabase;

    /**
     * @test
     * ログインユーザーは、商品をいいねしたり、いいねを解除したりできる
     */
    public function an_authenticated_user_can_like_and_unlike_an_item(): void
    {
        // --- 準備 (Arrange) ---
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $itemUrl = '/item/' . $item->id;

        // 1.「いいね」するテスト

        // --- 実行 (Act) ---
        // ログインした状態で、いいねボタンを押す（POSTリクエストを送信）
        $response = $this->actingAs($user)
            ->from($itemUrl)->post('/item/' . $item->id . '/like');

        // --- 検証 (Assert) ---
        // 1. 正しく元のページにリダイレクトされるか
        $response->assertRedirect('/item/' . $item->id);

        // 2. likesテーブルに、ちゃんとデータが保存されたか
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        // 2.「いいね解除」するテスト

        // --- 実行 (Act) ---
        // 続けて、いいね解除ボタンを押す（DELETEリクエストを送信）
        $response = $this->actingAs($user)
            ->from($itemUrl)->delete('/item/' . $item->id . '/unlike');

        // --- 検証 (Assert) ---
        // 1. 正しく元のページにリダイレクトされるか
        $response->assertRedirect('/item/' . $item->id);

        // 2. likesテーブルから、データがちゃんと削除されたか
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);
    }
}
