<?php
$page = basename($_SERVER['PHP_SELF']);
// Ambil status dari cookie untuk mencegah sidebar melebar saat refresh
$sidebar_class = (isset($_COOKIE['sidebar_status']) && $_COOKIE['sidebar_status'] == 'hide') ? 'hide' : '';
?>

<script>
    // Deteksi tema secara instan sebelum render halaman
    (function() {
        const theme = localStorage.getItem('theme');
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
            document.addEventListener("DOMContentLoaded", function() {
                document.body.classList.add('dark');
            });
        }
    })();
    // Pencegah Flicker Sidebar
    if (localStorage.getItem('sidebar-status') === 'hide') {
        document.documentElement.classList.add('sidebar-hidden');
    }

    // Pencegah Flicker Dark Mode
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.classList.add('dark-mode-active');
    }
</script>

<section id="sidebar" class="<?= $sidebar_class; ?>">
    <a href="#" class="brand">
        <img src="../../assets/img/logo_baktihusada.png" class="logo" alt="">
        <span class="text">SIAPuskesmas</span>
    </a>
    <ul class="side-menu top">
        <li class="<?= ($page == 'dashboard.php') ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class='bx  bxs-home'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li class="<?= ($page == 'arsip.php') ? 'active' : '' ?>">
            <a href="arsip.php">
                <i class='bx  bxs-file'></i>
                <span class="text">Data Arsip</span>
            </a>
        </li>
        <li class="<?= ($page == 'surat_masuk.php') ? 'active' : '' ?>">
            <a href="surat_masuk.php">
                <i class='bx  bxs-envelope'></i>
                <span class="text">Surat Masuk</span>
            </a>
        </li>

        <li class="<?= ($page == 'surat_keluar.php') ? 'active' : '' ?>">
            <a href="surat_keluar.php">
                <i class='bx  bxs-envelope-open'></i>
                <span class="text">Surat Keluar</span>
            </a>
        </li>
    </ul>
    <ul class="side-menu">
        <li>
            <a href="../../logout.php" class="logout">
                <i class='bx bx-door-open'></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>
<!-- SIDEBAR -->
<script>

</script>