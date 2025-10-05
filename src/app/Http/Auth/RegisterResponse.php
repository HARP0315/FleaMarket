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
        $user = $request->user();

        // プロフィールが未設定なら、プロフィール編集ページへ
        if (empty($user->post_code)) {
            return redirect('/mypage/profile');
        }

        return redirect(config('fortify.home'));
    }
}
