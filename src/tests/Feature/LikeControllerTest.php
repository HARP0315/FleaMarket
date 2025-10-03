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
     * 商品をいいねしたり、いいねを解除したりでき且つアイコンの色が変わる
     */
    public function an_authenticated_user_can_like_and_unlike_an_item(): void
    {
        //準備
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $itemUrl = '/item/' . $item->id;

        //「いいね」するテスト

        //実行
        $response = $this->actingAs($user)
            ->from($itemUrl)->post('/item/' . $item->id . '/like');

        //検証
        $response->assertRedirect('/item/' . $item->id);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->get('/item/' . $item->id)
            ->assertSee('fa-solid fa-star', false);//黒塗り星

        //「いいね解除」するテスト

        //実行
        $response = $this->actingAs($user)
            ->from($itemUrl)->delete('/item/' . $item->id . '/unlike');

        //検証
        $response->assertRedirect('/item/' . $item->id);

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $response = $this->get('/item/' . $item->id)
            ->assertSee('fa-regular fa-star', false);//枠線のみ星

    }
}
