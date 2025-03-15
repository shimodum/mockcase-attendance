{{-- 勤怠登録画面（出勤前） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
@php
    $date = \Carbon\Carbon::now();
    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
    $dayOfWeek = $weekdays[$date->dayOfWeek];
@endphp

<div class="attendance-container">
    <div class="status-label">
        <span class="status">勤務外</span>
    </div>
    <div class="date-time">
        <p class="date">{{ $date->format('Y年n月j日') }}（{{ $dayOfWeek }}）</p>
        <p class="time">{{ $date->format('H:i') }}</p>
    </div>

    <form action="/attendance" method="POST">
        @csrf
        <button type="submit" class="btn-primary">出勤</button>
    </form>
</div>
@endsection
