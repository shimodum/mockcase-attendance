{{-- 勤怠一覧画面（一般ユーザー） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('nav')
    @include('components.nav.user_nav')
@endsection

@section('content')
<div class="attendance-list-container">
    <h2 class="title">勤怠一覧</h2>

    {{-- 月切り替えボタン --}}
    <div class="month-switch">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="month-btn">← 前月</a>
        <span class="current-month">{{ $displayMonth }}</span>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="month-btn">翌月 →</a>
    </div>

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
            @foreach ($attendances as $attendance)
                @php
                    $totalBreakMinutes = 0;
                    foreach ($attendance->breakTimes as $break) {
                        if ($break->break_start && $break->break_end) {
                            $totalBreakMinutes += \Carbon\Carbon::parse($break->break_start)->diffInMinutes($break->break_end);
                        }
                    }

                    if ($attendance->clock_in && $attendance->clock_out) {
                        $workMinutes = \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes($attendance->clock_out) - $totalBreakMinutes;
                    } else {
                        $workMinutes = null;
                    }

                    $breakHour = floor($totalBreakMinutes / 60);
                    $breakMin = $totalBreakMinutes % 60;
                    $workHour = floor($workMinutes / 60);
                    $workMin = $workMinutes % 60;
                @endphp

                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y/m/d（D）') }}</td>
                    <td>{{ optional($attendance->clock_in)->format('H:i') ?? '-' }}</td>
                    <td>{{ optional($attendance->clock_out)->format('H:i') ?? '-' }}</td>
                    <td>{{ $totalBreakMinutes ? sprintf('%d:%02d', $breakHour, $breakMin) : '-' }}</td>
                    <td>{{ $workMinutes !== null ? sprintf('%d:%02d', $workHour, $workMin) : '-' }}</td>
                    <td><a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
