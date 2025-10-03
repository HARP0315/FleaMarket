<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>FleaMarket</title>
    <link rel="stylesheet" href="{{asset('css/sanitize.css')}}">
    <link rel="stylesheet" href="{{asset('css/common.css')}}">
    @yield('css')
</head>
<body>
<div class="app">
    <header class="header">
        <a href="/" class="header-logo">
            <img src="{{ asset('images/logo.svg') }}" alt="coachtech" class="header-logo__img">
        </a>
        @if (!request()->is('login','register'))
            {{-- 検索機能 --}}
            <form class="search-form" action="/" method="get">
                <input
                 type="search"
                 name="keyword"
                 placeholder="なにをお探しですか？"
                 value="{{request('keyword')}}"
                 id="search-input"
                 class="search-form__input"
                >
            </form>
            {{-- ナビ --}}
            <nav class="header-nav">
                <ul class="header-nav__list">
                    @guest
                        <li><a href="/login" class="login__link">ログイン</a></li>
                    @endguest
                    @auth
                        <li>
                            <form action="/logout" method="post">
                                @csrf
                                <input class="logout__link" type="submit" value="ログアウト">
                            </form>
                        </li>
                    @endauth
                    <li><a href="/mypage" class="mypage__link">マイページ</a></li>
                    <li><a href="/sell" class="sell__btn">出品</a></li>
                </ul>
            </nav>
        @endif
    </header>
    @yield('content')
</div>

{{-- search機能：検索欄が空になった際トップページに遷移 --}}
<script>
    const searchInput = document.getElementById('search-input');
    searchInput.addEventListener('search', function() {
        if (searchInput.value === '') {
            window.location.href = '/';
        }
    });
</script>
</body>
</html>
