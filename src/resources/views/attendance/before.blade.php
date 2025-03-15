{{-- 勤怠登録画面（出勤前） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="status-label">
        <span class="status">勤務外</span>
    </div>
    <div class="date-time">
        <p class="date">{{ \Carbon\Carbon::now()->format('Y年n月j日（D）') }}</p>
        <p class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</p>
    </div>

    <form action="/attendance" method="POST">
        @csrf
        <button type="submit" class="btn-primary">出勤</button>
    </form>
</div>
@endsection