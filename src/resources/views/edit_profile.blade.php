@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/edit_profile.css') }}">
@endsection

@section('content')
<div class="profile-settings">
    <h2 class="profile-settings__ttl">プロフィール設定</h2>
    <form action="/mypage/profile" method="post" enctype="multipart/form-data" class="profile-settings__form">
        @csrf
        @method('PATCH')
        <div class="profile-settings__group">
            <div class="profile-settings__user-img-area">
                @if($user->img)
                    <img src="{{asset($user->img)}}" alt="画像">
                @else
                    <div class="profile-settings__user-img--alternative"></div>
                @endif
                <label for="img" class="profile-settings__img-select-btn btn">画像を選択する</label>
                <input type="file" accept="image/jpeg, image/png"  name="img" id="img" class="profile-settings__file-input">
            </div>
        </div>
        <div class="profile-settings__group">
            <label for="name" class="profile-settings__label form__label">ユーザー名</label>
            <input type="text" name="name" id="name" class="profile-settings__input" value="{{ old('name', $user->name) }}">
        </div>
        <div class="profile-settings__group">
            <label for="post_code" class="profile-settings__label form__label">郵便番号</label>
            <input type="text" name="post_code" id="post_code" class="profile-settings__input" value="{{ old('post_code', $user->post_code) }}">
        </div>
        <div class="profile-settings__group">
            <label for="address" class="profile-settings__label form__label">住所</label>
            <input type="text" name="address" id="address" class="profile-settings__input">
        </div>
        <div class="profile-settings__group">
            <label for="building" class="profile-settings__label form__label">建物名</label>
            <input type="text" name="building" id="building" class="profile-settings__input">
        </div>
        <input type="submit" value="更新する" class="profile-settings__submit">
    </form>
</div>

@endsection
