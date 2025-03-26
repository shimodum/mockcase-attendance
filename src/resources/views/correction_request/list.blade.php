{{-- 申請一覧画面（一般ユーザー） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/correction_request.css') }}">
@endsection

@section('nav')
    <div class="nav-menu">
        <ul>
            @if (Auth::user()->role === 'admin')
                <li><a href="/admin/attendance/list">勤怠一覧</a></li>
                <li><a href="/admin/staff/list">スタッフ一覧</a></li>
                <li><a href="{{ route('stamp_correction_request.list') }}">申請一覧</a></li>
            @else
                <li><a href="/attendance">勤怠</a></li>
                <li><a href="/attendance/list">勤怠一覧</a></li>
                <li><a href="{{ route('stamp_correction_request.list') }}">申請</a></li>
            @endif
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-link-button">ログアウト</button>
                </form>
            </li>
        </ul>
    </div>
@endsection


@section('content')
    <main class="correction-list">
        <h2>申請一覧</h2>

        {{-- タブ切替（ページ遷移） --}}
        <div class="tab">
            <a href="{{ route('stamp_correction_request.list', ['status' => 'waiting_approval']) }}"
               class="{{ $status === 'waiting_approval' ? 'active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}"
               class="{{ $status === 'approved' ? 'active' : '' }}">
                承認済み
            </a>
        </div>

        {{-- 申請一覧テーブル --}}
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($corrections as $correction)
                    <tr>
                        <td>{{ $status === 'waiting_approval' ? '承認待ち' : '承認済み' }}</td>
                        <td>{{ $correction->attendance->user->name ?? '不明' }}</td>
                        <td>{{ \Carbon\Carbon::parse($correction->attendance->date)->format('Y/m/d') }}</td>
                        <td>{{ $correction->request_reason }}</td>
                        <td>{{ \Carbon\Carbon::parse($correction->created_at)->format('Y/m/d') }}</td>
                        <td>
                            <a href="{{ route('stamp_correction_request.showApprove', $correction->id) }}">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">申請はありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>
@endsection
