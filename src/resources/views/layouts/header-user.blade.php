<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/logo.png') }}" alt="ロゴ画像">

        <nav class="header-nav">
            <a href="/attendance" class="header-nav-item">勤怠</a>
            <a href="/attendance/list" class="header-nav-item">勤怠一覧</a>
            <a href="{{ route('correction.index') }}" class="header-nav-item">申請</a>
            <form action="{{ route('logout') }}" method="POST" class="header-nav-item">
                @csrf
                <button class="header-btn" type="submit">ログアウト</button>
            </form>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>