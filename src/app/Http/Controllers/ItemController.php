<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ExhibitionRequest;
use App\Models\Item;
use App\Models\Category;
use App\Models\Condition;


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
}
