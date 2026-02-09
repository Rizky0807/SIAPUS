<?php
session_start();
if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'pimpinan')) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

$role = $_SESSION['role'];
$nama_pimpinan = $_SESSION['nama'];

// 1. Inisialisasi Filter
$f_unit    = $_GET['filter_unit'] ?? '';
$tgl_awal  = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// 2. Query Rekap Per Unit (Untuk Grafik/Statistik di Laporan)
$q_rekap = mysqli_query($koneksi, "SELECT u.nama_unit, COUNT(a.id_arsip) as total 
                                   FROM unit_kerja u 
                                   LEFT JOIN arsip a ON u.id_unit = a.id_unit 
                                   GROUP BY u.id_unit");

// 3. Query Utama Laporan (Detail)
$query_base = "SELECT a.*, u.nama_unit, k.nama_kategori 
               FROM arsip a 
               LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
               LEFT JOIN kategori k ON a.id_kategori = k.id_kategori WHERE 1=1";

if ($f_unit != '') {
    $query_base .= " AND a.id_unit = '$f_unit'";
}
if ($tgl_awal != '' && $tgl_akhir != '') {
    $query_base .= " AND DATE(a.created_at) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$query_laporan = mysqli_query($koneksi, $query_base . " ORDER BY a.created_at DESC");
$total_data = mysqli_num_rows($query_laporan);

// Data Unit untuk Dropdown
$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");

$page = 'laporan_arsip.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Laporan Rekapitulasi - SIAPSIJUNJUNG</title>
    <style>
        .report-header {
            background: var(--light);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .filter-flex {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            background: var(--blue);
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }


        @media print {

            #sidebar,
            nav,
            #navbar,
            header,
            .filter-card,
            .btn-print,
            .form-group,
            .btn-cancel,
            .breadcrumb,
            .bx-chevron-right {
                display: none !important;
            }

            #content {
                width: 100%;
                left: 0;
                padding: 0;
            }

            .print-only {
                display: block !important;
            }

            .table-data {
                box-shadow: none !important;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 8px;
            }
        }

        .print-only {
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

        .btn-cancel {
            border-radius: 8px;
            font-size: 14px;
            padding: 15px 20px;
            margin: 21px;
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
                    <h1>Laporan Rekapitulasi Arsip</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Pimpinan</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li class="active">Laporan Tahunan</li>
                    </ul>
                </div>
                <button onclick="window.print()" class="btn-download btn-print">
                    <i class='bx bxs-printer'></i> <span class="text">Cetak Laporan</span>
                </button>
            </div>

            <div class="print-only" style="text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px;">
                <h2 style="text-transform: uppercase;">Pemerintah Kabupaten Sijunjung</h2>
                <h1 style="text-transform: uppercase;">Puskesmas Sijunjung</h1>
                <p>Jl. Jenderal Sudirman No. 123, Sijunjung | Telp: (0754) xxxxx</p>
            </div>

            <div class="report-header">
                <form action="" method="GET" id="formLaporan" class="filter-flex">
                    <div class="form-group">
                        <label style="font-size: 12px; font-weight: 600;">Unit Kerja</label><br>
                        <select name="filter_unit" onchange="this.form.submit()" style="padding: 8px; border-radius: 8px; border: 1px solid #ddd; width: 200px;">
                            <option value="">-- Semua Unit --</option>
                            <?php while ($u = mysqli_fetch_assoc($units)) : ?>
                                <option value="<?= $u['id_unit']; ?>" <?= ($f_unit == $u['id_unit']) ? 'selected' : ''; ?>><?= $u['nama_unit']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-size: 12px; font-weight: 600;">Dari Tanggal</label><br>
                        <input type="date" name="tgl_awal" value="<?= $tgl_awal; ?>" onchange="this.form.submit()" style="padding: 7px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <div class="form-group">
                        <label style="font-size: 12px; font-weight: 600;">Sampai Tanggal</label><br>
                        <input type="date" name="tgl_akhir" value="<?= $tgl_akhir; ?>" onchange="this.form.submit()" style="padding: 7px; border-radius: 8px; border: 1px solid #ddd;">
                    </div>
                    <a href="laporan_arsip.php" class="btn-cancel" style="padding: 10px 15px; text-decoration: none; border-radius: 8px; font-size: 12px; background: #eee; color: #333;">Reset</a>
                </form>
            </div>

            <div class="stat-grid">
                <div class="stat-item">
                    <h2 style="font-size: 30px;"><?= $total_data; ?></h2>
                    <p style="font-size: 13px;">Total Dokumen</p>
                </div>
                <div class="stat-item" style="background: var(--orange);">
                    <h2 style="font-size: 30px;"><?= mysqli_num_rows($q_rekap); ?></h2>
                    <p style="font-size: 13px;">Total Unit Kerja</p>
                </div>
            </div>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Detail Rekapitulasi Arsip</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Kode Arsip</th>
                                <th>Nama Dokumen</th>
                                <th>Unit Kerja</th>
                                <th>Kategori</th>
                                <th>Tanggal Upload</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            while ($row = mysqli_fetch_assoc($query_laporan)) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= $row['kode_arsip']; ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                    <td><?= $row['nama_unit']; ?></td>
                                    <td><span class="status completed" style="background: #e1f5fe; color: #039be5;"><?= $row['nama_kategori']; ?></span></td>
                                    <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($total_data == 0) : ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px;">Tidak ada data pada periode ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="print-only" style="margin-top: 50px; display: flex; justify-content: flex-end;">
                        <div style="text-align: center; width: 250px;">
                            <p>Sijunjung, <?= date('d F Y'); ?></p>
                            <p>Mengetahui,</p>
                            <p><strong>Kepala Puskesmas Sijunjung</strong></p>
                            <br><br><br>
                            <p><u>( __________________________ )</u></p>
                            <p>NIP. ............................</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>

</html>