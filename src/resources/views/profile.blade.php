@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="mypage-list">
    {{-- もし、'success'という名前のセッションメッセージがあれば --}}
    @if (session('success'))
        <div class="mypage-list__success-message--flash">
            {{ session('success') }}
        </div>
    @endif
    {{-- ▲▲▲ ここまで ▲▲▲ --}}
    <div class="mypage-list__heading">
        <div class="mypage-list__user-img-display"
         @if($user->img)
             style="background-image: url({{ asset('storage/' . $user->img) }});"
         @endif
        >
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
                        <a href="/item/{{$item->id}}"
               class="mypage-list__item-link @if(request()->query('page', 'sell') === 'sell' && $item->purchase)--sold @endif"
                 style="background-image: url({{ $item->image_url }});"
            >
                <div class="mypage-list__sold-box">
                    <span>SOLD</span>
                </div>
            </a>
            <div class="mypage-list__item-info">
            <p class=mypage-list__item-name>{{$item->name}}</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

@endsection
