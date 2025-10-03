@extends('layouts/app')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/exhibition.css') }}">
@endsection

@section('content')
<div class="exhibition-form">
    <h2 class="exhibition-form__ttl">商品の出品</h2>
    <form
     action="/sell"
     method="post"
     enctype="multipart/form-data"
     class="exhibition-form__form"
    >
        @csrf
        {{-- 商品画像 --}}
        <div class="exhibition-form__group">
            <div class="exhibition-form__item-img-area">
                <img
                 id="image-preview"
                 src=""
                 alt="商品画像"
                 class="exhibition-form__img-preview"
                >
                <label for="img" class="exhibition-form__label--select">画像を選択する</label>
                <input
                 type="file"
                 accept="image/jpeg,image/png"
                 name="img"
                 id="img"
                 class="exhibition-form__file-input"
                >
            </div>
            @error('img')
                <p class="exhibition-form__error-message">{{ $message }}</p>
            @enderror
        </div>
        <div class="exhibition-form__group">
            <h3 class="exhibition-form__sub-ttl--heading">商品の詳細</h3>
            {{-- 商品カテゴリ --}}
            <div class="exhibition-form__detail-area">
                <label class="exhibition-form__label">カテゴリー</label>
                <div class="exhibition-form__category-list">
                    @foreach($categories as $category)
                        <div class="exhibition-form__category-item">
                            <input
                             type="checkbox"
                             name="categories[]"
                             id="category-{{ $category->id }}"
                             class="exhibition-form__category-input"
                             value="{{ $category->id }}"
                             @if(is_array(old('categories')) && in_array($category->id, old('categories')))
                                 checked
                             @endif
                            >
                            <label for="category-{{ $category->id }}" class="exhibition-form__category-label">
                                {{ $category->content }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('categories')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            {{-- 商品の状態 --}}
            <div class="exhibition-form__detail-area details-list">
                <label for="condition" class="exhibition-form__label">商品の状態</label>
                <select name="condition" id="condition" class="exhibition-form__select">
                    <option disabled selected>選択してください</option>
                    @foreach(config('const.conditions.conditions') as $key => $value)
                        <option value="{{ $key }}"
                         @if(old('condition') == $key)
                             selected
                         @endif
                        >{{$value}}</option>
                    @endforeach
                </select>
                @error('condition')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="exhibition-form__group">
            <h3 class="exhibition-form__sub-ttl--heading">商品名と説明</h3>
            {{-- 商品名 --}}
            <div class="exhibition-form__detail-area">
                <label for="name" class="exhibition-form__label">商品名</label>
                <input
                 type="text"
                 name="name"
                 id="name"
                 class="exhibition-form__input"
                 value="{{ old('name') }}"
                >
                @error('name')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            {{-- ブランド名 --}}
            <div class="exhibition-form__detail-area">
                <label for="brand" class="exhibition-form__label">ブランド名</label>
                <input
                 type="text"
                 name="brand"
                 id="brand"
                 class="exhibition-form__input"
                 value="{{ old('brand') }}"
                >
                @error('brand')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            {{-- 商品の説明 --}}
            <div class="exhibition-form__detail-area">
                <label for="description" class="exhibition-form__label">商品の説明</label>
                <textarea
                 name="description"
                 id="description"
                 class="exhibition-form__textarea"
                 value="{{old('description')}}"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            {{-- 販売価格 --}}
            <h3 class="exhibition-form__sub-ttl">販売価格</h3>
            <div class="exhibition-form__detail-area">
                <div class="exhibition-form__price-box">
                    <span>¥</span>
                    <input
                     type="text"
                     name="price"
                     id="price"
                     class="exhibition-form__price-input"
                     value="{{ old('price') }}"
                    >
                </div>
                @error('price')
                    <p class="exhibition-form__error-message">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <input type="submit" class="exhibition-form__submit" value="出品する">
    </form>
</div>
@endsection

{{-- 画像プレビュー機能 --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const imageInput = document.getElementById('img');
        const imagePreview = document.getElementById('image-preview');
        const selectLabel = document.querySelector('.exhibition-form__label--select');

        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    selectLabel.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
