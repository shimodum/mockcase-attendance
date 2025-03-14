{{-- ログイン画面（一般ユーザー）--}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
    <h1>ログイン</h1>

    <form method="POST" action="/login">
        @csrf

        <div>
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}">
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label>パスワード</label>
            <input type="password" name="password">
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <button type="submit">ログインする</button>
        </div>

        <p><a href="/register">会員登録はこちら</a></p>
    </form>
@endsection
