{{-- 勤怠登録画面（退勤後） --}}
@extends('layouts.app')

@section('content')
<div class="attendance-container">
    <div class="status-label">
        <span class="status">退勤済</span>
    </div>
    <div class="date-time">
        <p class="date">{{ \Carbon\Carbon::now()->format('Y年n月j日（D）') }}</p>
        <p class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</p>
    </div>

    <p style="font-size: 18px; font-weight: bold; letter-spacing: 2px; margin-top: 50px;">
    お疲れ様でした。
    </p>
</div>
@endsection