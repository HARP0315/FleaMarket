<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;


class ItemController extends Controller
{
    public function index()
    {
        $items = Item::all();
        return view('index',compact('items'));
    }

    public function search(Request $request)
    {

        $keyword = $request->input('keyword');
        $query = Item::query();

        if(!empty($request->keyword)){
            $query->where('name','like','%'.$request->keyword.'%');
        }

        $items = $query->get();
        return view('index',compact('items'));

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
}
