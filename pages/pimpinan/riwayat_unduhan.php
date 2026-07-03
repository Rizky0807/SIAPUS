<?php
session_start();
date_default_timezone_set('Asia/Jakarta');

// Proteksi halaman khusus pimpinan resmi
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pimpinan') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";

/** @var mysqli $koneksi */
if (!isset($koneksi)) {
    die("Database connection error. Please check the configuration in 'koneksi.php'.");
}

$role = $_SESSION['role'];
$nama_user = $_SESSION['nama'] ?? 'Pimpinan';

// FITUR FILTERNYA: Inisialisasi Filter Rentang Waktu
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// Query Dasar Join Log
$query_base = "SELECT l.*, a.nama_arsip, a.kode_arsip 
               FROM log_download l 
               JOIN arsip a ON l.id_arsip = a.id_arsip WHERE 1=1";

// Masukkan filter tanggal ke dalam query log jika dipilih oleh pimpinan
if ($tgl_awal != '' && $tgl_akhir != '') {
    $query_base .= " AND DATE(l.waktu_download) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$query_log = mysqli_query($koneksi, $query_base . " ORDER BY l.waktu_download DESC");

$page = 'riwayat_download.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Riwayat Unduhan - SIAPSIJUNJUNG</title>
</head>
<style>
    /* 💡 KUNCI LAYAR SATU HALAMAN PENUH DESKTOP SINKRON VERSION KHAS SIAPUS */
    html, body {
        height: 100vh;
        overflow: hidden !important;
    }

    #content main {
        height: calc(100vh - 56px);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        padding: 24px;
        box-sizing: border-box;
    }

    .head-title {
        flex-shrink: 0;
        margin-bottom: 20px !important;
    }

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
        text-decoration: none;
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

    /* Standardisasi Desain Tombol Aksi */
    .btn-action-custom {
        height: 36px;
        padding: 0 16px;
        border-radius: 36px;
        font-size: 14px;
        font-weight: 600;
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

    .head h3 {
        font-size: 14px;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Styling Filter Form Pas Sejajar Bawaan Template Laporan */
    .filter-card-custom {
        background: var(--white-card, #fff);
        padding: 15px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        flex-shrink: 0;
    }

    .form-group-custom {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .form-group-custom label {
        font-size: 12px;
        font-weight: 600;
        color: var(--dark-grey);
    }

    .form-group-custom input {
        padding: 8px 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: var(--light);
        color: var(--dark);
        outline: none;
        height: 35px;
        box-sizing: border-box;
    }

    /* CONFIG SCROLL INTERNAL MANDIRI KHAS DATA ARSIP */
    .table-data {
        display: flex;
        flex-direction: column;
        flex-grow: 1;
        min-height: 0;
    }

    .table-data .order {
        flex-grow: 1;
        overflow-y: auto;
        min-height: 0;
    }

    .table-data .order table th {
        position: sticky;
        top: 0;
        background: var(--white-card, #fff);
        z-index: 10;
    }

    .table-data .order::-webkit-scrollbar {
        width: 5px;
    }
    .table-data .order::-webkit-scrollbar-thumb {
        background: var(--dark-grey);
        border-radius: 5px;
    }

    .print-only {
        display: none !important;
    }

    /* CETAK/PRINT PREVIEW LEMBARAN KERTAS */
    @media print {
        #sidebar,
        nav,
        #navbar,
        header,
        .breadcrumb,
        .filter-card-custom,
        .action-box {
            display: none !important;
        }

        #content, main, body {
            width: 100% !important;
            left: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            position: static !important;
            overflow: visible !important;
            height: auto !important;
        }

        body {
            background: #fff !important;
            color: #000 !important;
        }

        .print-only {
            display: block !important;
        }

        .head h3 {
            color: #000 !important;
            font-size: 12px !important;
            margin-top: 15px;
        }

        .table-data, .order {
            box-shadow: none !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            background: #fff !important;
            overflow: visible !important;
            height: auto !important;
            border-radius: 0 !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 5px;
            margin-bottom: 20px;
            page-break-inside: auto !important;
            border-radius: 0 !important;
        }

        tr {
            page-break-inside: avoid !important;
            page-break-after: auto !important;
        }

        th, td {
            border: 1px solid #000 !important;
            padding: 8px !important;
            font-size: 12px !important;
            color: #000 !important;
            border-radius: 0 !important;
        }

        th {
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-signature {
            page-break-inside: avoid !important;
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
                    <button onclick="window.print()" class="btn-action-custom" style="background: var(--green); border-radius: 10px;">
                        <i class='bx bxs-printer' style="font-size: 14px;"></i>
                        <span class="text">Cetak Laporan Log</span>
                    </button>
                </div>
            </div>

            <div class="print-only" style="margin-bottom: 20px;">
                <table style="width: 100%; border: none !important; border-collapse: collapse !important; margin-bottom: 5px !important;">
                    <tr style="border: none !important;">
                        <td style="width: 12%; text-align: center; border: none !important; padding: 0 !important; vertical-align: middle;">
                            <img src="../../assets/img/logo_sijunjung.png" alt="Logo Sijunjung" style="width: 70px; height: auto;">
                        </td>
                        <td style="text-align: center; border: none !important; padding: 0 10px !important; vertical-align: middle; line-height: 1.3;">
                            <h3 style="text-transform: uppercase; margin: 0; font-size: 15px; font-weight: 700; color: #000;">Pemerintah Kabupaten Sijunjung</h3>
                            <h2 style="text-transform: uppercase; margin: 2px 0; font-size: 16px; font-weight: 700; color: #000;">Dinas Kesehatan</h2>
                            <h1 style="text-transform: uppercase; margin: 2px 0; font-size: 19px; font-weight: 800; color: #000; letter-spacing: 0.5px;">UPTD Puskesmas Sijunjung</h1>
                            <p style="margin: 3px 0 0 0; font-size: 11px; color: #000;">Jl. Puskesmas No.85 Jorong Pasar, Nagari Sijunjung, Kecamatan Sijunjung</p>
                            <p style="margin: 1px 0 0 0; font-size: 10px; font-style: italic; color: #000;">E-mail: puskesmassijunjung@sijunjung.go.id | Kode Pos: 27553</p>
                        </td>
                        <td style="width: 12%; text-align: center; border: none !important; padding: 0 !important; vertical-align: middle;">
                            <img src="../../assets/img/logo_baktihusada.png" alt="Logo Puskesmas" style="width: 65px; height: auto;">
                        </td>
                    </tr>
                </table>
                <div style="border-top: 3px solid #000; border-bottom: 1px solid #000; height: 2px; margin-top: 8px; margin-bottom: 15px;"></div>
            </div>

            <div class="print-only" style="margin-bottom: 20px; font-size: 12px; line-height: 1.6;">
                <table style="width: 100%; border: none !important; margin: 0 !important;">
                    <tr style="border: none !important;">
                        <td style="width: 18%; border: none !important; padding: 2px !important;"><strong>Jenis Dokumen</strong></td>
                        <td style="width: 2%; border: none !important; padding: 2px !important;">:</td>
                        <td style="border: none !important; padding: 2px !important;">Laporan Log Aktivitas Unduhan Arsip Digital</td>
                        <td style="width: 15%; border: none !important; padding: 2px !important;"><strong>Dicetak Oleh</strong></td>
                        <td style="width: 2%; border: none !important; padding: 2px !important;">:</td>
                        <td style="border: none !important; padding: 2px !important;"><?= htmlspecialchars($nama_user); ?> (<?= ucfirst($role); ?>)</td>
                    </tr>
                    <tr style="border: none !important;">
                        <td style="border: none !important; padding: 2px !important;"><strong>Status Akses</strong></td>
                        <td style="border: none !important; padding: 2px !important;">:</td>
                        <td style="border: none !important; padding: 2px !important;">Pimpinan Resmi (Hak Audit Trail)</td>
                        <td style="border: none !important; padding: 2px !important;"><strong>Periode Log</strong></td>
                        <td style="border: none !important; padding: 2px !important;">:</td>
                        <td style="border: none !important; padding: 2px !important;">
                            <?= ($tgl_awal != '' && $tgl_akhir != '') ? date('d/m/Y', strtotime($tgl_awal)) . " s/d " . date('d/m/Y', strtotime($tgl_akhir)) : "Semua Periode"; ?>
                        </td>
                    </tr>
                </table>
                <hr style="border: 1px solid #000; margin-top: 15px;">
            </div>

            <div class="filter-card-custom">
                <form action="" method="GET" style="display: flex; gap: 15px; flex-wrap: wrap; width: 100%; align-items: flex-end;">
                    <div class="form-group-custom">
                        <label>Periode Mulai</label>
                        <input type="date" name="tgl_awal" value="<?= $tgl_awal; ?>" onchange="this.form.submit()" style="cursor: pointer;">
                    </div>
                    <div class="form-group-custom">
                        <label>Periode Akhir</label>
                        <input type="date" name="tgl_akhir" value="<?= $tgl_akhir; ?>" onchange="this.form.submit()" style="cursor: pointer;">
                    </div>
                    <div class="form-group-custom">
                        <a href="riwayat_unduhan.php" class="btn-action-custom" style="background: #e2e8f0; color: #334155 !important; border-radius: 8px; height: 35px; padding: 0 15px;">
                            <i class='bx bx-refresh' style="font-size: 18px;"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3 style="color: var(--dark)">Log Aktivitas Riwayat Unduhan Arsip Digital</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th width="60" style="text-align: center;">No</th>
                                <th>Nama Pengunduh</th>
                                <th>Kode Arsip</th>
                                <th>Nama Arsip</th>
                                <th width="220">Waktu Akses</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($query_log) > 0) :
                                while ($row = mysqli_fetch_assoc($query_log)) :
                            ?>
                                    <tr>
                                        <td style="text-align: center;"><?= $no++; ?></td>
                                        <td>
                                            <p style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($row['user_pengunduh']); ?></p>
                                        </td>
                                        <td><span style="font-family: monospace; font-weight: bold;"><?= $row['kode_arsip']; ?></span></td>
                                        <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                        <td>
                                            <span style="color: var(--dark-grey); font-size: 13px; font-weight: 500;">
                                                <i class='bx bx-time-five' style="vertical-align: middle;"></i> <?= date('d/m/Y | H:i', strtotime($row['waktu_download'])); ?> WIB
                                            </span>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else :
                                ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: var(--dark-grey);">
                                        Belum ada aktivitas unduhan yang tercatat pada periode ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="print-only print-signature" style="margin-top: 60px; display: flex; justify-content: flex-end;">
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