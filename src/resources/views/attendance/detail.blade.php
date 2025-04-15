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
                {{-- ユーザー名を表示。データがなければ「-」を表示 --}}
                <td>{{ $attendance->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日') }}</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    {{-- 出勤時間の入力欄（修正申請中は無効化） --}}
                    <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}" {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>
                    〜
                    {{-- 退勤時間の入力欄（修正申請中は無効化） --}}
                    <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}" {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>

                    {{-- 出勤・退勤時刻のバリデーションエラー表示 --}}
                    @error('clock_in')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    @error('clock_out')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>

            {{-- 複数の休憩欄 --}}
            @php
                $breaks = $attendance->breakTimes->all();
                $breakCount = count($breaks);
            @endphp

            @for ($i = 0; $i <= $breakCount; $i++)
                @php
                    $break = $breaks[$i] ?? null;
                    $correction = optional($break)->correction;

                    $breakStart = old("breaks.$i.break_start",
                        $correction && $correction->requested_break_start
                            ? \Carbon\Carbon::parse($correction->requested_break_start)->format('H:i')
                            : ($break && $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '')
                    );

                    $breakEnd = old("breaks.$i.break_end",
                        $correction && $correction->requested_break_end
                            ? \Carbon\Carbon::parse($correction->requested_break_end)->format('H:i')
                            : ($break && $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '')
                    );
                @endphp
                <tr>
                    <th>休憩{{ $i + 1 }}</th>
                    <td>
                        <input type="time" name="breaks[{{ $i }}][break_start]" value="{{ $breakStart }}" {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>
                        〜
                        <input type="time" name="breaks[{{ $i }}][break_end]" value="{{ $breakEnd }}" {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>

                        {{-- バリデーションエラー表示 --}}
                        @error("breaks.$i.break_start")
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error("breaks.$i.break_end")
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            @endfor

            <tr>
                <th>備考</th>
                <td>
                    {{-- 備考欄（修正申請中は編集不可） --}}
                    <textarea name="note" rows="2" cols="40" {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>{{ old('note', $attendance->note) }}</textarea>

                    {{-- 備考のバリデーションエラー表示 --}}
                    @error('note')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        {{-- ボタンエリア（申請中かどうかで表示を切り替え） --}}
        <div class="action-btn-area">
            @if ($attendance->status === 'waiting_approval')
                {{-- 修正申請中ならボタンは表示せず、注意メッセージを表示 --}}
                <p class="notice">* 承認待ちのため修正できません。</p>
            @else
                <button type="submit" class="btn-primary">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection
