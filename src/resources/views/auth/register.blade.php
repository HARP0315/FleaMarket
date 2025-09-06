@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')

<div class="register-form">
    <div class="register-form__heading content__heading">
        <h2>会員登録</h2>
    </div>
    <div class="register-form__inner">
        <form action="/register" method="post" class="register-form__form" novalidate>
            @csrf
            <div class="register-form__group">
                <label for="name" class="register-form__label form__label">ユーザ名</label>
                <input type="text" name="name" id="name" class="register-form__input" value="{{ old('name') }}">
                @error('name')
                    <p class="register-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            <div class="register-form__group">
                <label for="email" class="register-form__label form__label">メールアドレス</label>
                <input type="email" name="email" id="email" class="register-form__input" value="{{ old('email') }}">
                @error('email')
                    <p class="register-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            <div class="register-form__group">
                <label for="password" class="register-form__label form__label">パスワード</label>
                <input type="password" name="password" id="password" class="register-form__input">
                @error('password')
                    <p class="register-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            <div class="register-form__group">
                <label for="password_confirmation" class="register-form__label form__label">確認用パスワード</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="register-form__input">
                @error('password_confirmation')
                    <p class="register-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            <input type="submit" value="登録する" class="register-form__submit">
        </form>
        <a href="/login" class="register-form__login-link">ログインはこちら</a>
    </div>
</div>

@endsection
