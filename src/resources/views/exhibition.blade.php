@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/exhibition.css') }}">
@endsection

@section('content')
<div class="exhibition-form">
    <h2 class="exhibition-form__ttl">商品の出品</h2>
    <form action="/sell" method="post" enctype="multipart/form-data" class="exhibition-form__inner">
        @csrf
        <div class="exhibition-form__group">
            <div class="exhibition-form__item-img-area">
                <label for="img" class="exhibition-form__label form__label">画像を選択する</label>
                <input type="file" accept="image/jpeg, image/png"  name="img" id="img" class="exhibition-form__file-input">
            </div>
        </div>
        <div class="exhibition-form__group">
            <h3 class="exhibition-form__sub-ttl">商品の詳細</h3>
            <div class="exhibition-form__detail-area">
                <label class="exhibition-form__label">カテゴリー</label>
                <div class="exhibition-form__category-list">
                    @foreach($categories as $category)
                        <div class="exhibition-form__category-item">
                            <input type="checkbox" name="categories[]" id="category-{{ $category->id }}" value="{{ $category->id }}">
                            <label for="category-{{ $category->id }}" class="exhibition-form__category-label">{{ $category->content }}</label>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="exhibition-form__detail-area">
                <label for="condition" class="exhibition-form__label">商品の状態</label>
                <select name="condition_id" id="condition" class="exhibition-form__select">
                    <option disabled selected>選択してください</option>
                    @foreach($conditions as $condition)
                        <option value="{{ $condition->id }}">{{ $condition->content }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="exhibition-form__group">
            <h3 class="exhibition-form__sub-ttl">商品名と説明</h3>
            <div class="exhibition-form__detail-area">
                <label for="name" class="exhibition-form__label">商品名</label>
                <input type="text" name="name" id="name" class="exhibition-form__input" value="{{ old('name') }}">
            </div>
            <div class="exhibition-form__detail-area">
                <label for="brand" class="exhibition-form__label">ブランド名</label>
                <input type="text" name="brand" id="brand" class="exhibition-form__input" value="{{ old('brand') }}">
            </div>
            <div class="exhibition-form__detail-area">
                <label for="description" class="exhibition-form__label">商品の説明</label>
                <textarea name="description" id="description" class="exhibition-form__textarea">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="exhibition-form__group">
            <h3 class="exhibition-form__sub-ttl">販売価格</h3>
            <div class="exhibition-form__detail-area">
                <label for="price" class="exhibition-form__label">販売価格</label>
                <div class="exhibition-form__price-box">
                    <span>¥</span>
                    <input type="text" name="price" id="price" class="exhibition-form__price-input" value="{{ old('price') }}">
                </div>
            </div>
        </div>
        <input type="submit" class="exhibition-form__submit" value="出品する">
    </form>
</div>

@endsection
