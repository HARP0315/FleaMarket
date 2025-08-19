@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')
<div class="shipping-address">
    <h2 class="shipping-address__ttl">住所の変更</h2>
    <form action="/purchase/address/{{$item->id}}" method="post" class="shipping-address__form">
        @csrf
        <div class="shipping-address__group">
            <label for="post_code" class="shipping-address__label form__label">郵便番号</label>
            <input type="text" name="post_code" id="post_code" class="shipping-address__input" value="{{ old('post_code', $user->post_code) }}">
        </div>
        <div class="shipping-address__group">
            <label for="address" class="shipping-address__label form__label">住所</label>
            <input type="text" name="address" id="address" class="shipping-address__input" value="{{ old('address',$user->address) }}">
        </div>
        <div class="shipping-address__group">
            <label for="building" class="shipping-address__label form__label">建物名</label>
            <input type="text" name="building" id="building" class="shipping-address__input" value="{{ old('building',$user->building) }}">
        </div>
        <input type="submit" value="更新する" class="shipping-address__submit">
    </form>
</div>

@endsection
