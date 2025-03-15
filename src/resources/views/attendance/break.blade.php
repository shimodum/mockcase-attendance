{{-- 勤怠登録画面（休憩中） --}}
@extends('layouts.app')

@section('content')
<div class="attendance-container">
    <div class="status-label">
        <span class="status">休憩中</span>
    </div>
    <div class="date-time">
        <p class="date">{{ \Carbon\Carbon::now()->format('Y年n月j日（D）') }}</p>
        <p class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</p>
    </div>

    <form action="/attendance/working" method="GET">
        <button type="submit" class="btn-secondary">休憩戻</button>
    </form>
</div>
@endsection