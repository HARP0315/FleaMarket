<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Item;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;


/**
 * 商品関連の操作を扱うコントローラー
 */
class ItemController extends Controller
{
    /**
     * 商品一覧ページを表示
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Item::query();

        if (!empty($request->keyword)) {
            $keyword = $request->input('keyword');
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        if ($request->input('tab') === 'mylist') {
            $query->whereHas('likes', function($q) {
                $q->where('user_id', Auth::id());
            });

        } else {

            if (Auth::check()) {
                $query->where('user_id', '!=', Auth::id());
            }
        }

        $items = $query->latest()->get();

        return view('index', compact('items'));
    }

    /**
     * 商品出品ページを表示
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = Category::all();
        return view('exhibition',compact('categories'));
    }

    /**
     * 商品情報を保存
     * @param ExhibitionRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * 商品詳細を表示
     *
     * @param [type] $item_id
     * @return void
     */
    public function show($item_id)
    {
        $item = Item::findOrFail($item_id);
        $comments = Comment::where('item_id', $item_id)
            ->with('user')->latest()->take(3)->get();

        return view('item',compact('item','comments'));
    }
}
