{{-- スタッフ一覧画面（管理者） --}}
@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-staff.css') }}">
@endsection

@section('nav')
    @include('components.nav.admin_nav') {{-- 管理者用ナビ --}}
@endsection

@section('content')
<div class="staff-list-container">
    <h2 class="page-title">
        <span class="pipe">｜</span>
        <span class="bold-title">スタッフ一覧</span>
    </h2>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ url('/admin/attendance/staff/' . $user->id) }}" class="detail-link">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
