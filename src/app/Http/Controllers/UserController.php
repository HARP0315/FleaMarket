<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Models\Item;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request){

        $user = Auth::user();

        $tab = $request->query('page','sell');

        if($tab==='sell'){
            $items = $user->items;
        }
        else{
            $items = $user->purchases()->with('item')->get()->pluck('item');
        }

        return view('profile',compact('user','items'));
    }

    public function edit(){

        $user = Auth::user();

        return view('edit_profile',compact('user'));

    }

    public function update(ProfileRequest $request){

        $user = Auth::user();
        $form = $request->validated();

        // 2. もし画像ファイルが送信されてきたら、保存処理を行う
        if ($request->hasFile('img')) {
        // 'storage/app/public/profiles'フォルダに画像を保存し、そのパスを取得
        $path = $request->file('img')->store('profiles', 'public');
        // フォームのデータに、画像のパスを上書きする
        $form['img'] = $path;
    }

        // 3. ログインしているユーザー自身の情報を更新する
        $user->update($form);

        return redirect('/mypage/profile')->with('success', 'プロフィールを更新しました！');

    }
}
