<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

$role = $_SESSION['role'];
$id_unit_user = $_SESSION['id_unit'];

// 1. Inisialisasi Filter
$f_unit = $_GET['filter_unit'] ?? '';
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// 2. Query untuk Rekap Per Unit (Untuk Ringkasan Laporan)
$q_rekap = "SELECT u.nama_unit, COUNT(a.id_arsip) as total 
            FROM unit_kerja u 
            LEFT JOIN arsip a ON u.id_unit = a.id_unit 
            GROUP BY u.id_unit";
$rekap_data = mysqli_query($koneksi, $q_rekap);

// 3. Query Utama Detail Arsip
$query_base = "SELECT a.*, u.nama_unit, k.nama_kategori 
               FROM arsip a 
               LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
               LEFT JOIN kategori k ON a.id_kategori = k.id_kategori WHERE 1=1";

if ($role !== 'admin' && $role !== 'pimpinan') {
    $query_base .= " AND a.id_unit = '$id_unit_user'";
}
if ($f_unit != '') {
    $query_base .= " AND a.id_unit = '$f_unit'";
}
if ($tgl_awal != '' && $tgl_akhir != '') {
    $query_base .= " AND DATE(a.created_at) BETWEEN '$tgl_awal' AND '$tgl_akhir'";
}

$query_laporan = mysqli_query($koneksi, $query_base . " ORDER BY a.created_at DESC");
$total_arsip = mysqli_num_rows($query_laporan);

// Data Unit untuk Filter
$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");
$page = 'laporan.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Laporan Lengkap - SIAPSIJUNJUNG</title>
    <style>
        .report-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--light);
            padding: 20px;
            border-radius: 12px;
            border-left: 5px solid var(--blue);
        }

        .stat-card h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .stat-card p {
            font-size: 14px;
            color: var(--dark-grey);
        }

        .filter-card {
            background: var(--light);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .filter-row {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        @media print {

            #sidebar,
            nav,
            #navbar,header,
            .filter-card,
            .btn-print,
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

        .btn-cancel{
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
                    <h1>Laporan Arsip</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">Laporan</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Rekapitulasi Sistem</a></li>
                    </ul>
                </div>
                <button onclick="window.print()" class="btn-add btn-print">
                    <i class='bx bxs-printer'></i> <span class="text">Cetak Laporan</span>
                </button>
            </div>

            <div class="print-only" style="text-align: center; padding-bottom: 10px; margin-bottom: 20px;">
                <h2>PEMERINTAH KABUPATEN SIJUNJUNG</h2>
                <h1>PUSKESMAS SIJUNJUNG</h1>
                <p>Alamat: Jl. Jenderal Sudirman No. 123, Sijunjung, Sumatera Barat</p>
            </div>

            <div class="filter-card">
                <form action="" method="GET">
                    <div class="filter-row">
                        <div class="form-group">
                            <label>Unit Kerja</label>
                            <select name="filter_unit" onchange="this.form.submit()">
                                <option value="">-- Semua Unit --</option>
                                <?php while ($u = mysqli_fetch_assoc($units)) : ?>
                                    <option value="<?= $u['id_unit']; ?>" <?= ($f_unit == $u['id_unit']) ? 'selected' : ''; ?>><?= $u['nama_unit']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Periode Mulai</label>
                            <input type="date" name="tgl_awal" value="<?= $tgl_awal; ?>" onchange="this.form.submit()">
                        </div>
                        <div class="form-group">
                            <label>Periode Akhir</label>
                            <input type="date" name="tgl_akhir" value="<?= $tgl_akhir; ?>" onchange="this.form.submit()">
                        </div>
                        <a href="laporan_arsip.php" class="btn-cancel" style="text-decoration:none; padding: 10px 15px;">Reset</a>
                    </div>
                </form>
            </div>

            <div class="report-stats">
                <div class="stat-card">
                    <p>Total Arsip Ditemukan</p>
                    <h3 style="color: var(--dark);"><?= $total_arsip; ?> <span>Berkas</span></h3>
                </div>
                <?php if ($f_unit == '') : ?>
                    <div class="stat-card" style="border-left-color: var(--orange);">
                        <p>Unit Aktif</p>
                        <h3 style="color: var(--dark);"><?= mysqli_num_rows($rekap_data); ?> <span>Unit</span></h3>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($f_unit == '') : ?>
                <div class="table-data" style="margin-bottom: 30px;">
                    <div class="order">
                        <div class="head">
                            <h3 style="color: var(--dark);">Rekap Jumlah Arsip Per Unit</h3>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Unit Kerja</th>
                                    <th>Jumlah Arsip</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($rekap_data, 0);
                                while ($rkp = mysqli_fetch_assoc($rekap_data)) :
                                    $persen = ($total_arsip > 0) ? round(($rkp['total'] / $total_arsip) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td><?= $rkp['nama_unit']; ?></td>
                                        <td><?= $rkp['total']; ?> Dokumen</td>
                                        <td><span class="status completed" style="width: <?= $persen; ?>%"><?= $persen; ?>%</span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3 style="color: var(--dark);">Detail Daftar Arsip</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Judul Arsip</th>
                                <th>Unit</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            while ($row = mysqli_fetch_assoc($query_laporan)) : ?>
                                <tr onclick="window.location='arsip_view.php?id=<?= $row['id_arsip']; ?>'" style="cursor: pointer;" title="Klik untuk detail">
                                    <td><?= $no++; ?></td>
                                    <td><strong><?= $row['kode_arsip']; ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                    <td><?= $row['nama_unit']; ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="print-only" style="margin-top: 50px; display: flex; justify-content: flex-end;">
                <div style="text-align: center;">
                    <p>Sijunjung, <?= date('d F Y'); ?></p>
                    <p>Mengetahui,</p>
                    <p><strong>Kepala Puskesmas Sijunjung</strong></p>
                    <br><br><br>
                    <p><u>__________________________</u></p>
                    <p>NIP. ............................</p>
                </div>
            </div>
        </main>
    </section>

    <script src="../../assets/js/script.js"></script>
</body>

</html>