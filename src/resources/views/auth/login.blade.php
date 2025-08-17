@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')

<div class="login-form">
    <div class="login-form__heading content__heading">
        <h2>ログイン</h2>
    </div>
    <div class="login-form__inner">
        <form action="/login" method="post" class="login-form__form">
            @csrf
            <div class="login-form__group">
                <label for="email" class="login-form__label form__label">メールアドレス</label>
                <input type="email" name="email" id="email" class="login-form__input">
            </div>
            <div class="login-form__group">
                <label for="password" class="login-form__label form__label">パスワード</label>
                <input type="password" name="password" id="password" class="login-form__input">
            </div>
            <input type="submit" value="ログインする" class="login-form__submit">
        </form>
        <a href="/register" class="login-form__register-link">会員登録はこちら</a>
    </div>
</div>

@endsection
