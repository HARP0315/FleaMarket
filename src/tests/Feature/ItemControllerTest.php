<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Category;
use App\Models\Like;
use App\Models\Comment;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;

    // ★商品一覧取得 関連のテスト★

    /**
     * @test
     * ゲストユーザーは、商品一覧ページで、自分以外のユーザーが出品した全ての商品を見ることができ、売り切れの商品には「SOLD」と表示される
     */
    public function a_guest_can_view_all_items_with_sold_status(): void
    {
        // --- 準備 (Arrange) ---
        // 1. テスト用のユーザーを2人作成
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 2. user1が出品した、まだ売れていない商品を作成
        $itemNotSold = Item::factory()->create(['user_id' => $user1->id, 'name' => 'まだ売れていない商品']);

        // 3. user1が出品した、user2が購入済みの商品を作成
        $itemSold = Item::factory()->create(['user_id' => $user1->id, 'name' => '売り切れの商品']);
        Purchase::factory()->create([
            'user_id' => $user2->id,
            'item_id' => $itemSold->id,
        ]);

        // --- 実行 (Act) ---
        // トップページ('/')にGETリクエストを送信する
        $response = $this->get('/');

        // --- 検証 (Assert) ---
        // 1. ページが正しく表示されることを確認
        $response->assertStatus(200);

        // 2. まだ売れていない商品の名前が表示されていることを確認
        $response->assertSee('まだ売れていない商品');

        // 3. 売り切れの商品の名前も表示されていることを確認
        $response->assertSee('売り切れの商品');

        // 4. HTMLの中に「SOLD」という文字が含まれていることを確認
        $response->assertSee('SOLD');
    }

    /**
     * @test
     * ログインしているユーザーは、商品一覧ページで、自分自身が出品した商品が表示されない
     */
    public function an_authenticated_user_cannot_see_their_own_items_on_the_index_page(): void
    {
        // --- 準備 (Arrange) ---
        // 1. テスト用のユーザーを2人作成
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // 2. userA（自分）が出品した商品を作成
        $itemFromUserA = Item::factory()->create(['user_id' => $userA->id, 'name' => '自分が出品した商品']);

        // 3. userB（他人）が出品した商品を作成
        $itemFromUserB = Item::factory()->create(['user_id' => $userB->id, 'name' => '他人が出品した商品']);

        // --- 実行 (Act) ---
        // userAとしてログインした状態で、トップページ('/')にGETリクエストを送信する
        $response = $this->actingAs($userA)->get('/');

        // --- 検証 (Assert) ---
        // 1. ページが正しく表示されることを確認
        $response->assertStatus(200);

        // 2. 自分が出品した商品の名前が、HTMLに「含まれていない」ことを確認
        $response->assertDontSee('自分が出品した商品');

        // 3. 他人が出品した商品の名前が、HTMLに「含まれている」ことを確認
        $response->assertSee('他人が出品した商品');
    }

    // ★マイリスト一覧取得 関連のテスト★

    /**
     * @test
     * ログインユーザーは、マイリストで自分がいいねした商品だけを見ることができ、売り切れの商品には「SOLD」と表示される
     */
    public function an_authenticated_user_can_view_their_liked_items_with_sold_status(): void
    {
        // --- 準備 (Arrange) ---
        // 1. テスト用のユーザーを作成
        $user = User::factory()->create();

        // 2. ユーザーがいいねした、まだ売れていない商品を作成
        $likedItemNotSold = Item::factory()->create(['name' => 'いいねした未売却の商品']);
        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $likedItemNotSold->id,
        ]);

        // 3. ユーザーがいいねした、購入済みの商品を作成
        $likedItemSold = Item::factory()->create(['name' => 'いいねした売却済みの商品']);
        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $likedItemSold->id,
        ]);
        Purchase::factory()->create(['item_id' => $likedItemSold->id]);

        // 4. ユーザーがいいね「していない」商品も作成
        $notLikedItem = Item::factory()->create(['name' => 'いいねしていない商品']);

        // --- 実行 (Act) ---
        // ログインした状態で、マイリストタブ('/?tab=mylist')にGETリクエストを送信する
        $response = $this->actingAs($user)->get('/?tab=mylist');

        // --- 検証 (Assert) ---
        // 1. ページが正しく表示されることを確認
        $response->assertStatus(200);

        // 2. いいねした商品（未売却・売却済み）の両方が表示されていることを確認
        $response->assertSee('いいねした未売却の商品');
        $response->assertSee('いいねした売却済みの商品');

        // 3. いいねしていない商品は「表示されていない」ことを確認
        $response->assertDontSee('いいねしていない商品');

        // 4. HTMLの中に「SOLD」という文字が含まれていることを確認
        $response->assertSee('SOLD');
    }

    /**
     * @test
     * ゲストは、マイリストページにアクセスしても、商品は何も表示されない
     */
    public function a_guest_sees_no_items_on_the_mylist_page(): void
    {
        // --- 準備 (Arrange) ---
        // 誰かがいいねした商品を1つ作成しておく
        $item = Item::factory()->create(['name' => '誰かがいいねした商品']);
        Like::factory()->create(['item_id' => $item->id]);

        // --- 実行 (Act) ---
        // ゲストとしてマイリストタブ('/?tab=mylist')にGETリクエストを送信する
        $response = $this->get('/?tab=mylist');

        // --- 検証 (Assert) ---
        // 1. ページは正しく表示されることを確認
        $response->assertStatus(200);

        // 2. しかし、商品は何も表示されていないことを確認
        $response->assertDontSee('誰かがいいねした商品');
    }

    // ★検索機能 関連のテスト★

    /**
     * @test
     * 商品名での部分一致検索ができ、その検索状態はマイリストタブでも保持される
     */
    public function it_can_search_items_by_name_and_persist_the_keyword_across_tabs(): void
    {
        // --- 準備 (Arrange) ---
        // 1. テスト用のユーザーを作成
        $user = User::factory()->create();

        // 2. 検索キーワードにヒットする商品を作成
        $matchingItem1 = Item::factory()->create(['name' => 'すごい腕時計']);
        $matchingItem2 = Item::factory()->create(['name' => 'アンティークな腕時計']);

        // 3. 検索キーワードにヒットしない商品を作成
        $nonMatchingItem = Item::factory()->create(['name' => 'ただのTシャツ']);

        // 4. matchingItem1だけをユーザーがいいねしておく
        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $matchingItem1->id,
        ]);

        // --- 実行 & 検証 (Act & Assert) ---

        // === おすすめタブで検索 ===
        $response = $this->actingAs($user)->get('/?keyword=腕時計');

        $response->assertStatus(200);
        $response->assertSee('すごい腕時計');
        $response->assertSee('アンティークな腕時計');
        $response->assertDontSee('ただのTシャツ');

        // === マイリストタブでも検索状態が保持されるか ===
        // 検索キーワード「腕時計」を維持したまま、マイリストタブに移動
        $response = $this->actingAs($user)->get('/?tab=mylist&keyword=腕時計');

        $response->assertStatus(200);
        // マイリストでは、いいねした「すごい腕時計」だけが表示されるはず
        $response->assertSee('すごい腕時計');
        // いいねしていない「アンティークな腕時計」は表示されないはず
        $response->assertDontSee('アンティークな腕時計');

        // 検索キーワードが入力欄に残っている（保持されている）ことを確認
        $response->assertSee('<input type="search" name="keyword" placeholder="なにをお探しですか？" value="腕時計"', false);
    }

    // ★商品詳細情報取得 関連のテスト★

    /**
     * @test
     * 商品詳細ページで、関連する情報が全て正しく表示される
     */
    public function it_displays_all_necessary_information_on_the_item_detail_page(): void
    {
        // --- 準備 (Arrange) ---
        // 1. 必要なデータを作成
        $seller = User::factory()->create();
        $commenter = User::factory()->create(['name' => 'コメントユーザーA']);

        $category1 = Category::factory()->create(['content' => 'ファッション']);
        $category2 = Category::factory()->create(['content' => 'メンズ']);

        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'name' => 'テスト用高級腕時計',
            'brand' => 'テストブランド',
            'description' => 'これは商品の説明文です。',
            'condition' => 1,
            'price' => 50000,
        ]);

        // 2. 作成した商品に、複数のカテゴリとコメントを紐付ける
        $item->categories()->attach([$category1->id, $category2->id]);

        Comment::factory()->create([
            'item_id' => $item->id,
            'user_id' => $commenter->id,
            'content' => 'このコメントは表示されるはずです。',
        ]);

        Like::factory()->count(5)->create(['item_id' => $item->id]);

        // --- 実行 (Act) ---
        // 作成した商品の詳細ページにGETリクエストを送信する
        $response = $this->get('/item/' . $item->id);

        // --- 検証 (Assert) ---
        // 1. ページが正しく表示されることを確認
        $response->assertStatus(200);

        // 2. 商品の基本情報が表示されていることを確認
        $response->assertSee('テスト用高級腕時計');
        $response->assertSee('テストブランド');
        $response->assertSee('¥50,000');
        $response->assertSee('これは商品の説明文です。');

        // 3. 関連情報（カテゴリ、状態）が表示されていることを確認
        $response->assertSee('ファッション');
        $response->assertSee('メンズ');
        $response->assertSee('良好');

        // 4. いいね数とコメント数が表示されていることを確認
        $response->assertSee('<span class="item-page__like-count">5</span>', false);
        $response->assertSee('<span class="item-page__comment-count">1</span>', false);

        // 5. コメントしたユーザーの情報と内容が表示されていることを確認
        $response->assertSee('コメントユーザーA');
        $response->assertSee('このコメントは表示されるはずです。');
    }

    // ★商品情報登録機能 のテスト★

    /**
     * @test
     * ログインユーザーは、商品出品ページで必要な情報を全て正しく保存できる
     */
    public function an_authenticated_user_can_create_a_new_item(): void
    {
        // --- 準備 (Arrange) ---
        // 1. テスト用のユーザーとカテゴリを作成
        $user = User::factory()->create();
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();

        // 2.テスト用の架空の画像ファイルを作成
        Storage::fake('public'); // publicディスクを偽のストレージに置き換える
        $dummyImage = UploadedFile::fake()->image('test_image.jpg', 100, 100);

        // 3. フォームで送信するテストデータを用意
        $itemData = [
            'name' => 'テスト出品商品',
            'brand' => 'テストブランド',
            'description' => 'これは商品説明です。',
            'img' => $dummyImage,
            'condition' => '1',
            'categories' => [$category1->id, $category2->id],
            'price' => 12345,
        ];

        // --- 実行 (Act) ---
        // ログインした状態で、商品出品URL('/sell')にPOSTリクエストを送信
        $response = $this->actingAs($user)->post('/sell', $itemData);

        // --- 検証 (Assert) ---
        // 1. 正しくホームページ('/')にリダイレクトされたか
        $response->assertRedirect('/');

        // 2. itemsテーブルに、基本的な商品情報が保存されたか
        $this->assertDatabaseHas('items', [
            'user_id' => $user->id,
            'name' => 'テスト出品商品',
            'brand' => 'テストブランド',
            'price' => 12345,
        ]);

        // 3. 画像ファイルが、実際にストレージに保存されたか
        //    (データベースに保存されたパスを元に、ファイルが存在するかを確認)
        $item = Item::first(); // 作成された最初の商品を取得
        Storage::disk('public')->assertExists($item->img);

        // 4. category_itemテーブルに、2つのカテゴリが正しく紐付けられたか
        $this->assertDatabaseHas('category_item', [
            'item_id' => $item->id,
            'category_id' => $category1->id,
        ]);
        $this->assertDatabaseHas('category_item', [
            'item_id' => $item->id,
            'category_id' => $category2->id,
        ]);
    }
}
