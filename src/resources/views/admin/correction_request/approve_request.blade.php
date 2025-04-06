{{-- 修正申請承認画面（管理者） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-correction-request.css') }}">
@endsection

@section('nav')
    <div class="nav-menu">
        <ul>
            <li><a href="/admin/attendance/list">勤怠一覧</a></li>
            <li><a href="/admin/staff/list">スタッフ一覧</a></li>
            <li><a href="{{ route('stamp_correction_request.list') }}">申請一覧</a></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-link-button">ログアウト</button>
                </form>
            </li>
        </ul>
    </div>
@endsection

@section('content')
<main class="correction-approve-container">
    <h2 class="page-title"><span class="pipe">｜</span>勤怠詳細</h2>

    <table class="correction-table">
        <tr>
            <th>名前</th>
            <td>{{ $correction->attendance->user->name ?? '不明' }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($correction->attendance->date)->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ $correction->requested_clock_in ?? '-' }} 〜 {{ $correction->requested_clock_out ?? '-' }}
            </td>
        </tr>
        <tr>
            <th>休憩1</th>
            <td>未入力（※今後機能追加）</td>
        </tr>
        <tr>
            <th>備考</th>
            <td>{{ $correction->request_reason ?? '-' }}</td>
        </tr>
    </table>

    <form method="POST" action="{{ route('stamp_correction_request.approve', $correction->id) }}" class="approve-form">
        @csrf
        <button type="submit" class="btn-approve">承認</button>
    </form>
</main>
@endsection
