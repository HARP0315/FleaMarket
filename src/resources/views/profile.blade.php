@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="mypage-list">
    <div class="mypage-list__heading">
        <div class="mypage-list__heading-user-img">
            @if($user->img)
                <img src="{{asset('storage/' . $user->img)}}" alt="画像">
            @else
                <div class="mypage-list__heading-user-img--alternative"></div>
            @endif
        </div>
        <div class="mypage-list__heading-user-name">
            <p class="mypage-list__user-name">{{$user->name}}</p>
        </div>
        <div class="mypage-list__profile-link">
            <a href="/mypage/profile" class="mypage-list__profile-button btn">プロフィールを編集</a>
        </div>
    </div>
    <div class="mypage-list__tab">
        <a href="/mypage?page=sell" class="mypage-list__tab-link {{ request()->query('page','sell') === 'sell' ? 'mypage-list__tab-link--active' : ''}}">出品した商品</a>
        <a href="/mypage?page=buy" class="mypage-list__tab-link {{ request()->query('page') === 'buy' ? 'mypage-list__tab-link--active' : '' }}">購入した商品</a>
    </div>
    <div class="mypage-list__content">
        @foreach($items as $item)
        <div class="mypage-list__item">
            <a href="/item/{{$item->id}}" class="mypage-list__item-link">
                <img src="{{asset($item->img)}}" alt="商品画像" class="mypage-list__item-img">
                @if(request()->query('page', 'sell') === 'sell' && $item->purchase)
                    <div class="mypage-list__sold-box">
                        <span class="mypage-list__sold-box--alert">SOLD</span>
                    </div>
                @endif
            </a>
            <div class="mypage-list__item-info">
            <p class=mypage-list__item-name>{{$item->name}}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
