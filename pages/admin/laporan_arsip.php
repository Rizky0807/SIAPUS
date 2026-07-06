<?php
session_start();

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
/** @var mysqli $koneksi */

$role = $_SESSION['role'];
$id_unit_user = $_SESSION['id_unit'];
$nama_admin = $_SESSION['nama'] ?? 'Administrator';

// Hanya admin & pimpinan yang boleh mengakses halaman Laporan Arsip.
// Petugas unit tidak memiliki menu ini, jadi harus ditolak sebelum baris manapun dieksekusi.
if (!in_array($role, ['admin', 'pimpinan'], true)) {
    header("Location: ../dashboard.php"); // sesuaikan dengan halaman dashboard/403 milikmu
    exit;
}

// Array Nama Bulan Indonesia untuk filter & cetak
$nama_bulan_indo = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

// 1. Inisialisasi Filter Baru (Bulan, Tahun, Unit)
$f_unit = $_GET['filter_unit'] ?? '';
$f_bulan = $_GET['filter_bulan'] ?? '';
$f_tahun = $_GET['filter_tahun'] ?? '';

// 2. Query Utama Detail Arsip (pakai prepared statement, halaman ini sudah
//    dipastikan hanya diakses admin/pimpinan sehingga tidak perlu filter per-unit_user lagi)
$query_base = "SELECT a.*, u.nama_unit, k.nama_kategori 
               FROM arsip a 
               LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
               LEFT JOIN kategori k ON a.id_kategori = k.id_kategori WHERE 1=1";

$params = [];
$types = '';

if ($f_unit != '') {
    $query_base .= " AND a.id_unit = ?";
    $params[] = $f_unit;
    $types .= 's';
}
if ($f_bulan != '') {
    $query_base .= " AND MONTH(a.created_at) = ?";
    $params[] = $f_bulan;
    $types .= 's';
}
if ($f_tahun != '') {
    $query_base .= " AND YEAR(a.created_at) = ?";
    $params[] = $f_tahun;
    $types .= 's';
}

$query_base .= " ORDER BY a.created_at DESC";
$stmt = mysqli_prepare($koneksi, $query_base);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$query_laporan = mysqli_stmt_get_result($stmt);
$total_arsip = mysqli_num_rows($query_laporan);

// 3. Query untuk Rekap Per Unit (prepared statement juga)
$q_rekap = "SELECT u.nama_unit, COUNT(a.id_arsip) as total 
            FROM unit_kerja u 
            LEFT JOIN arsip a ON u.id_unit = a.id_unit";

$rekap_params = [];
$rekap_types = '';

if ($f_bulan != '') {
    $q_rekap .= " AND MONTH(a.created_at) = ?";
    $rekap_params[] = $f_bulan;
    $rekap_types .= 's';
}
if ($f_tahun != '') {
    $q_rekap .= " AND YEAR(a.created_at) = ?";
    $rekap_params[] = $f_tahun;
    $rekap_types .= 's';
}

$q_rekap .= " GROUP BY u.id_unit ORDER BY total DESC";
$stmt_rekap = mysqli_prepare($koneksi, $q_rekap);
if (!empty($rekap_params)) {
    mysqli_stmt_bind_param($stmt_rekap, $rekap_types, ...$rekap_params);
}
mysqli_stmt_execute($stmt_rekap);
$rekap_data = mysqli_stmt_get_result($stmt_rekap);

// Proses data rekap dan hitung total unit yang memiliki arsip
$rekap_array = [];
$total_unit_berarsip = 0;
while ($rkp = mysqli_fetch_assoc($rekap_data)) {
    $rekap_array[] = $rkp;
    if ($rkp['total'] > 0) {
        $total_unit_berarsip++;
    }
}

// 4. Query Rekap Per Kategori Arsip (pola sama seperti rekap per unit di atas)
$q_rekap_kategori = "SELECT k.nama_kategori, COUNT(a.id_arsip) as total 
                      FROM kategori k 
                      LEFT JOIN arsip a ON k.id_kategori = a.id_kategori";

$rekap_kat_params = [];
$rekap_kat_types = '';

