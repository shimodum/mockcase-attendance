{{-- 勤怠一覧画面（管理者） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}">
@endsection

@section('nav')
    @include('components.nav.admin_nav')
@endsection

@section('content')
    @php
        // 分 → H:i 表記に変換する共通関数
        function formatHM($minutes) {
            return floor($minutes / 60) . ':' . sprintf('%02d', $minutes % 60);
        }
    @endphp

    <div class="attendance-list-container">
        <h2 class="page-title">
            <span class="pipe">｜</span>{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠
        </h2>

        <form method="GET" action="{{ url('/admin/attendance/list') }}" class="date-selector-form">
            <a href="{{ url('/admin/attendance/list?date=' . \Carbon\Carbon::parse($date)->subDay()->toDateString()) }}" class="btn-date">← 前日</a>
            <input type="text" class="date-input" value="{{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}" readonly>
            <a href="{{ url('/admin/attendance/list?date=' . \Carbon\Carbon::parse($date)->addDay()->toDateString()) }}" class="btn-date">翌日 →</a>
        </form>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
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
                        $clockIn = \Carbon\Carbon::parse($attendance->clock_in);
                        $clockOut = \Carbon\Carbon::parse($attendance->clock_out);
                        $workMinutes = $clockIn->diffInMinutes($clockOut);

                        $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                            return \Carbon\Carbon::parse($break->break_end)->diffInMinutes($break->break_start);
                        });

                        $totalMinutes = $workMinutes - $breakMinutes;
                    @endphp
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ $clockIn->format('H:i') }}</td>
                        <td>{{ $clockOut->format('H:i') }}</td>
                        <td>{{ formatHM($breakMinutes) }}</td>
                        <td>{{ formatHM($totalMinutes) }}</td>
                        <td><a href="{{ url('/admin/attendance/' . $attendance->id) }}" class="detail-link">詳細</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
