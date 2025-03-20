{{-- 勤怠登録画面（出勤後） --}}
@extends('layouts.app')

@section('nav')
    @include('components.nav.user_nav')
@endsection

@section('content')
@php
    $date = \Carbon\Carbon::now();
    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    $dayOfWeek = $weekdays[$date->dayOfWeek];
@endphp

<div class="attendance-container">
    <div class="status-label">
        <span class="status">出勤中</span>
    </div>
    <div class="date-time">
        <p class="date" id="current-date">{{ $date->format('Y年n月j日') }}（{{ $dayOfWeek }}）</p>
        <p class="time" id="current-time">{{ $date->format('H:i') }}</p>
    </div>

    <div class="button-group">
        <form action="{{ route('attendance.clockout') }}" method="POST" style="display: inline-block;">
            @csrf
            <button type="submit" class="btn-primary">退勤</button>
        </form>

        <form action="{{ route('attendance.break_start') }}" method="POST" style="display: inline-block; margin-left: 10px;">
            @csrf
            <button type="submit" class="btn-secondary">休憩入</button>
        </form>
    </div>
</div>
@endsection
