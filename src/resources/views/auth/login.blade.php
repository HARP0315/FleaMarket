@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')

<div class="login-form">
    <div class="login-form__heading">
        <h2>ログイン</h2>
    </div>
    <div class="login-form__inner">
        <form action="/login" method="post" class="login-form__form">
            @csrf
            <div class="login-form__group">
                <label for="email" class="login-form__label">メールアドレス</label>
                <input type="mail" name="email" id="email" class="login-form__input" value="{{ old('email') }}">
                @error('email')
                    <p class="login-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            <div class="login-form__group">
                <label for="password" class="login-form__label">パスワード</label>
                <input type="password" name="password" id="password" class="login-form__input">
                @error('password')
                    <p class="login-form__error-message">{{ $message }}</p>
                @enderror
            </div>
            <input type="submit" value="ログインする" class="login-form__submit">
        </form>
        <a href="/register" class="login-form__register-link">会員登録はこちら</a>
    </div>
</div>

@endsection
