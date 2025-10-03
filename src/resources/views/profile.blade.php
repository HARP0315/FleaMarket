@extends('layouts/app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

@section('content')
<div class="mypage-list">
    {{-- プロフィール更新成功セッションメッセージ --}}
    @if (session('success'))
        <div class="mypage-list__success-message--flash">
            {{ session('success') }}
        </div>
    @endif
    {{-- ユーザ画像 --}}
    <div class="mypage-list__heading">
        <div class="mypage-list__user-img-display"
         @if($user->img)
             style="background-image: url({{ asset('storage/' . $user->img) }});"
         @endif
        >
    </div>
        {{-- ユーザ名 --}}
        <div class="mypage-list__heading-user-name">
            <p class="mypage-list__user-name">{{$user->name}}</p>
        </div>
        {{-- プロフィール編集リンク --}}
        <div class="mypage-list__profile-link">
            <a href="/mypage/profile" class="mypage-list__profile-button">プロフィールを編集</a>
        </div>
    </div>
    {{-- タブの切替え --}}
    <div class="mypage-list__tab">
        <a
         href="/mypage?page=sell"
         class="mypage-list__tab-link
          {{ request()->query('page','sell') === 'sell' ? 'mypage-list__tab-link--active' : ''}}"
        >出品した商品</a>
        <a
         href="/mypage?page=buy"
         class="mypage-list__tab-link
          {{ request()->query('page') === 'buy' ? 'mypage-list__tab-link--active' : '' }}"
        >購入した商品</a>
    </div>
    {{-- 商品一覧 --}}
    <div class="mypage-list__content">
        @foreach($items as $item)
        <div class="mypage-list__item">
            <a href="/item/{{$item->id}}"
             class="mypage-list__item-link
              @if(request()->query('page', 'sell') === 'sell' && $item->purchase)
                  --sold
              @endif"
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
