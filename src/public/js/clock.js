// このファイルは「現在の日付と時刻」をリアルタイムに表示するためのJavaScript

function updateClock() {
    const now = new Date();

    const days = ['日', '月', '火', '水', '木', '金', '土'];
    const formattedDate = `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日（${days[now.getDay()]}）`;

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const formattedTime = `${hours}:${minutes}`;

    // HTML内の「#current-date」と「#current-time」の要素を取得
    const dateElem = document.getElementById('current-date');
    const timeElem = document.getElementById('current-time');

    // 該当の要素がある場合に表示内容を更新する
    if (dateElem && timeElem) {
        dateElem.textContent = formattedDate;
        timeElem.textContent = formattedTime;
    }
}

updateClock(); //最初に1回だけ現在時刻を表示（または更新）
setInterval(updateClock, 1000); // その後、1秒ごとに自動で時計を更新し続ける
