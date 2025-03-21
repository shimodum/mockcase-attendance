{{-- 勤怠詳細画面（一般ユーザー） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('nav')
    @include('components.nav.user_nav')
@endsection

@section('content')
<div class="attendance-detail-container">
    <h2 class="title">勤怠詳細</h2>

    <table class="attendance-detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $attendance->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ optional($attendance->clock_in)->format('H:i') ?? '-' }} ～ {{ optional($attendance->clock_out)->format('H:i') ?? '-' }}
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                @php
                    $firstBreak = $attendance->breakTimes->first();
                    $breakStart = optional($firstBreak->break_start)->format('H:i') ?? '-';
                    $breakEnd = optional($firstBreak->break_end)->format('H:i') ?? '-';
                @endphp
                {{ $breakStart }} ～ {{ $breakEnd }}
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>電車遅延のため</td> {{-- ※現時点ではダミー固定でもOK --}}
        </tr>
    </table>

    <div class="action-btn-area">
        <a href="#" class="btn-primary">修正</a> {{-- ※まだリンク先は未定でOK --}}
    </div>
</div>
@endsection
