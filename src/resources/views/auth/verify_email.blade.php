<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>FleaMarket</title>
    <link rel="stylesheet" href="{{asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{asset('css/common.css')}}">
    <link rel="stylesheet" href="{{asset('css/auth/verify_email.css')}}">
    @yield('css')
</head>
<body>
<div class="app">
    <header class="header">
        <a href="/" class="header-logo">
            <img src="{{ asset('images/logo.svg') }}" alt="coachtech" class="header-logo__img">
        </a>
    </header>
    <div class="verify-email">
        <div class="verify-email__inner">
            <p class="verify-email__inner-message">
                ご登録いただいたメールアドレスに認証メールを送信しました。<br>
                メール認証を完了してください。
            </p>

            {{-- メールが再送された直後だけ表示されるメッセージ --}}
            @if (session('status') == 'verification-link-sent')
                <p class="verify-email__success-message">
                    新しい認証メールを送信しました。
                </p>
            @endif

            <div class="verify-email__inner-actions">
                <div class="verify-email__inner-link-wrapper">
                    <a href="http://localhost:8025" target="_blank" class="verify-email__inner-verify-button">
                        認証はこちらから
                    </a>
                </div>
                {{-- ▲▲▲ ここまで ▲▲▲ --}}

                {{-- 認証メール再送フォーム --}}
                <form class="verify-email__inner-resend-form" method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <input type="submit" class="verify-email__inner-resend-link" value="認証メールを再送する">
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
