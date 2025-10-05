<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Auth;

/**
 * ユーザー関連の操作を扱うコントローラー
 */
class UserController extends Controller
{
    /**
     * ユーザーのプロフィールページを表示
     * @param Request $request
     * @return \Illuminate\View\View
     */
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

    /**
     * ユーザーのプロフィール編集ページを表示
     * @return \Illuminate\View\View
     */
    public function edit(){

        $user = Auth::user();

        return view('edit_profile',compact('user'));
    }

    /**
     * ユーザーのプロフィール情報を更新
     * @param ProfileRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileRequest $request){

        $user = Auth::user();
        //初回更新かどうかを判断し、記憶しておく
        $isFirstProfileUpdate = empty($user->post_code);

        $form = $request->validated();

        if ($request->hasFile('img')) {
            // 'storage/app/public/profiles'フォルダに画像を保存し、そのパスを取得
            $path = $request->file('img')->store('profiles', 'public');
            $form['img'] = $path;
        }

        $user->update($form);

        //リダイレクト先の切り替え
        if ($isFirstProfileUpdate) {
            return redirect('/');
        } else {
            return redirect('/mypage')->with('success', 'プロフィールを更新しました！');
        }
    }
}
