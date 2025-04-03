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

    {{-- 月選択フォーム --}}
    <form method="GET" action="{{ url('/admin/attendance/staff/' . $user->id) }}" class="date-selector-form">
        <button type="submit" name="month" value="{{ $prevMonth }}" class="btn-date">← 前月</button>
        <input type="month" name="month" value="{{ $currentMonth }}" class="date-input" onchange="this.form.submit()">
        <button type="submit" name="month" value="{{ $nextMonth }}" class="btn-date">翌月 →</button>
    </form>

    {{-- 勤怠一覧テーブル --}}
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('m/d(ddd)') }}</td>
                    <td>{{ $attendance->clock_in_time ?? '－' }}</td>
                    <td>{{ $attendance->clock_out_time ?? '－' }}</td>
                    <td>{{ $attendance->break_duration ?? '0:00' }}</td>
                    <td>{{ $attendance->working_duration ?? '0:00' }}</td>
                    <td>
                        <a href="{{ url('/admin/attendance/' . $attendance->id) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">データがありません</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- CSV出力ボタン --}}
    <div style="text-align: right; margin-top: 20px;">
        <form method="GET" action="{{ url('/admin/attendance/staff/' . $user->id . '/export') }}">
            <input type="hidden" name="month" value="{{ $currentMonth }}">
            <button type="submit" class="btn-primary">CSV出力</button>
        </form>
    </div>
</div>
@endsection
