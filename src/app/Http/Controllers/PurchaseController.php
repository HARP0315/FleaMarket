<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use App\Models\Purchase;
use App\Models\Address;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function create($item_id)
    {
        $user = Auth::user();
        $item = Item::find($item_id);

        return view('address',compact('user','item'));
    }

}
