@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/item.css') }}">
@endsection

@section('content')
<div class="item-info">
    <div class="item-info__item-img">
        <img src="パス" alt="商品画像">
        @if(あとで->purchase)
                    <div class="item-info__sold-overlay">
                        <span>SOLD</span>
                    </div>
        @endif
    </div>
    <div class="item-info__item-info">
        <h1 class="item-info__item-info__heading"></h1>

    </div>

</div>

@endsection
