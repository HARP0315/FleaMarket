<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use App\Models\Comment;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(CommentRequest $request,Item $item)
    {
        $form = $request->validated();
        $form['user_id'] = Auth::id();
        $item->comments()->create($form);

        return back();
    }
}
