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
    <h2 class="title">勤怠詳細</h2>

    <table class="attendance-detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $attendance->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年n月j日（D）') }}</td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}
                 ～ 
                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                @if ($attendance->breakTimes && $attendance->breakTimes->count())
                    <ul>
                        @foreach ($attendance->breakTimes as $break)
                            <li>
                                {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '-' }}
                                ～
                                {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '-' }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    休憩記録なし
                @endif
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>電車遅延のため</td> {{-- 今はダミーでOK --}}
        </tr>
    </table>

    <div class="action-btn-area">
        <a href="#" class="btn-primary">修正</a> {{-- ※リンク先未定ならダミー --}}
    </div>
</div>
@endsection
