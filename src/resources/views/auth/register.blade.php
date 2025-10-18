@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="contents">
    <h2 class="heading">会員登録</h2>
    <div class="form">
        <form action="{{ route('register') }}" class="form-inner" method="post" novalidate>
        @csrf
            <div class="form-group">
                <label for="name" class="label">名前</label>
                <input type="text" name="name" id="name" class="input" value="{{ old('name') }}">
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="label">メールアドレス</label>
                <input type="text" name="email" id="email" class="input" value="{{ old('email') }}">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="label">パスワード</label>
                <input type="password" name="password" id="password" class="input" value="{{ old('password') }}">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="label">パスワード確認</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="input" value="{{ old('password_confirmation') }}">
                @error('password_confirmation')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
            <input type="submit" class="form-btn" value="登録する">
            <a href="/login" class="login-link">ログインはこちら</a>
        </form>
    </div>
</div>
@endsection