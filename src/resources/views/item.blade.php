@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/item.css') }}">
    {{-- Font AwesomeのCDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
@endsection

@section('content')
<div class="item-page">
    <div class="item-page__main-content">
        <div class="item-page__img-area">
            <img src="{{ asset($item->img) }}" alt="{{ $item->name }}" class="item-page__item-img">
        </div>
        <div class="item-page__content">
            <div class="item-page__item-info">
                <h2 class="item-page__item-name">{{ $item->name }}</h2>
                <p class="item-page__item-brand">{{ $item->brand }}</p>
                <p class="item-page__item-price">¥{{ number_format($item->price) }}（税込）</p>

                <div class="item-page__actions">
                    {{-- いいね機能 --}}
                    <div class="item-page__like">
                        @if($item->isLikedBy(Auth::user()))
                            {{-- いいね解除 --}}
                            <form action="{{ route('unlike', $item) }}" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="item-page__like-btn">
                                    <i class="fa-solid fa-star"></i> {{-- 塗りつぶし星 --}}
                                </button>
                            </form>
                        @else
                            {{-- いいね --}}
                            <form action="{{ route('like', $item) }}" method="post">
                                @csrf
                                <button type="submit" class="item-page__like-btn">
                                    <i class="fa-regular fa-star"></i> {{-- 枠線のみの星 --}}
                                </button>
                            </form>
                        @endif
                        <span class="item-page__like-count">{{ $item->likes->count() }}</span>
                    </div>

                    {{-- コメント数 --}}
                    <div class="item-page__comment">
                        <a href="#comment-area" class="item-page__comment-link">
                            <i class="fa-regular fa-comment"></i>
                        </a>
                        <span class="item-page__comment-count">{{ $item->comments->count() }}</span>
                    </div>
                </div>

                {{-- 購入ボタンの分岐 --}}
                @if(!$item->purchase)
                    <a href="/purchase/{{ $item->id }}" class="item-page__purchase-btn">購入手続きへ</a>
                @else
                    <div class="item-page__purchase-btn btn--disabled">売り切れました</div>
                @endif
            </div>

            <div class="item-page__description">
                <h3 class="item-page__sub-ttl">商品説明</h3>
                <p class="item-page__description-content">{{ $item->description }}</p>
            </div>

            <div class="item-page__details">
                <h3 class="item-page__sub-ttl">商品の情報</h3>
                <dl class="item-page__details-list">
                    <div class="item-page__details-item">
                        <dt class="item-page__details-item-term">カテゴリー</dt>
                        <dd class="item-page__details-item-data">
                            @foreach($item->categories as $category)
                                <span class="item-page__category-tag">{{ $category->content }}</span>
                            @endforeach
                        </dd>
                    </div>
                    <div class="item-page__details-item">
                        <dt class="item-page__details-item-term">商品の状態</dt>
                        <dd class="item-page__details-item-data">{{ $item->condition->content }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- コメントエリア --}}
    <div class="item-page__comment-area" id="comment-area">
        <h3 class="item-page__sub-ttl">コメント（{{ $item->comments->count() }}件）</h3>
        <div class="item-page__comment-list">
            @foreach($comments as $comment)
                <div class="comment-item">
                    @if($comment->user->img)
                        <img src="{{ asset($comment->user->img) }}" alt="画像" class="comment-item__user-img">
                    @else
                        <div class="comment-item__user-img--alternative"></div>
                    @endif
                    <span class="comment-item__user-name">{{ $comment->user->name }}</span>
                    <p class="comment-item__content">{{ $comment->content }}</p>
                </div>
            @endforeach
        </div>

        {{-- コメント投稿フォームの分岐 --}}
        @auth {{-- ログインしているか --}}
            @if(!$item->purchase) {{-- かつ、売り切れていないか --}}
                <div class="item-page__comment-form">
                    <form action="/item/{{ $item->id }}/comments" method="post">
                        @csrf
                        <textarea name="content" class="item-page__comment-textarea" placeholder="コメントを入力"></textarea>
                        <input type="submit" class="item-page__comment-submit" value="コメントを送信する">
                    @error('content')
                        <p class="item-page__error-message">{{ $message }}</p>
                    @enderror
                    </form>
                </div>
            @endif
        @endauth
    </div>
</div>
@endsection
