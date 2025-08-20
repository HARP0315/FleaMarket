@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')

<div class="purchase-page"> {{-- ← コンテンツ全体 --}}

    {{-- ▼ 全ての入力部品を1つのformで囲う --}}
    <form action="/purchase/{{ $item->id }}" method="post">
        @csrf

        <div class="purchase-page__main"> {{-- ← ページ左側 --}}
            <div class="item-info">
                <img src="..." alt="..." class="item-info__img">
                <h2 class="item-info__name">{{ $item->name }}</h2>
                <p class="item-info__price">¥{{ number_format($item->price) }}</p>
            </div>

            <div class="payment-method">
                <label for="payment" class="payment-method__label">支払い方法</label>
                <select name="payment_method" id="payment-select" class="payment-method__select">
                    {{-- ... options ... --}}
                </select>
                {{-- configで設定したやつ載せるよ --}}
            </div>

            <div class="shipping-address">
                <h3 class="shipping-address__ttl">配送先</h3>
                <a href="..." class="shipping-address__change-link">変更する</a>
                <p class="shipping-address__post-code">〒{{ $address->post_code }}</p>
                <p class="shipping-address__address">{{ $address->address }}</p>
            </div>
        </div>

        <div class="purchase-page__summary"> {{-- ← ページ右側 --}}
            <div class="summary-box">
                <dl class="summary-box__list">
                    <div class="summary-box__item">
                        <dt>商品代金</dt>
                        <dd>¥{{ number_format($item->price) }}</dd>
                    </div>
                    <div class="summary-box__item">
                        <dt>支払い方法</dt>
                        <dd id="payment-display">
                            {{-- JSでここに選択内容が表示される --}}
                        </dd>
                    </div>
                    {{-- 他にも合計金額など --}}
                </dl>
                <button type="submit" class="summary-box__submit-btn btn">購入する</button>
            </div>
        </div>

    </form> {{-- ▲ formはここで閉じる --}}
</div>

@endsection
