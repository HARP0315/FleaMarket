<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use App\Models\User;
use App\Models\Purchase;
use App\Models\Address;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class PurchaseController extends Controller
{

    private function getCurrentAddress(Request $request)
    {
        $user = Auth::user();

        // 【優先順位1】セッションに「一時的な住所」があるか？
        $temporaryAddress = $request->session()->get('temporary_address');
        if ($temporaryAddress) {
            // あれば、それを使って仮のAddressオブジェクトを返す
            return new Address($temporaryAddress);
        }

        // 【優先順位2】addressesテーブルに、過去に使った住所があるか？
        $latestAddress = $user->shippingAddress()->latest()->first();
        if ($latestAddress) {
            // あれば、その最新の住所を返す
            return $latestAddress;
        }

        // 【優先順位3】usersテーブルの「デフォルト住所」を使う
        // usersテーブルのカラムから、仮のAddressオブジェクトを作成して返す
        return new Address([
            'post_code' => $user->post_code,
            'address'   => $user->address,
            'building'  => $user->building,
        ]);
    }

    public function create(Request $request,$item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        $address = $this->getCurrentAddress($request);

        return view('purchase',compact('item','address'));
    }

    public function store(PurchaseRequest $request,$item_id)
    {

        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        // ▼▼▼ ここからが今回の追加部分（門番ロジック） ▼▼▼
        // もし、商品の出品者IDと、ログインしているユーザーのIDが同じだったら
        if ($item->user_id === $user->id) {
            // 商品詳細ページに戻し、「自分が出品した商品は購入できません」というエラーメッセージを表示する
            return redirect('/item/' . $item->id)
                ->with('error', 'ご自身が出品した商品は購入できません');
        }
        // ▲▲▲ ここまで ▲▲▲

        $form = $request->validated();

        // フォームから送られてきた住所データを取得
        $shippingAddress = [
            'user_id' => $user->id,
            'post_code' => $form['post_code'],
            'address' => $form['address'],
            'building' => $form['building'] ?? null,
        ];

        // ★★★ このタイミングで、firstOrCreateを使って住所を保存 ★★★
        $address = Address::firstOrCreate($shippingAddress);

        $request->session()->put('purchase_address_id', $address->id);
        $request->session()->put('payment_method', $form['payment_method']);

        // 購入情報はまだ DB には入れず、Stripe Checkout に送る
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Stripe Checkout 作成
        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = Session::create([
            'payment_method_types' => [$form['payment_method'] == 1 ? 'konbini' : 'card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => ['name' => $item->name],
                    'unit_amount' => $item->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('purchase.success'),
            'cancel_url' => route('purchase.cancel', ['item_id' => $item->id]),

            'metadata' => [
                'item_id'        => $item->id,
                'user_id'        => $user->id,
                'address_id'     => $address->id,
                'payment_method' => $form['payment_method'],
            ],
        ]);

        // 4. 作成されたStripeの決済ページURLにリダイレクト
        return redirect($session->url, 303);

    }

    public function edit(Request $request,$item_id)
    {
        $item = Item::findOrFail($item_id);
        $address = $this->getCurrentAddress($request);

        return view('address',compact('item','address'));
    }

    public function update(AddressRequest $request,$item_id)
    {

        $address = $request->validated();
        $request->session()->put('temporary_address', $address);

        return redirect('/purchase/' . $item_id);
    }

    /**
     * 決済成功時の処理
     */
    public function success(Request $request)
    {
        // 1. セッションから、購入された商品IDと住所IDを取得
        $itemId = $request->session()->get('purchase_item_id');
        $addressId = $request->session()->get('purchase_address_id');
        $paymentMethod = $request->session()->get('payment_method');

        // 2. 必要なモデルを取得
        if (!$itemId || !$addressId || !$paymentMethod) {
        // セッションが切れているか、直接アクセスされた場合
        return redirect('/')
            ->with('error', '購入情報が見つかりません。もう一度お試しください。');
    }

        $item = Item::find($itemId);
        if (!$item) {
            return redirect('/')
                ->with('error', '購入対象の商品が見つかりません。');
        }
        $user = Auth::user();

        // 4. 使い終わったセッション情報を削除
        $request->session()->forget(['purchase_item_id', 'purchase_address_id', 'purchase_payment_method']);

        // 5. 購入完了ページを表示
        return redirect('/')->with('success', '購入が完了しました！');
    }

    /**
     * 決済キャンセル時の処理
     */
    public function cancel(Request $request,$item_id)
    {
        // 商品詳細ページに戻し、「支払いがキャンセルされました」というメッセージを表示
        $request->session()->forget(['purchase_item_id', 'purchase_address_id', 'purchase_payment_method']);
        return redirect('/item/' . $item_id)->with('error', '支払いがキャンセルされました。');
    }
}

