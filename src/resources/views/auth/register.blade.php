{{-- 会員登録画面（一般ユーザー） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
    <h1>会員登録</h1>

    <form method="POST" action="/register">
        @csrf

        <div>
            <label>名前</label>
            <input type="text" name="name" value="{{ old('name') }}">
            @error('name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

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
            <label>パスワード確認</label>
            <input type="password" name="password_confirmation">
            {{-- 確認用はLaravelバリデーションで password_confirmation エラーは password に付随するため通常このままでOKです --}}
        </div>

        <div>
            <button type="submit">登録する</button>
        </div>

        <p><a href="/login">ログインはこちら</a></p>
    </form>
@endsection
