{{-- 勤怠登録画面（休憩中） --}}
@extends('layouts.app')

@section('content')
@php
    $date = \Carbon\Carbon::now();
    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    $dayOfWeek = $weekdays[$date->dayOfWeek];
@endphp

<div class="attendance-container">
    <div class="status-label">
        <span class="status">休憩中</span>
    </div>
    <div class="date-time">
        <p class="date">{{ $date->format('Y年n月j日') }}（{{ $dayOfWeek }}）</p>
        <p class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</p>
    </div>

    <form action="/attendance/working" method="GET">
        <button type="submit" class="btn-secondary">休憩戻</button>
    </form>
</div>
@endsection