if ($f_bulan != '') {
    $q_rekap_kategori .= " AND MONTH(a.created_at) = ?";
    $rekap_kat_params[] = $f_bulan;
    $rekap_kat_types .= 's';
}
if ($f_tahun != '') {
    $q_rekap_kategori .= " AND YEAR(a.created_at) = ?";
    $rekap_kat_params[] = $f_tahun;
    $rekap_kat_types .= 's';
}
if ($f_unit != '') {
    $q_rekap_kategori .= " AND a.id_unit = ?";
    $rekap_kat_params[] = $f_unit;
    $rekap_kat_types .= 's';
}

$q_rekap_kategori .= " GROUP BY k.id_kategori ORDER BY total DESC";
$stmt_rekap_kat = mysqli_prepare($koneksi, $q_rekap_kategori);
if (!empty($rekap_kat_params)) {
    mysqli_stmt_bind_param($stmt_rekap_kat, $rekap_kat_types, ...$rekap_kat_params);
}
mysqli_stmt_execute($stmt_rekap_kat);
$rekap_kategori_data = mysqli_stmt_get_result($stmt_rekap_kat);

$rekap_kategori_array = [];
$total_kategori_terpakai = 0; // 💡 Counter kategori terpakai berdasarkan filter aktif
while ($rk = mysqli_fetch_assoc($rekap_kategori_data)) {
    $rekap_kategori_array[] = $rk;
    if ($rk['total'] > 0) {
        $total_kategori_terpakai++;
    }
}

// 5. Query Tren Jumlah Arsip 12 Bulan Terakhir (untuk grafik)
$q_tren = "SELECT DATE_FORMAT(created_at, '%Y-%m') as periode, COUNT(*) as total
           FROM arsip
           WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
           GROUP BY periode
           ORDER BY periode ASC";
$tren_data = mysqli_query($koneksi, $q_tren);

// Susun 12 bulan penuh (termasuk yang 0/tidak ada arsip) supaya grafik tidak bolong
$tren_map = [];
while ($t = mysqli_fetch_assoc($tren_data)) {
    $tren_map[$t['periode']] = (int) $t['total'];
}
$tren_label = [];
$tren_value = [];
for ($i = 11; $i >= 0; $i--) {
    $ts = strtotime("-$i month");
    $key = date('Y-m', $ts);
    $label = $nama_bulan_indo[date('m', $ts)] . " " . date('Y', $ts);
    $tren_label[] = $label;
    $tren_value[] = $tren_map[$key] ?? 0;
}

$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");

// Variabel bantu metadata saat dicetak
$nama_unit_aktif = "Semua Unit";
if ($f_unit != '') {
    mysqli_data_seek($units, 0);
    while ($u_info = mysqli_fetch_assoc($units)) {
        if ($u_info['id_unit'] == $f_unit) {
            $nama_unit_aktif = $u_info['nama_unit'];
            break;
        }
    }
}

// Format Periode untuk Cetak Laporan
$periode_cetak = "Semua Periode";
if ($f_bulan != '' || $f_tahun != '') {
    $teks_bulan = $f_bulan != '' ? $nama_bulan_indo[$f_bulan] . " " : "";
    $teks_tahun = $f_tahun != '' ? $f_tahun : "";
    $periode_cetak = $teks_bulan . $teks_tahun;
}

