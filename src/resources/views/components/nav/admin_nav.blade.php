{{-- 管理者向けナビゲーションメニューのパーシャルファイル --}}
<nav class="nav-menu">
    <ul>
        <li><a href="/admin/attendance">勤怠一覧</a></li>
        <li><a href="/admin/staff/list">スタッフ一覧</a></li>
        <li><a href="/stamp_correction_request/list">申請一覧</a></li>
        <li>
            <form action="{{ route('logout') }}" method="POST"  style="display: inline;">
                @csrf
                <button type="submit" class="logout-link-button">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>
