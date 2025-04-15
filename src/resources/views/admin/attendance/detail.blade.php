{{-- 勤怠詳細画面（管理者） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance.css') }}">
@endsection

@section('nav')
    @include('components.nav.admin_nav')
@endsection

@section('content')
<div class="attendance-detail-container">
    <h2 class="page-title"><span class="pipe">｜</span>勤怠詳細</h2>

    {{-- 管理者が勤怠を修正するフォーム（PUTメソッド） --}}
    <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
        @csrf
        @method('PUT')

        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ $attendance->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    <input type="date" name="date"
                        value="{{ old('date', \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')) }}"
                        {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>
                    @error('date')<div class="error-message">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in"
                        value="{{ old('clock_in', $attendance->clock_in_time) }}"
                        {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>
                    〜
                    <input type="time" name="clock_out"
                        value="{{ old('clock_out', $attendance->clock_out_time) }}"
                        {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>
                    @error('clock_in')<div class="error-message">{{ $message }}</div>@enderror
                    @error('clock_out')<div class="error-message">{{ $message }}</div>@enderror
                </td>
            </tr>

            {{-- 休憩は複数回対応なので繰り返し表示 --}}
            @foreach ($attendance->breakTimes as $index => $break)
                @php
                    $correction = $break->correction;  // 対応する修正申請があるか確認

                    // 休憩開始時間（修正申請がある場合はそちらを優先）
                    $breakStart = old("breaks.$index.break_start",
                        $correction && $correction->requested_break_start
                            ? \Carbon\Carbon::parse($correction->requested_break_start)->format('H:i')
                            : $break->start_time
                    );

                    // 休憩終了時間（修正申請がある場合はそちらを優先）
                    $breakEnd = old("breaks.$index.break_end",
                        $correction && $correction->requested_break_end
                            ? \Carbon\Carbon::parse($correction->requested_break_end)->format('H:i')
                            : $break->end_time
                    );
                @endphp
                <tr>
                    <th>休憩{{ $index + 1 }}</th> {{-- 休憩1、休憩2、… --}}
                    <td>
                        <input type="time" name="breaks[{{ $index }}][break_start]"
                            value="{{ $breakStart }}"
                            {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>
                        〜
                        <input type="time" name="breaks[{{ $index }}][break_end]"
                            value="{{ $breakEnd }}"
                            {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}>
                        @error("breaks.$index.break_start")<div class="error-message">{{ $message }}</div>@enderror
                        @error("breaks.$index.break_end")<div class="error-message">{{ $message }}</div>@enderror
                    </td>
                </tr>
            @endforeach

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" rows="3"
                        {{ $attendance->status === 'waiting_approval' ? 'disabled' : '' }}
                    >{{ old('note', $attendance->note) }}</textarea>
                    @error('note')<div class="error-message">{{ $message }}</div>@enderror
                </td>
            </tr>
        </table>

        <div class="action-btn-area">
            {{-- 修正申請中は修正ボタンを非表示にして、注意メッセージを表示 --}}
            @if ($attendance->status !== 'waiting_approval')
                <button type="submit" class="btn-primary">修正</button>
            @else
                <p class="text-danger">
                    ※この勤怠は現在「修正申請中」のため、編集できません。<br>
                    承認完了後に再度編集が可能になります。
                </p>
            @endif
        </div>
    </form>
</div>
@endsection
