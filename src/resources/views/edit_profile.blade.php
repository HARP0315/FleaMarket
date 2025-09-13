@extends('layouts/app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/edit_profile.css') }}">
@endsection

@section('content')
<div class="profile-form">
    <h2 class="profile-form__ttl">プロフィール設定</h2>
    <form action="/mypage/profile" method="post" enctype="multipart/form-data" class="profile-form__inner">
        @csrf
        @method('PATCH')
        <div class="profile-form__group">
            <div class="profile-form__user-img-area">
                <div id="image-preview-container"
                    class="profile-form__user-img-display"
                     @if($user->img)
                        style="background-image: url({{ asset('storage/' . $user->img) }});"
                     @endif
                >
                </div>
                <label for="img-input" class="profile-form__img-select-btn btn">画像を選択する</label>
                <input type="file" accept="image/jpeg, image/png" name="img" id="img-input" class="profile-form__file-input">
    @error('img')
        <p class="profile-form__error-message">{{ $message }}</p>
    @enderror
</div>
            <p class="profile-form__error-message">
                @error('img')
                    {{ $message }}
                @enderror
            </p>
        </div>
        <div class="profile-form__group">
            <label for="name" class="profile-form__label form__label">ユーザー名</label>
            <input type="text" name="name" id="name" class="profile-form__input" value="{{ old('name', $user->name) }}">
            <p class="profile-form__error-message">
                @error('name')
                    {{ $message }}
                @enderror
            </p>
        </div>
        <div class="profile-form__group">
            <label for="post_code" class="profile-form__label form__label">郵便番号</label>
            <input type="text" name="post_code" id="post_code" class="profile-form__input" value="{{ old('post_code', $user->post_code) }}">
            <p class="profile-form__error-message">
                @error('post_code')
                    {{ $message }}
                @enderror
            </p>
        </div>
        <div class="profile-form__group">
            <label for="address" class="profile-form__label form__label">住所</label>
            <input type="text" name="address" id="address" class="profile-form__input" value="{{ old('address', $user->address) }}">
            <p class="profile-form__error-message">
                @error('address')
                    {{ $message }}
                @enderror
            </p>
        </div>
        <div class="profile-form__group">
            <label for="building" class="profile-form__label form__label">建物名</label>
            <input type="text" name="building" id="building" class="profile-form__input" value="{{ old('building', $user->building) }}">
            <p class="profile-form__error-message">
                @error('building')
                    {{ $message }}
                @enderror
            </p>
        </div>
        <input type="submit" value="更新する" class="profile-form__submit">
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('img-input');
        // ▼ ターゲットを新しいdivのIDに変更
        const imagePreviewContainer = document.getElementById('image-preview-container');

        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // ▼ srcを書き換えるのではなく、backgroundImageを書き換える
                    imagePreviewContainer.style.backgroundImage = `url(${e.target.result})`;
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>

@endsection
