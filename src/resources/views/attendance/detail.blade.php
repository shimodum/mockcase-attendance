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
    <h2 class="page-title"><span class="pipe">｜</span>勤怠詳細</h2>

    {{-- 修正申請フォーム --}}
    <form method="POST" action="{{ route('attendance.correction_request', $attendance->id) }}">
        @csrf

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
                    <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}" {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>
                    〜 
                    <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}" {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>

                    @error('clock_in')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('clock_out')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    @php
                        // 最初の休憩とその修正データを取得
                        $firstBreak = $attendance->breakTimes->first();
                        $breakCorrection = optional($firstBreak)->correction;

                        // 修正があれば優先し、それ以外は元データを表示
                        $breakStart = old('break_start', $breakCorrection && $breakCorrection->requested_break_start
                            ? \Carbon\Carbon::parse($breakCorrection->requested_break_start)->format('H:i')
                            : ($firstBreak && $firstBreak->break_start ? \Carbon\Carbon::parse($firstBreak->break_start)->format('H:i') : '')
                        );

                        $breakEnd = old('break_end', $breakCorrection && $breakCorrection->requested_break_end
                            ? \Carbon\Carbon::parse($breakCorrection->requested_break_end)->format('H:i')
                            : ($firstBreak && $firstBreak->break_end ? \Carbon\Carbon::parse($firstBreak->break_end)->format('H:i') : '')
                        );
                    @endphp

                    <input type="time" name="break_start" value="{{ $breakStart }}" {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="break_end" value="{{ $breakEnd }}" {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>

                    @error('break_start')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('break_end')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" rows="2" cols="40" {{ $attendance->status === 'waiting_approval' ? 'readonly=readonly' : '' }}>{{ old('note', $attendance->note) }}</textarea>

                    @error('note')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        {{-- 修正ボタンか承認待ちメッセージを表示 --}}
        <div class="action-btn-area">
            @if ($attendance->status === 'waiting_approval')
                <p class="notice">* 承認待ちのため修正できません。</p>
            @else
                <button type="submit" class="btn-primary">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection
