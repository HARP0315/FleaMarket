@extends('layouts/app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/address.css') }}">
@endsection

@section('content')
<div class="address-form">
    <h2 class="address-form__ttl">住所の変更</h2>
    <form action="/purchase/address/{{$item->id}}" method="post" class="address-form__form">
        @csrf
        {{-- 郵便番号 --}}
        <div class="address-form__group">
            <label for="post_code" class="address-form__label">郵便番号</label>
            <input
             type="text"
             name="post_code"
             id="post_code"
             class="address-form__input"
             value="{{ old('post_code', $address->post_code) }}"
            >
            @error('post_code')
                <p class="address-form__error-message">{{ $message }}</p>
            @enderror
        </div>
        {{-- 住所 --}}
        <div class="address-form__group">
            <label for="address" class="address-form__label">住所</label>
            <input
             type="text"
             name="address"
             id="address"
             class="address-form__input"
             value="{{ old('address',$address->address) }}"
            >
            @error('address')
                <p class="address-form__error-message">{{ $message }}</p>
            @enderror
        </div>
        {{-- 建物名 --}}
        <div class="address-form__group">
            <label for="building" class="address-form__label">建物名</label>
            <input
             type="text"
             name="building"
             id="building"
             class="address-form__input"
             value="{{ old('building',$address->building) }}"
            >
        </div>
        <input type="submit" value="更新する" class="address-form__submit">
    </form>
</div>
@endsection
