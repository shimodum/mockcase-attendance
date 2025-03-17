{{-- 一般ユーザー向け（退勤後）ナビゲーションメニューのパーシャルファイル --}}

<nav class="nav-menu">
    <ul>
        <li><a href="/attendance/list">今月の出勤一覧</a></li>
        <li><a href="/stamp_correction_request/list">申請一覧</a></li>
        <li>
            <form action="{{ route('logout') }}" method="POST"  style="display: inline;">
                @csrf
                <button type="submit" class="logout-link-button">ログアウト</button>
            </form>
        </li>
    </ul>
</nav>