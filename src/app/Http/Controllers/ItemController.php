<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;


class ItemController extends Controller
{
    public function index(Request $request)
    {
        // 1. まず、Itemを取得するためのクエリの準備を始める
        $query = Item::query();

        if (!empty($request->keyword)) {
            $keyword = $request->input('keyword');
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        // 3. URLに ?tab=mylist が付いているかどうかをチェック
        if ($request->input('tab') === 'mylist') {
            // whereHasを使って、ログインしているユーザーがいいねした商品だけに絞り込む
            $query->whereHas('likes', function($q) {
                $q->where('user_id', Auth::id());
            });

        } else {
            // もしログインしていれば、自分が出品した商品を除外する
            if (Auth::check()) {
                $query->where('user_id', '!=', Auth::id());
            }
        }

        // 4. 最後に、準備した全ての条件で、新しい順に商品データを取得する
        $items = $query->latest()->get();

        return view('index', compact('items'));

    }

    public function create()
    {
        $categories = Category::all();
        return view('exhibition',compact('categories'));
    }

    public function store(ExhibitionRequest $request)
    {
        $form = $request->validated();

        $form['user_id'] = Auth::id();
        $categoryIds = $form['categories'];
        unset($form['categories']);

        $path = $request->file('img')->store('items', 'public');
        $form['img'] = $path;
        $item = Item::create($form);

        $item->categories()->attach($categoryIds);
        return redirect('/');
    }

    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);
        $comments = Comment::where('item_id', $item_id)
            ->with('user')->latest()->take(3)->get();

        return view('item',compact('item','comments'));
    }
}
