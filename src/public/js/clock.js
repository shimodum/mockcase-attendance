
function updateClock() {
    const now = new Date();

    const days = ['日', '月', '火', '水', '木', '金', '土'];
    const formattedDate = `${now.getFullYear()}年${now.getMonth() + 1}月${now.getDate()}日（${days[now.getDay()]}）`;

    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    const formattedTime = `${hours}:${minutes}`;

    const dateElem = document.getElementById('current-date');
    const timeElem = document.getElementById('current-time');

    if (dateElem && timeElem) {
        dateElem.textContent = formattedDate;
        timeElem.textContent = formattedTime;
    }
}

updateClock();
setInterval(updateClock, 1000);
