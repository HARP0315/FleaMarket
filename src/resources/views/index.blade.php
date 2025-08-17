@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')
<div class="item-list">
    <div class="item-list__tab">
        <a href="/" class="item-list__tab-link {{ request()->query('tab') !== 'mylist' ? 'item-list__tab-link--active' : ''}}">おすすめ</a>
        <a href="/?tab=mylist" class="item-list__tab-link {{ request()->query('tab') === 'mylist' ? 'item-list__tab-link--active' : '' }}">マイリスト</a>
    </div>
    <div class="item-list__content">
        @foreach(あとで as あとで)
        <div class="item-list__item">
            <a href="/item/{item_id}" class="item-list__item-link">
                <img src="{{asset(あとで->あとで)}}" alt="商品画像" class="item-list__item-img">
                @if(あとで->purchase)
                        <div class="item-list__sold-overlay">
                            <span>SOLD</span>
                        </div>
                    @endif
            </a>
            <div class="item-list__item-info">
            <p class=item-list__item-name>商品名持ってくる</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
