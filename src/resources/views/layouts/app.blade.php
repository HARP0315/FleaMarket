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
        <a href="/">
            <img src="" alt="coachtech">
        </a>
        @if (!request()->is('login','register'))
        <div class="header-nav">
            <form class="search-form" action="/search" method="get">
                <input type="search" name="keyword" placeholder="なにをお探しですか？">
            </form>
            <nav>
                <ul>
                    <li>
                        <form action="/logout" method="post">
                            @csrf
                            <input class="logout__link" type="submit" value="ログアウト">
                        </form>
                    </li>
                    <li><a href="/mypage">マイページ</a></li>
                    <li><a href="/sell" class="sell__btn">出品</a></li>
                </ul>
            </nav>
        </div>
        @endif
    </header>
    @yield('content')
</div>
</body>
</html>
