{{-- 申請詳細画面（一般ユーザー） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/correction_request.css') }}">
@endsection

@section('nav')
    @include('components.nav.user_nav')
@endsection

@section('content')
<div class="attendance-detail-container">
    <h2 class="page-title"><span class="pipe">｜</span>申請詳細</h2>

    <table class="attendance-detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $correction->attendance->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($correction->attendance->date)->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ $correction->requested_clock_in ? \Carbon\Carbon::parse($correction->requested_clock_in)->format('H:i') : '-' }}
                〜
                {{ $correction->requested_clock_out ? \Carbon\Carbon::parse($correction->requested_clock_out)->format('H:i') : '-' }}
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>{{ $correction->request_reason ?? '-' }}</td>
        </tr>
    </table>

    <div class="action-btn-area">
        <a href="{{ route('stamp_correction_request.list') }}" class="btn-secondary">一覧に戻る</a>
    </div>
</div>
@endsection

