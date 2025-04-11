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
                    <input type="date" name="date" value="{{ old('date', \Carbon\Carbon::parse($attendance->date)->format('Y-m-d')) }}">
                    @error('date')<div class="error-message">{{ $message }}</div>@enderror
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in_time) }}">
                    〜
                    <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out_time) }}">
                    @error('clock_in')<div class="error-message">{{ $message }}</div>@enderror
                    @error('clock_out')<div class="error-message">{{ $message }}</div>@enderror
                </td>
            </tr>

            @foreach ($attendance->breakTimes as $index => $break)
                <tr>
                    <th>休憩{{ $index + 1 }}</th>
                    <td>
                        <input type="time" name="breaks[{{ $index }}][break_start]" value="{{ old("breaks.$index.break_start", $break->start_time) }}">
                        〜
                        <input type="time" name="breaks[{{ $index }}][break_end]" value="{{ old("breaks.$index.break_end", $break->end_time) }}">
                        @error("breaks.$index.break_start")<div class="error-message">{{ $message }}</div>@enderror
                        @error("breaks.$index.break_end")<div class="error-message">{{ $message }}</div>@enderror
                    </td>
                </tr>
            @endforeach

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                    @error('note')<div class="error-message">{{ $message }}</div>@enderror
                </td>
            </tr>
        </table>

        <div class="action-btn-area">
            {{-- 修正申請中は修正ボタン非表示にして、警告メッセージ表示 --}}
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
