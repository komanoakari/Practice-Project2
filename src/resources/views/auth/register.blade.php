@extends('layouts.header_user')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register">
    <h2 class="register-heading">会員登録</h2>
    <div class="register-form">
        <form action="{{ route('register') }}" class="register-form-inner" method="post" novalidate>
        @csrf
            <div class="register-form-group">
                <label for="name" class="register-form-label">名前</label>
                <input type="text" name="name" id="name" class="register-form-input" value="{{ old('name') }}">
                @error('name')
                    <p class="register-error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="register-form-group">
                <label for="email" class="register-form-label">メールアドレス</label>
                <input type="text" name="email" id="email" class="register-form-input" value="{{ old('email') }}">
                @error('email')
                    <p class="register-error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="register-form-group">
                <label for="password" class="register-form-label">パスワード</label>
                <input type="password" name="password" id="password" class="register-form-input" value="{{ old('password') }}">
                @error('password')
                    <p class="register-error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="register-form-group">
                <label for="password_confirmation" class="register-form-label">パスワード確認</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="register-form-input" value="{{ old('password_confirmation') }}">
                @error('password_confirmation')
                    <p class="register-error-message">{{ $message }}</p>
                @enderror
            </div>
            <input type="submit" class="register-form-btn" value="登録する">
            <a href="{{ route('login') }}" class="login-link">ログインはこちら</a>
        </form>
    </div>
</div>
@endsection