{{-- ログイン画面（管理者）--}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
@endsection

@section('content')
    <h1>管理者ログイン</h1>

    <form method="POST" action="/admin/login">
        @csrf

        <div>
            <label>メールアドレス</label>
            <input type="text" name="email" value="{{ old('email') }}">
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
            <button type="submit">管理者ログインする</button>
        </div>

    </form>
@endsection
