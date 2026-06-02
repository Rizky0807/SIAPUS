<?php
session_start();
if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'pimpinan')) {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";

if (!isset($koneksi)) {
    die("Koneksi ke database gagal. Periksa konfigurasi koneksi.");
}

// Query Join untuk mendapatkan detail arsip yang diunduh
$query_log = mysqli_query($koneksi, "SELECT l.*, a.nama_arsip, a.kode_arsip 
                                     FROM log_download l 
                                     JOIN arsip a ON l.id_arsip = a.id_arsip 
                                     ORDER BY l.waktu_download DESC");

// Logika Hapus Riwayat (Hanya Admin)
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
        margin-top: 10px;
    }

    .breadcrumb li {
        color: var(--dark);
        list-style: none;
        display: flex;
        align-items: center;
    }

    .breadcrumb li a {
        color: var(--dark-grey);
        font-size: 14px;
    }

    .breadcrumb li a.active {
        color: var(--blue);
        font-weight: 600;
    }

    .breadcrumb li i {
        font-size: 18px;
        color: var(--dark-grey);
    }

    /* Container tombol aksi di sebelah kanan header */
    .action-box {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    /* Standardisasi Desain Tombol Aksi (Button & Link) */
    .btn-action-custom {
        height: 36px;
        padding: 0 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        color: var(--light) !important;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    /* Efek Hover saat Kursor di Atas Tombol */
    .btn-action-custom:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    /* Elemen Kop Surat dan Tanda Tangan default disembunyikan di browser */
    .print-only {
        display: none;
    }

    /* CSS KHUSUS CETAK/PRINT PREVIEW */
    @media print {
        /* Sembunyikan Sidebar, Navbar, dan Kontrol Aksi */
        #sidebar, 
        nav, 
        .navbar,
        .breadcrumb,
        .action-box,
        .head h3 { 
            display: none !important; 
        }

        /* Lebarkan konten utama */
        #content {
            width: 100% !important;
            left: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        body {
            background: #fff !important;
        }

        /* Tampilkan Kop Surat dan Tanda Tangan */
        .print-only {
            display: block !important;
        }

        .table-data, .order {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background: #fff !important;
        }

        /* Desain garis tabel agar tegas saat dicetak */
        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #000 !important;
            padding: 8px !important;
            font-size: 12px !important;
            color: #000 !important;
        }

        th {
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
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
                
                <div class="action-box">
                    <button onclick="window.print()" class="btn-action-custom" style="background: var(--green);">
                        <i class='bx bxs-printer' style="font-size: 18px;"></i>
                        <span class="text">Cetak Laporan Log</span>
                    </button>

                    <?php if ($_SESSION['role'] == 'admin') : ?>
                    <a href="riwayat_unduhan.php?hapus_semua=true" class="btn-action-custom" style="background: var(--red);" onclick="return confirm('Yakin ingin menghapus semua catatan riwayat?')">
                        <i class='bx bxs-trash' style="font-size: 18px;"></i>
                        <span class="text">Bersihkan Riwayat</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="print-only" style="text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 30px;">
                <h2 style="text-transform: uppercase; margin: 0; font-size: 18px;">Pemerintah Kabupaten Sijunjung</h2>
                <h1 style="text-transform: uppercase; margin: 5px 0; font-size: 22px;">Puskesmas Sijunjung</h1>
                <p style="margin: 0; font-size: 12px; font-style: italic;">Jl. Jenderal Sudirman No. 123, Sijunjung | Email: puskesmassijunjung@gmail.com</p>
            </div>
            
            <div class="print-only" style="margin-bottom: 20px;">
                <h3 style="text-align: center; text-transform: uppercase; margin: 0; font-size: 15px;">Laporan Log Aktivitas Unduhan Arsip</h3>
                <p style="font-size: 12px; margin: 5px 0 0 0;">Dibuat Oleh: <?= htmlspecialchars($_SESSION['nama']); ?> (<?= ucfirst($_SESSION['role']); ?>)</p>
                <p style="font-size: 12px; margin: 2px 0 0 0;">Tanggal Cetak: <?= date('d/m/Y H:i'); ?> WIB</p>
            </div>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3 style="color: var(--dark)">Aktivitas Terakhir</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th width="60">No</th>
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
                                <td><span style="font-family: monospace; font-weight: bold;"><?= $row['kode_arsip']; ?></span></td>
                                <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                <td>
                                    <span style="color: var(--dark-grey); font-size: 13px;">
                                        <i class='bx bx-time-five'></i> <?= date('d/m/Y H:i', strtotime($row['waktu_download'])); ?> WIB
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

                    <div class="print-only" style="margin-top: 60px; display: flex; justify-content: flex-end;">
                        <div style="text-align: center; width: 250px; font-size: 13px;">
                            <p>Sijunjung, <?= date('d F Y'); ?></p>
                            <p>Mengetahui,</p>
                            <p style="font-weight: bold; margin-bottom: 70px;">Kepala Puskesmas Sijunjung</p>
                            <p>__________________________</p>
                            <p style="color: #333;">NIP. ............................</p>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </section>

    <script src="../../assets/js/script.js"></script>
</body>
</html>