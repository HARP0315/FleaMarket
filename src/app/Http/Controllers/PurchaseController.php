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

        // 購入情報を保存
        $user->purchases()->create([
            'item_id' => $item->id,
            'address_id' => $address->id,
            'price' => $item->price,
            'payment_method' => $form['payment_method'],
        ]);

        // ★★★ 使った一時的な住所をセッションから削除 ★★★
        $request->session()->forget('temporary_address');

        return redirect('/');

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

}
