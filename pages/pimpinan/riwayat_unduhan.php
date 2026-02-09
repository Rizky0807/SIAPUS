<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

$role = $_SESSION['role'];
$nama_pimpinan = $_SESSION['nama'];

// 1. QUERY RIWAYAT: Pimpinan & Admin bisa melihat seluruh aktivitas lintas unit
$query_base = "SELECT l.*, a.nama_arsip, a.kode_arsip, u.nama_unit 
               FROM log_download l
               JOIN arsip a ON l.id_arsip = a.id_arsip 
               LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit";

// Jika Petugas iseng akses, filter hanya unitnya. Pimpinan/Admin tetap akses semua.
if ($role == 'petugas') {
    $id_unit_user = $_SESSION['id_unit'];
    $query_base .= " WHERE a.id_unit = '$id_unit_user'";
}

$query_base .= " ORDER BY l.waktu_download DESC";
$sql_log = mysqli_query($koneksi, $query_base);

$page = 'riwayat_download.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Riwayat Aktivitas - SIAPSIJUNJUNG</title>
    <style>
        .log-card {
            background: var(--light);
            border-radius: 12px;
            padding: 20px;
        }

        .user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: var(--blue);
        }

        .unit-tag {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 20px;
            background: #e0e0e0;
            color: #666;
        }

        @media print {

            #sidebar,
            nav,
            #navbar,
            header,
            .btn-print,
            .breadcrumb {
                display: none !important;
            }

            #content {
                width: 100%;
                left: 0;
                padding: 0;
            }

            .print-header {
                display: block !important;
                text-align: center;
                border-bottom: 2px solid #000;
                margin-bottom: 20px;
            }
        }

        .print-header {
            display: none;
        }

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
</head>

<body>
    <?php include '../partials/sidebar.php'; ?>
    <section id="content">
        <?php include '../partials/navbar.php'; ?>
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Monitoring Unduhan Arsip</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Pimpinan</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Riwayat Aktivitas</a></li>
                    </ul>
                </div>
                <button onclick="window.print()" class="btn-download btn-print">
                    <i class='bx bxs-printer'></i>
                    <span class="text">Cetak Laporan Log</span>
                </button>
            </div>

            <div class="print-header">
                <h2>PUSKESMAS SIJUNJUNG</h2>
                <h3>LAPORAN LOG AKTIVITAS UNDUHAN ARSIP</h3>
                <p>Dicetak oleh: <?= $nama_pimpinan; ?> | Tanggal: <?= date('d/m/Y H:i'); ?></p>
            </div>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Daftar Aktivitas Pengguna</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Pengguna</th>
                                <th>Arsip / Dokumen</th>
                                <th>Unit Asal</th>
                                <th>Waktu Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($sql_log) > 0) :
                                while ($row = mysqli_fetch_assoc($sql_log)) :
                            ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td>
                                            <div class="user-badge">
                                                <i class='bx bxs-user-pin'></i>
                                                <?= htmlspecialchars($row['user_pengunduh']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-size: 13px;">
                                                <strong><?= $row['kode_arsip']; ?></strong><br>
                                                <span style="color: #888;"><?= htmlspecialchars($row['nama_arsip']); ?></span>
                                            </div>
                                        </td>
                                        <td><span class="unit-tag"><?= $row['nama_unit'] ?? 'GLOBAL'; ?></span></td>
                                        <td>
                                            <span style="font-size: 12px;">
                                                <i class='bx bx-calendar'></i> <?= date('d/m/Y', strtotime($row['waktu_download'])); ?><br>
                                                <i class='bx bx-time'></i> <?= date('H:i', strtotime($row['waktu_download'])); ?> WIB
                                            </span>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else :
                                ?>
                                <tr>
                                    <td colspan="5" style="text-align:center; padding: 30px;">Belum ada aktivitas terekam.</td>
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