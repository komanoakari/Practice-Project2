@extends('layouts.header_user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-contents">
    <h2 class="login-heading">ログイン</h2>

    <div class="login-form">
        <form action="{{ route('login.store') }}" class="login-form-inner" method="post" novalidate>
        @csrf
            <div class="login-form-group">
                <label for="email" class="login-form-label">メールアドレス</label>
                <input type="email" id="email" class="login-form-input" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="login-error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="login-form-group">
                <label for="password" class="login-form-label">パスワード</label>
                <input type="password" id="password" name="password" class="login-form-input">
                @error('password')
                    <p class="login-error-message">{{ $message }}</p>
                @enderror
            </div>
            <input type="submit" class="login-form-btn" value="ログインする">
            <a href="{{ route('register') }}" class="register-link">会員登録はこちら</a>
        </form>
    </div>
</div>
@endsection