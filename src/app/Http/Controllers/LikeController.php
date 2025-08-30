<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function store(Item $item)
    {
        Auth::user()->likes()->attach($item->id);

        return back();

    }

    public function destroy(Item $item)
    {
        Auth::user()->likes()->detach($item->id);

        return back();
    }

}
