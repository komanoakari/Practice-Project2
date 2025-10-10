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
            <a href="/admin/attendance/list" class="header-nav-item">勤怠一覧</a>
            <a href="/admin/staff/list" class="header-nav-item">スタッフ一覧</a>
            <a href="{{ route('corrections.index') }}" class="header-nav-item">申請一覧</a>
            <form action="{{ route('admin.logout') }}" method="POST" class="header-nav-item">
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