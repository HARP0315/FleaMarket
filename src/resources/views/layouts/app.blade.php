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
                <input type="search" name="keyword" placeholder="なにをお探しですか？" value="{{request('keyword')}}" id="search-input">
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
<script>
    // 1. idを使って検索入力欄の要素を取得する
    const searchInput = document.getElementById('search-input');

    // 2. 検索入力欄で'search'イベントが発生したら、中の処理を実行する
    searchInput.addEventListener('search', function() {
        // 3. もし入力欄の中身が空っぽになったら...
        if (searchInput.value === '') {
            // 4. トップページ('/')に移動する
            window.location.href = '/';
        }
    });
</script>
</body>
</html>
