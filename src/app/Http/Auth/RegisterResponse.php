<?php

namespace App\Http\Auth;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // 会員登録が成功すると、そのユーザーは自動でログイン状態になります
        $user = $request->user();

        // ★★★ ユーザーさんの完璧な判定ロジックをここに書く ★★★
        if (empty($user->post_code)) {
            // プロフィールが未設定なら、プロフィール編集ページへ
            return redirect('/mypage/profile');
        }

        // 設定済みなら、通常のホームページへ（RouteServiceProvider::HOMEを参照）
        return redirect(config('fortify.home'));
    }
}
