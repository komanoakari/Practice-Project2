@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="contents">
    <h2 class="heading">管理者ログイン</h2>

    <div class="form">
        <form action="{{ route('admin.login') }}" class="form-inner" method="post" novalidate>
        @csrf
            <div class="form-group">
                <label for="email" class="label">メールアドレス</label>
                <input type="email" id="email" class="input" name="email" value="{{ old('email') }}">
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="label">パスワード</label>
                <input type="password" id="password" name="password" class="input">
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>
            <input type="submit" class="form-btn" value="管理者ログインする">
        </form>
    </div>
</div>
@endsection