$page = 'laporan_arsip.php';
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
            align-items: center;
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
            height: 18px;
            display: block;
        }

        .form-control-custom {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 14px;
            background: #fff;
            outline: none;
            min-width: 160px;
            box-sizing: border-box;
            height: 38px;
        }

        .table-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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

        .btn-action-custom {
            height: 38px;
            padding: 0 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            grid-gap: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .btn-action-custom:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .print-only {
            display: none !important;
        }

        /* 💡 CONFIG MEDIAPRINT: REKAYASA HALAMAN HASIL CETAK KERTAS */
        @media print {
            #sidebar,
            nav,
            #navbar,
            header,
            .filter-card,
            .btn-print,
            .breadcrumb,
            .bx-chevron-right,
            .hide-on-print { 
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

            /* 💡 Paksa Statistik tetap muncul di kertas cetak (menjadi 3 kolom) */
            .report-stats {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 20px !important;
                margin-bottom: 20px !important;
            }

            .stat-card {
                background: #fff !important;
                border: 1px solid #000 !important;
                border-left: 5px solid #000 !important;
                padding: 15px !important;
            }

            .table-title {
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
                <button onclick="window.print()" class="btn-action-custom btn-print" style="background: var(--green); color: #fff; border-radius: 10px;">
                    <i class='bx bxs-printer' style="font-size: 18px;"></i> <span class="text">Cetak Laporan</span>
                </button>
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
                        <td style="width: 15%; border: none !important; padding: 2px !important;"><strong>Jenis Laporan</strong></td>
                        <td style="width: 2%; border: none !important; padding: 2px !important;">:</td>
                        <td style="border: none !important; padding: 2px !important;">Laporan Arsip Digital</td>
                        <td style="width: 15%; border: none !important; padding: 2px !important;"><strong>Dicetak Oleh</strong></td>
                        <td style="width: 2%; border: none !important; padding: 2px !important;">:</td>
                        <td style="border: none !important; padding: 2px !important;"><?= htmlspecialchars($nama_admin); ?> (<?= ucfirst($role); ?>)</td>
                    </tr>
                    <tr style="border: none !important;">
                        <td style="border: none !important; padding: 2px !important;"><strong>Filter Unit</strong></td>
                        <td style="border: none !important; padding: 2px !important;">:</td>
                        <td style="border: none !important; padding: 2px !important;"><?= $nama_unit_aktif; ?></td>
                        <td style="border: none !important; padding: 2px !important;"><strong>Tanggal Cetak</strong></td>
                        <td style="border: none !important; padding: 2px !important;">:</td>
                        <td style="border: none !important; padding: 2px !important;"><?= date('d/m/Y H:i'); ?> WIB</td>
                    </tr>
                    <tr style="border: none !important;">
                        <td style="border: none !important; padding: 2px !important;"><strong>Periode Data</strong></td>
                        <td style="border: none !important; padding: 2px !important;">:</td>
                        <td colspan="4" style="border: none !important; padding: 2px !important;">
                            <?= $periode_cetak; ?>
                        </td>
                    </tr>
                </table>
                <hr style="border: 1px solid #000; margin-top: 15px;">
            </div>

            <div class="filter-card">
                <form action="" method="GET">
                    <div class="filter-row">
                        <div class="form-group">
                            <label>Unit Kerja</label>
                            <select name="filter_unit" onchange="this.form.submit()" class="form-control-custom" style="width: 250px; cursor: pointer; padding: 10px;">
                                <option value="">-- Semua Unit --</option>
                                <?php 
                                mysqli_data_seek($units, 0); 
                                while ($u = mysqli_fetch_assoc($units)) : 
                                ?>
                                    <option value="<?= $u['id_unit']; ?>" <?= ($f_unit == $u['id_unit']) ? 'selected' : ''; ?>><?= $u['nama_unit']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Bulan</label>
                            <select name="filter_bulan" onchange="this.form.submit()" class="form-control-custom" style="cursor: pointer; width: 160px; padding: 10px;">
                                <option value="">-- Semua Bulan --</option>
                                <?php foreach ($nama_bulan_indo as $key => $val) : ?>
                                    <option value="<?= $key; ?>" <?= $f_bulan == $key ? 'selected' : ''; ?>><?= $val; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tahun</label>
                            <select name="filter_tahun" onchange="this.form.submit()" class="form-control-custom" style="cursor: pointer; width: 140px; padding: 10px;">
                                <option value="">-- Semua Tahun --</option>
                                <?php 
                                $tahun_sekarang = date('Y');
                                for ($i = $tahun_sekarang; $i >= $tahun_sekarang - 4; $i--) : 
                                ?>
                                    <option value="<?= $i; ?>" <?= $f_tahun == $i ? 'selected' : ''; ?>><?= $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <a href="laporan_arsip.php" class="btn-action-custom" style="background: #e2e8f0; color: #334155 !important;">
                                <i class='bx bx-refresh' style="font-size: 18px;"></i> Reset Filter
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="report-stats">
                <div class="stat-card">
                    <p>Total Arsip</p>
                    <h3 style="color: var(--dark);"><?= $total_arsip; ?> <span style="font-size: 14px; font-weight: normal; color: var(--dark-grey);">Berkas</span></h3>
                </div>
                <?php if ($f_unit == '') : ?>
                    <div class="stat-card" style="border-left-color: var(--orange);">
                        <p>Total Unit yang Memiliki Arsip</p>
                        <h3 style="color: var(--dark);"><?= $total_unit_berarsip; ?> <span style="font-size: 14px; font-weight: normal; color: var(--dark-grey);">Unit</span></h3>
                    </div>
                <?php endif; ?>
                <div class="stat-card" style="border-left-color: var(--green);">
                    <p>Kategori Terpakai</p>
                    <h3 style="color: var(--dark);"><?= $total_kategori_terpakai; ?> <span style="font-size: 14px; font-weight: normal; color: var(--dark-grey);">Kategori</span></h3>
                </div>
            </div>

            <?php if ($f_unit == '') : ?>
                <div class="table-title">Rekapitulasi Arsip Per Unit Kerja</div>
                <div class="table-data" style="margin-bottom: 30px;">
                    <div class="order">
                        <table>
                            <thead>
                                <tr>
                                    <th>Unit Kerja</th>
                                    <th>Jumlah Arsip</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rekap_array as $rkp) :
                                    $persen = ($total_arsip > 0) ? round(($rkp['total'] / $total_arsip) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($rkp['nama_unit']); ?></td>
                                        <td><?= $rkp['total']; ?> Dokumen</td>
                                        <td><span class="status completed" style="width: <?= $persen; ?>%"><?= $persen; ?>%</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <div class="table-title">Rekapitulasi Arsip Per Kategori</div>
            <div class="table-data" style="margin-bottom: 30px;">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th>Kategori Arsip</th>
                                <th>Jumlah Arsip</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($rekap_kategori_array) > 0) : ?>
                                <?php foreach ($rekap_kategori_array as $rk) :
                                    $persen_kat = ($total_arsip > 0) ? round(($rk['total'] / $total_arsip) * 100, 1) : 0;
                                ?>
                                    <tr>
                                        <td><?= htmlspecialchars($rk['nama_kategori']); ?></td>
                                        <td><?= $rk['total']; ?> Dokumen</td>
                                        <td><span class="status completed" style="width: <?= $persen_kat; ?>%"><?= $persen_kat; ?>%</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 20px; color: var(--dark-grey);">Belum ada data kategori.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="table-title hide-on-print">Tren Jumlah Arsip Masuk (12 Bulan Terakhir)</div>
            <div class="table-data hide-on-print" style="margin-bottom: 30px; padding: 20px;">
                <canvas id="chartTrenArsip" height="90"></canvas>
            </div>

            <div class="table-title hide-on-print">
                <?= ($f_unit == '') ? 'Detail Daftar Seluruh Arsip Digital' : 'Detail Daftar Arsip Kerja - ' . htmlspecialchars($nama_unit_aktif); ?>
            </div>
            <div class="table-data hide-on-print">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Kode Arsip</th>
                                <th>Nama Arsip</th>
                                <th>Kategori Arsip</th>
                                <th>Unit Kerja</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_arsip > 0) : $no = 1; ?>
                                <?php while ($row = mysqli_fetch_assoc($query_laporan)) : ?>
                                    <tr onclick="window.location='arsip_view.php?id=<?= $row['id_arsip']; ?>'" style="cursor: pointer;" title="Klik untuk detail">
                                        <td><?= $no++; ?></td>
                                        <td><span style="font-family: monospace; font-weight: bold;"><?= $row['kode_arsip']; ?></span></td>
                                        <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_kategori'] ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($row['nama_unit'] ?? 'GLOBAL'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: var(--dark-grey);">Tidak ada data arsip pada periode/filter ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="print-only print-signature" style="margin-top: 60px; display: flex; justify-content: flex-end;">
                <div style="text-align: center; width: 250px; font-size: 13px;">
                    <p>Sijunjung, <?= date('d F Y'); ?></p>
                    <p>Mengetahui,</p>
                    <p style="font-weight: bold; margin-bottom: 70px;">Kepala Puskesmas Sijunjung</p>
                    <p>__________________________</p>
                    <p style="color: #333;">NIP. ............................</p>
                </div>
            </div>

        </main>
    </section>

    <script src="../../assets/js/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script>
        const ctxTren = document.getElementById('chartTrenArsip');
        if (ctxTren) {
            new Chart(ctxTren, {
                type: 'line',
                data: {
                    labels: <?= json_encode($tren_label); ?>,
                    datasets: [{
                        label: 'Jumlah Arsip Masuk',
                        data: <?= json_encode($tren_value); ?>,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.15)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        }
    </script>
</body>
</html>