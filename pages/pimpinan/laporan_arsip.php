<?php
session_start();
if (!isset($_SESSION['login']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'pimpinan')) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
if (!isset($koneksi)) {
    die("Database connection error.");
}

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
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }

        .filter-flex {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--dark-grey);
            text-transform: uppercase;
        }

        .form-control-custom {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
            background: #fff;
            outline: none;
            min-width: 160px;
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
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
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

        /* Standardisasi Desain Tombol Aksi */
        .btn-action-custom {
            height: 36px;
            padding: 0 16px;
            border-radius: 36px;
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

        .btn-action-custom:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .print-only {
            display: none;
        }

        /* CSS KHUSUS PRINT PREVIEW */
        @media print {
            #sidebar,
            nav,
            #navbar,
            header,
            .report-header,
            .btn-print,
            .breadcrumb,
            .head h3 {
                display: none !important;
            }

            #content {
                width: 100% !important;
                left: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            body {
                background: #fff !important;
            }

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

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                margin-top: 20px;
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
                        <li><a href="dashboard.php">Pimpinan</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Laporan Tahunan</a></li>
                    </ul>
                </div>
                <button onclick="window.print()" class="btn-action-custom btn-print" style="background: var(--blue);">
                    <i class='bx bxs-printer' style="font-size: 18px;"></i> 
                    <span class="text">Cetak Laporan</span>
                </button>
            </div>

            <div class="print-only" style="text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 30px;">
                <h2 style="text-transform: uppercase; margin: 0; font-size: 18px;">Pemerintah Kabupaten Sijunjung</h2>
                <h1 style="text-transform: uppercase; margin: 5px 0; font-size: 22px;">Puskesmas Sijunjung</h1>
                <p style="margin: 0; font-size: 12px; font-style: italic;">Jl. Jenderal Sudirman No. 123, Sijunjung | Telp: (0754) xxxxx</p>
            </div>

            <div class="print-only" style="margin-bottom: 20px;">
                <p style="font-size: 12px; margin: 5px 0 0 0;">Dicetak oleh: <?= htmlspecialchars($nama_pimpinan); ?> (<?= ucfirst($role); ?>)</p>
                <p style="font-size: 12px; margin: 2px 0 0 0;">Tanggal: <?= date('d/m/Y H:i'); ?> WIB</p>
            </div>

            <div class="report-header">
                <form action="" method="GET" id="formLaporan" class="filter-flex">
                    <div class="form-group">
                        <label>Unit Kerja</label>
                        <select name="filter_unit" onchange="this.form.submit()" class="form-control-custom" style="width: 200px; cursor: pointer;">
                            <option value="">-- Semua Unit --</option>
                            <?php while ($u = mysqli_fetch_assoc($units)) : ?>
                                <option value="<?= $u['id_unit']; ?>" <?= ($f_unit == $u['id_unit']) ? 'selected' : ''; ?>><?= $u['nama_unit']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" name="tgl_awal" value="<?= $tgl_awal; ?>" onchange="this.form.submit()" class="form-control-custom" style="cursor: pointer;">
                    </div>
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="tgl_akhir" value="<?= $tgl_akhir; ?>" onchange="this.form.submit()" class="form-control-custom" style="cursor: pointer;">
                    </div>
                    <a href="laporan_arsip.php" class="btn-action-custom" style="background: #eee; color: #333 !important; font-weight: 600;">
                        <i class='bx bx-refresh' style="font-size: 18px;"></i> Reset
                    </a>
                </form>
            </div>

            <div class="stat-grid">
                <div class="stat-item">
                    <h2 style="font-size: 30px; font-weight: 700;"><?= $total_data; ?></h2>
                    <p style="font-size: 13px; font-weight: 500; margin-top: 5px;">Total Dokumen</p>
                </div>
                <div class="stat-item" style="background: var(--orange);">
                    <h2 style="font-size: 30px; font-weight: 700;"><?= mysqli_num_rows($q_rekap); ?></h2>
                    <p style="font-size: 13px; font-weight: 500; margin-top: 5px;">Total Unit Kerja</p>
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
                            <?php if ($total_data > 0) : $no = 1; ?>
                                <?php while ($row = mysqli_fetch_assoc($query_laporan)) : ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><span style="font-family: monospace; font-weight: bold;"><?= $row['kode_arsip']; ?></span></td>
                                        <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                        <td><?= $row['nama_unit'] ?? 'GLOBAL'; ?></td>
                                        <td><span class="status completed" style="background: #e1f5fe; color: #039be5; font-weight: 600;"><?= $row['nama_kategori']; ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px; color: var(--dark-grey);">Tidak ada data pada periode ini.</td>
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