{{-- メール認証誘導画面 --}}

@extends('layouts.app')

@section('content')
    <div class="email-verify-wrapper">
        <p class="email-verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール内のリンクをクリックして認証を完了してください。
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="email-verify-resend-button">
                認証メールを再送する
            </button>
        </form>

        @if (session('message'))
            <p style="color: green; margin-top: 10px;">{{ session('message') }}</p>
        @endif
    </div>
@endsection
