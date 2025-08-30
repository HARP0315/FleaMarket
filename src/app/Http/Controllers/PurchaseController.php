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

    public function create(Request $request,$item_id)
    {
        $item = Item::findOrFail($item_id);
        $user = Auth::user();

        $address = $user;

        if($request->has('address_id')){
            $shippingAddress = Address::where('user_id',$user->id)
                ->where('id',$request->address_id)
                ->first();

            if ($shippingAddress) {
                $address = $shippingAddress;
            }
        };

        return view('purchase',compact('item','address'));
    }


    public function edit($item_id)
    {
        $user = Auth::user();
        $item = Item::find($item_id);

        return view('address',compact('user','item'));
    }

    public function update(AddressRequest $request,$item_id)
    {

        $form = $request->validated();

        $form['user_id'] = Auth::id();
        $address = Address::firstOrCreate($form);

        return redirect('/purchase' .'/'. $item_id .'?address_id='. $address->id);

    }

}
