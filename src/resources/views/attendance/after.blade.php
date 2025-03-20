{{-- 勤怠登録画面（退勤後） --}}
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
        <span class="status">退勤済</span>
    </div>
    <div class="date-time">
        <p class="date" id="current-date">{{ $date->format('Y年n月j日') }}（{{ $dayOfWeek }}）</p>
        <p class="time" id="current-time">{{ $date->format('H:i') }}</p>
    </div>

    <p style="font-size: 18px; font-weight: bold; letter-spacing: 2px; margin-top: 50px;">
    お疲れ様でした。
    </p>
</div>
@endsection