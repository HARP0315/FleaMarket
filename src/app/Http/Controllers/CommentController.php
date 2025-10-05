<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

/**
 * コメント管理のコントローラー
 */
class CommentController extends Controller
{

    public function store(CommentRequest $request,Item $item)
    {
        if (Auth::guest()) {
            return back()->with('comment_error', 'コメントを投稿するには、ログインが必要です');
        }

        $form = $request->validated();
        $form['user_id'] = Auth::id();
        $item->comments()->create($form);

        return back();
    }
}
