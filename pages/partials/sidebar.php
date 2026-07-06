<?php
$page = basename($_SERVER['PHP_SELF']);
$sidebar_class = (isset($_COOKIE['sidebar_status']) && $_COOKIE['sidebar_status'] == 'hide') ? 'hide' : '';
$role = $_SESSION['role']; // Ambil role dari session login
?>
<script>
    // Deteksi tema secara instan untuk mencegah layar putih saat pindah halaman
    (function() {
        const theme = localStorage.getItem('theme') || '<?= isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light' ?>';
        if (theme === 'dark') {
            document.documentElement.classList.add('dark-mode-active');
            // Menunggu DOM siap untuk memasang class pada body
            document.addEventListener("DOMContentLoaded", function() {
                document.body.classList.add('dark');
            });
        }
    })();
</script>

<section id="sidebar" class="<?= $sidebar_class; ?>">
    <a href="#" class="brand">
        <img src="../../assets/img/logo_baktihusada.png" class="logo" alt="Logo">
        <span class="text">SIAPSIJUNJUNG</span>
    </a>
    <ul class="side-menu top">
        <li class="<?= ($page == 'dashboard.php') ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class='bx bxs-home'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>

        <?php if ($role == 'admin') : ?>
            <li class="<?= ($page == 'data_unit.php') ? 'active' : '' ?>">
                <a href="data_unit.php">
                    <i class='bx bxs-city'></i>
                    <span class="text">Data Unit Kerja</span>
                </a>
            </li>
            <li class="<?= ($page == 'data_kategori.php') ? 'active' : '' ?>">
                <a href="data_kategori.php">
                    <i class='bx bxs-category'></i>
                    <span class="text">Data Kategori</span>
                </a>
            </li>
            <li class="<?= ($page == 'data_user.php') ? 'active' : '' ?>">
                <a href="data_user.php">
                    <i class='bx bxs-user-account'></i>
                    <span class="text">Data User</span>
                </a>
            </li>
        <?php endif; ?>

        <li class="<?= ($page == 'data_arsip.php' || $page == 'surat_masuk.php' || $page == 'surat_keluar.php') ? 'active' : '' ?>">
            <a href="data_arsip.php">
                <i class='bx bxs-file-blank'></i>
                <span class="text">Data Arsip</span>
            </a>
        </li>

        <?php if ($role == 'admin' || $role == 'pimpinan') : ?>
            <li class="<?= ($page == 'log_aktivitas.php') ? 'active' : '' ?>">
                <a href="log_aktivitas.php">
                    <i class='bx bx-history'></i>
                    <span class="text">Log Aktivitas</span>
                </a>
            </li>
            <li class="<?= ($page == 'laporan_arsip.php') ? 'active' : '' ?>">
                <a href="laporan_arsip.php">
                    <i class='bx bxs-printer'></i>
                    <span class="text">Laporan Arsip</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <ul class="side-menu">
        <li>
            <a href="../../logout.php" class="logout" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class='bx bx-door-open'></i>
                <span class="text">Logout</span>
            </a>
        </li>
    </ul>
</section>