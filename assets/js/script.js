const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

if (menuBar) {
    menuBar.addEventListener('click', function () {
        sidebar.classList.toggle('hide');
        if (sidebar.classList.contains('hide')) {
            document.cookie = "sidebar_status=hide; path=/";
        } else {
            document.cookie = "sidebar_status=show; path=/";
        }
    });
}

// SEARCH FORM
const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

if (searchButton) {
    searchButton.addEventListener('click', function (e) {
        if (window.innerWidth < 576) {
            e.preventDefault();
            searchForm.classList.toggle('show');
            if (searchForm.classList.contains('show')) {
                searchButtonIcon.classList.replace('bx-search', 'bx-x');
            } else {
                searchButtonIcon.classList.replace('bx-x', 'bx-search');
            }
        }
    });
}

// RESPONSIVE SETUP
if (window.innerWidth < 768) {
    sidebar.classList.add('hide');
}

window.addEventListener('resize', function () {
    if (this.innerWidth > 576) {
        if (searchButtonIcon) searchButtonIcon.classList.replace('bx-x', 'bx-search');
        if (searchForm) searchForm.classList.remove('show');
    }
});

// DARK MODE LOGIC 
const switchMode = document.getElementById('switch-mode');

function setCookie(name, value, days) {
    const d = new Date();
    d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = name + "=" + value + ";" + expires + ";path=/";
}

if (switchMode) {
    if (localStorage.getItem('theme') === 'dark') {
        switchMode.checked = true;
    }

    switchMode.addEventListener('change', function () {
        if (this.checked) {
            document.body.classList.add('dark');
            document.documentElement.classList.add('dark-mode-active');
            setCookie('theme', 'dark', 30); 
            localStorage.setItem('theme', 'dark'); 
        } else {
            document.body.classList.remove('dark');
            document.documentElement.classList.remove('dark-mode-active');
            setCookie('theme', 'light', 30);
            localStorage.setItem('theme', 'light');
        }
    });
}

function updateClock() {
    const clockElement = document.getElementById('clock');
    if (clockElement) {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        clockElement.textContent = `${hours}:${minutes}:${seconds}`;
    }
}
setInterval(updateClock, 1000);
updateClock();