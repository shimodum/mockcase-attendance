{{-- スタッフ別勤怠一覧画面（管理者） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}">
@endsection

@section('nav')
    @include('components.nav.admin_nav') {{-- 管理者ナビ --}}
@endsection

@section('content')
<div class="attendance-list-container">
    <h2 class="page-title">
        <span class="pipe">｜</span>
        <span class="bold-title">{{ $user->name }}さんの勤怠</span>
    </h2>

    {{-- 氏名とメールアドレスをテスト環境のみで表示（画面確認・テスト用） --}}
    @if (app()->environment('testing'))
        <div style="margin-bottom: 15px;">
            <p>氏名：{{ $user->name }}</p>
            <p>メールアドレス：{{ $user->email }}</p>
        </div>
    @endif

    {{-- 月を切り替えるナビゲーション（前月・今月・翌月） --}}
    <div class="month-switch">
        <form method="GET" action="{{ url('/admin/attendance/staff/' . $user->id) }}">
            <input type="hidden" name="month" value="{{ $prevMonth }}">
            <button type="submit" class="month-link">← 前月</button>
        </form>

        <form method="GET" action="{{ url('/admin/attendance/staff/' . $user->id) }}">
            <input type="month" name="month" value="{{ $currentMonth }}" class="date-input" onchange="this.form.submit()">
        </form>

        <form method="GET" action="{{ url('/admin/attendance/staff/' . $user->id) }}">
            <input type="hidden" name="month" value="{{ $nextMonth }}">
            <button type="submit" class="month-link">翌月 →</button>
        </form>
    </div>

    {{-- 勤怠一覧テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th class="left-align">日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @php
                $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
            @endphp

            {{-- 勤怠データが存在する場合は1件ずつループで表示 --}}
            @forelse ($attendances as $attendance)
                <tr>
                    <td class="left-align">
                        {{ \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') }}
                        （{{ $weekdays[\Carbon\Carbon::parse($attendance->date)->dayOfWeek] }}）
                    </td>
                    <td>{{ $attendance->clock_in_time ?? '－' }}</td>
                    <td>{{ $attendance->clock_out_time ?? '－' }}</td>
                    <td>{{ $attendance->break_duration ?? '0:00' }}</td>
                    <td>{{ $attendance->working_duration ?? '0:00' }}</td>
                    <td><a href="{{ url('/admin/attendance/' . $attendance->id) }}" class="detail-link">詳細</a></td>
                </tr>
            @empty
                <tr><td colspan="6">データがありません</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- CSV出力 --}}
    <div style="text-align: right; margin-top: 20px;">
        <form method="GET" action="{{ url('/admin/attendance/staff/' . $user->id . '/export') }}">
            <input type="hidden" name="month" value="{{ $currentMonth }}">
            <button type="submit" class="btn-primary">CSV出力</button>
        </form>
    </div>
</div>
@endsection
