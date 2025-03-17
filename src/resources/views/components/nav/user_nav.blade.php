{{-- 一般ユーザー向けナビゲーションメニューのパーシャルファイル --}}

<div class="nav-menu">
    <ul>
        <li><a href="/attendance">勤怠</a></li>
        <li><a href="/attendance/list">勤怠一覧</a></li>
        <li><a href="/stamp_correction_request/list">申請</a></li>
        <li>
            <form action="{{ route('logout') }}" method="POST"  style="display: inline;">
                @csrf
                <button type="submit" class="logout-link-button">ログアウト</button>
            </form>
        </li>
    </ul>
</div>
