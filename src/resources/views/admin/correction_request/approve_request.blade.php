{{-- 修正申請承認画面（管理者） --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="page-title">勤怠詳細</h2>

    <table class="detail-table">
        <tr>
            <th>名前</th>
            <td>{{ $correction->attendance->user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>
                {{ \Carbon\Carbon::parse($correction->date)->format('Y年 n月j日') }}
            </td>
        </tr>
        <tr>
            <th>出勤・退勤</th>
            <td>
                {{ \Carbon\Carbon::parse($correction->start_time)->format('H:i') }} 〜 
                {{ \Carbon\Carbon::parse($correction->end_time)->format('H:i') }}
            </td>
        </tr>
        <tr>
            <th>休憩</th>
            <td>
                {{ \Carbon\Carbon::parse($correction->break_start)->format('H:i') }} 〜 
                {{ \Carbon\Carbon::parse($correction->break_end)->format('H:i') }}
            </td>
        </tr>
        <tr>
            <th>休憩2</th>
            <td>
                {{-- 休憩2は任意なのでnullチェック --}}
                @if ($correction->break2_start && $correction->break2_end)
                    {{ \Carbon\Carbon::parse($correction->break2_start)->format('H:i') }} 〜 
                    {{ \Carbon\Carbon::parse($correction->break2_end)->format('H:i') }}
                @else
                    ー
                @endif
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>{{ $correction->note }}</td>
        </tr>
    </table>

    {{-- 承認ボタン --}}
    <form method="POST" action="{{ route('stamp_correction_request.approve', $correction->id) }}" class="approve-form">
        @csrf
        <div class="form-group">
            <label for="admin_comment">管理者コメント</label>
            <textarea name="admin_comment" id="admin_comment" rows="3" placeholder="コメントを入力してください">{{ old('admin_comment') }}</textarea>
        </div>

        <button type="submit" class="btn-approve">承認</button>
    </form>
</div>
@endsection
