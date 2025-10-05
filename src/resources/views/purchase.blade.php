@extends('layouts/app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/purchase.css') }}">
@endsection

@section('content')
<div class="purchase-page">
    <form action="/purchase/{{ $item->id }}" method="post" target="_blank">
        @csrf
        {{-- 画面左：購入情報 --}}
        <div class="purchase-page__main">
            {{-- 商品の画像 --}}
            <div class="purchase-page__group">
                <img src="{{$item->image_url}}" alt="商品画像" class="purchase-page__item-img">
                <div class="purchase-page__item-info">
                    <h2 class="purchase-page__item-name">{{ $item->name }}</h2>
                    <p class="purchase-page__item-price">
                        <span>¥</span>{{ number_format($item->price) }}
                    </p>
                </div>
                <input type="hidden" name="price" value="{{ $item->price }}">
            </div>
            {{-- 支払い方法 --}}
            <div class="purchase-page__group">
                <div class="purchase-page__payment">
                    <label for="payment" class="purchase-page__label">支払い方法</label>
                    <div class="purchase-page__payment-content">
                        <select name="payment_method" id="payment" class="purchase-page__payment-select">
                            <option disabled selected>選択してください</option>
                                @foreach(config('const.payments.payments') as $key => $value)
                                    <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                        </select>
                    </div>
                </div>
                @error('payment_method')
                    <p class="purchase-page__error-message">{{ $message }}</p>
                @enderror
            </div>
            {{-- 配送先 --}}
            <div class="purchase-page__group">
                <div class="purchase-page__address-header">
                    <h3 class="purchase-page__address-ttl">配送先</h3>
                    <a href="/purchase/address/{{$item->id}}" class="shipping-address__change-link">変更する</a>
                </div>
                <div class="purchase-page__address-content">
                    <p>〒{{ $address?->post_code }}</p>
                    <p>{{ $address?->address }}</p>
                    <p>{{ $address?->building }}</p>
                </div>
                <input type="hidden" name="post_code" value="{{ $address->post_code }}">
                <input type="hidden" name="address" value="{{ $address->address }}">
                <input type="hidden" name="building" value="{{ $address->building }}">
            </div>
            @if ($errors->has('post_code'))
                <p class="purchase-page__error-message">{{ $errors->first('post_code') }}</p>
            @elseif ($errors->has('address'))
                <p class="purchase-page__error-message">{{ $errors->first('address') }}</p>
            @endif
        </div>
        {{-- 画面右：小計画面 --}}
        <div class="purchase-page__summary">
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

{{-- 小計画面への支払い方法の反映機能 --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

    const paymentSelect = document.getElementById('payment');
    const paymentDisplay = document.getElementById('payment-display');

    paymentSelect.addEventListener('change', function() {
        const selectedText = this.options[this.selectedIndex].text;
        paymentDisplay.textContent = selectedText;
    });
});
</script>
