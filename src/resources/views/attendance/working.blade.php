{{-- 勤怠登録画面（出勤後） --}}
@extends('layouts.app')

@section('content')
<div class="attendance-container">
    <div class="status-label">
        <span class="status">出勤中</span>
    </div>
    <div class="date-time">
        <p class="date">{{ \Carbon\Carbon::now()->format('Y年n月j日（D）') }}</p>
        <p class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</p>
    </div>

    <form action="/attendance/after" method="GET" style="display: inline;">
        <button type="submit" class="btn-primary">退勤</button>
    </form>

    <form action="/attendance/break" method="GET" style="display: inline;">
        <button type="submit" class="btn-secondary">休憩入</button>
    </form>
</div>
@endsection