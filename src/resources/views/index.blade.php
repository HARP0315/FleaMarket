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
        @foreach($items as $item)
        <div class="item-list__item">
            <a href="/item/{{$item->id}}" class="item-list__item-link">
                @if(!$item->purchase)
                    <img src="{{ asset($item->img) }}" alt="{{ $item->name }}" class="item-list__item-img">
                @else
                    <div class="item-list__sold-box">
                        <span class="item-list__sold-box--alert">SOLD</span>
                    </div>
                @endif
            </a>
            <div class="item-list__item-info">
            <p class=item-list__item-name>{{$item->name}}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
