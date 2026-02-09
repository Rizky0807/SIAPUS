<?php
session_start();
if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'pimpinan')) {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";

// Query Join untuk mendapatkan detail arsip yang diunduh
$query_log = mysqli_query($koneksi, "SELECT l.*, a.nama_arsip, a.kode_arsip 
                                     FROM log_download l 
                                     JOIN arsip a ON l.id_arsip = a.id_arsip 
                                     ORDER BY l.waktu_download DESC");

// Logika Hapus Riwayat (Opsional - Hanya Admin)
if (isset($_GET['hapus_semua']) && $_SESSION['role'] == 'admin') {
    mysqli_query($koneksi, "DELETE FROM log_download");
    echo "<script>alert('Semua riwayat berhasil dibersihkan!'); window.location='riwayat_unduhan.php';</script>";
}

$page = 'riwayat.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Riwayat Unduhan - SIAPSIJUNJUNG</title>
</head>
<style>
            /* Styling Breadcrumb agar Sejajar */
            .breadcrumb {
            display: flex;
            align-items: center;
            grid-gap: 10px;
            /* Jarak antar elemen */
            margin-top: 10px;
        }

        .breadcrumb li {
            color: var(--dark);
            list-style: none;
            /* Menghilangkan titik list */
            display: flex;
            align-items: center;
        }

        .breadcrumb li a {
            color: var(--dark-grey);
            font-size: 14px;
        }

        .breadcrumb li a.active {
            color: var(--blue);
            /* Warna khusus untuk halaman aktif */
            font-weight: 600;
        }

        .breadcrumb li i {
            font-size: 18px;
            color: var(--dark-grey);
        }
</style>
<body>
    <?php include '../partials/sidebar.php'; ?>

    <section id="content">
        <?php include '../partials/navbar.php'; ?>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Riwayat Unduhan Arsip</h1>
                    <ul class="breadcrumb">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Riwayat Unduhan</a></li>
                    </ul>
                </div>
                
                <?php if ($_SESSION['role'] == 'admin') : ?>
                <a href="riwayat_unduhan.php?hapus_semua=true" class="btn-download" style="background: var(--red);" onclick="return confirm('Yakin ingin menghapus semua catatan riwayat?')">
                    <i class='bx bxs-trash'></i>
                    <span class="text">Bersihkan Riwayat</span>
                </a>
                <?php endif; ?>
            </div>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3 style="color: var(--dark)">Aktivitas Terakhir</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pengunduh</th>
                                <th>Kode Arsip</th>
                                <th>Nama Arsip</th>
                                <th>Waktu Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($query_log) > 0) :
                                while($row = mysqli_fetch_assoc($query_log)) : 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td>
                                    <p style="font-weight: 600;"><?= htmlspecialchars($row['user_pengunduh']); ?></p>
                                </td>
                                <td><span style="font-family: monospace;"><?= $row['kode_arsip']; ?></span></td>
                                <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                <td>
                                    <span style="color: var(--dark-grey); font-size: 13px;">
                                        <i class='bx bx-time-five'></i> <?= date('d/m/Y H:i', strtotime($row['waktu_download'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else :
                            ?>
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 30px; color: var(--dark-grey);">
                                    Belum ada aktivitas unduhan yang tercatat.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>

    <script src="../../assets/js/script.js"></script>
</body>
</html>