{{-- 勤怠一覧画面（管理者） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}">
@endsection

@section('nav')
    @include('components.nav.admin_nav')
@endsection

@section('content')
    <div class="attendance-list-container">
        <h2 class="page-title"><span class="pipe">｜</span>{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠</h2>

        <form method="GET" action="{{ url('/admin/attendance/list') }}" class="date-selector-form">
            <button type="submit" name="date" value="{{ \Carbon\Carbon::parse($date)->subDay()->toDateString() }}" class="btn-date">&lt; 前日</button>
            <input type="text" name="date" value="{{ $date }}" class="date-input" readonly>
            <button type="submit" name="date" value="{{ \Carbon\Carbon::parse($date)->addDay()->toDateString() }}" class="btn-date">翌日 &gt;</button>
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
                    <tr>
                        <td>{{ $attendance->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</td>
                        <td>
                            {{ optional($attendance->breakTimes)->sum(function ($break) {
                                return \Carbon\Carbon::parse($break->break_end)->diffInMinutes($break->break_start);
                            }) / 60 }}:00
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes($attendance->clock_out) / 60
                                - optional($attendance->breakTimes)->sum(function ($break) {
                                    return \Carbon\Carbon::parse($break->break_end)->diffInMinutes($break->break_start);
                                }) / 60 }}:00
                        </td>
                        <td><a href="{{ url('/admin/attendance/' . $attendance->id) }}" class="detail-link">詳細</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
