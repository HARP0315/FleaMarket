<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use Stripe\Stripe;
use Stripe\Checkout\Session;

/**
 * 商品購入に関するコントローラー（stripe通知は別）
 */
class PurchaseController extends Controller
{

    /**
     * 送付先住所決め関数
     *
     * @param Request $request
     * @return void
     */
    private function getCurrentAddress(Request $request)
    {
        $user = Auth::user();

        //送付先住所変更ページで一時保存した住所があれば
        $temporaryAddress = $request->session()->get('temporary_address');
        if ($temporaryAddress) {
            return new Address($temporaryAddress);
        }

        //一時保存がなく、addressesテーブルに過去に使用した送付先住所があれば
        $latestAddress = $user->shippingAddress()->latest()->first();
        if ($latestAddress) {
            return $latestAddress;
        }

        //上2つがない場合、ユーザ登録住所を返す
        return new Address([
            'post_code' => $user->post_code,
            'address'   => $user->address,
            'building'  => $user->building,
        ]);
    }

    /**
     * 商品購入ページの表示
     *
     * @param Request $request
     * @param [type] $item_id
     * @return void
     */
    public function create(Request $request,$item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        $address = $this->getCurrentAddress($request);

        return view('purchase',compact('item','address'));
    }

    /**
     * 購入機能（stripeへの決済情報渡し）
     *
     * @param PurchaseRequest $request
     * @param [type] $item_id
     * @return void
     */
    public function store(PurchaseRequest $request,$item_id)
    {

        $user = Auth::user();
        $item = Item::findOrFail($item_id);

        // 自分が出品した商品は購入不可
        if ($item->user_id === $user->id) {
            return redirect('/item/' . $item->id)
                ->with('error', 'ご自身が出品した商品は購入できません');
        }

        // 既に購入されているかチェック
        $existingPurchase = $item->purchase()->where('is_deleted', 0)->first();
        if ($existingPurchase) {
            return redirect('/item/' . $item->id)
                ->with('error', 'この商品はすでに売却済みです');
        }

        $form = $request->validated();

        //Stripe Checkout に送る
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Checkout セッション作成前に仮注文レコードを保存
        $purchase = Purchase::firstOrCreate([
            'item_id' => $item->id,
            'user_id' => $user->id,
            'price' => $form['price'],
            'payment_method' => $form['payment_method'],
            'payment_status' => 0, // 未決済
            'is_deleted' => 0, //有効
        ]);

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

            'payment_intent_data' => [
                'metadata' => [
                    'item_id'     => $item->id,
                    'user_id'     => $user->id,
                    'post_code'   => $form['post_code'],
                    'address'     => $form['address'],
                    'building'    => $form['building'] ?? '',
                    'payment_method' => $form['payment_method'],
                    'purchase_id' => $purchase->id,
                ],
            ],

            'metadata' => [
                'item_id' => $item->id,
                'user_id' => $user->id,
                'post_code'   => $form['post_code'],
                'address'     => $form['address'],
                'building'    => $form['building'] ?? '',
                'payment_method' => $form['payment_method'],
                'purchase_id' => $purchase->id,
            ],
        ]);

        return redirect($session->url, 303);
    }

    /**
     * 送付先住所変更ページの表示
     *
     * @param Request $request
     * @param [type] $item_id
     * @return void
     */
    public function edit(Request $request,$item_id)
    {
        $item = Item::findOrFail($item_id);
        $address = $this->getCurrentAddress($request);

        return view('address',compact('item','address'));
    }

    /**
     * 送付先住所の更新（セッションで持たせる）
     *
     * @param AddressRequest $request
     * @param [type] $item_id
     * @return void
     */
    public function update(AddressRequest $request,$item_id)
    {

        $address = $request->validated();
        $request->session()->put('temporary_address', $address);

        return redirect('/purchase/' . $item_id);
    }

    /**
     * 決済成功時の処理
     */
    public function success()
    {
        return redirect('/')->with('success', '購入が完了しました！');
    }

    /**
     * 決済キャンセル時の処理
     */
    public function cancel($item_id)
    {
        $user = Auth::user();

        // 該当する仮購入レコードを削除
        $purchase = Purchase::where('item_id', $item_id)
            ->where('user_id', $user->id)
            ->where('payment_status', 0) // 未決済のみ削除
            ->first();

        if ($purchase) {
            $purchase->delete();
        }

        return redirect('/item/' . $item_id)
            ->with('error', '購入がキャンセルされました。');
    }
}

