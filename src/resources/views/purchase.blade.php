@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')

<div class="purchase-page"> {{-- ← コンテンツ全体 --}}

    <form action="/purchase/{{ $item->id }}" method="post">
        @csrf

        <div class="purchase-page__main"> {{-- ← ページ左側 --}}
            <div class="purchase-page__group">
                <img src="{{$item->image_url}}" alt="商品画像" class="purchase-page__item-img">
                <h2 class="purchase-page__item-name">{{ $item->name }}</h2>
                <p class="purchase-page__item-price">¥<span>{{ number_format($item->price) }}</span></p>
            </div>

            <div class="purchase-page__group">
                <label for="payment" class="purchase-page__label">支払い方法</label>
                <select name="payment_method" id="payment" class="purchase-page__payment-select">
                    <option disabled selected>選択してください</option>
                    @foreach(config('const.payments.payments') as $key => $value)
                    <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                </select>
                @error('payment_method')
                    <p class="purchase-page__error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="purchase-page__group">
                <h3 class="purchase-page__address-ttl">配送先</h3>
                <a href="/purchase/address/{{$item->id}}" class="shipping-address__change-link">変更する</a>
                <p class="purchase-page__post-code">〒{{ $address->post_code }}</p>
                <p class="purchase-page__address">{{ $address->address }}</p>
                <input type="hidden" name="post_code" value="{{ $address->post_code }}">
                <input type="hidden" name="address" value="{{ $address->address }}">
                @error('address')
                    <p class="purchase-page__error-message">{{ $message }}</p>
                @enderror
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
                </dl>
                <input type="submit" class="summary-box__submit-btn" value="購入する">
            </div>
        </div>
    </form>
</div>

@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // 1. 支払い方
    // 法を選択する<select>要素を取得します
    const paymentSelect = document.getElementById('payment');

    // 2. 選択された支払い方法を表示する<dd>要素を取得します
    const paymentDisplay = document.getElementById('payment-display');

    // 3. <select>要素の値が変更されたとき（changeイベント）に、中の処理を実行するように設定します
    paymentSelect.addEventListener('change', function() {
        // 4. 選択されている<option>の「表示されているテキスト」（例：「コンビニ払い」）を取得します
        const selectedText = this.options[this.selectedIndex].text;

        // 5. 表示用の<dd>要素の中身を、取得したテキストで書き換えます
        paymentDisplay.textContent = selectedText;
    });
});
</script>
