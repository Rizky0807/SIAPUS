<?php
session_start();
// Kunci akses: Pastikan hanya Pimpinan yang bisa membuka halaman ini
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pimpinan') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

$role = $_SESSION['role'];
$nama_pimpinan = $_SESSION['nama'];
$id_unit_user = $_SESSION['id_unit'];

// 1. Inisialisasi Filter
$f_unit = $_GET['filter_unit'] ?? '';
$tgl_awal = $_GET['tgl_awal'] ?? '';
$tgl_akhir = $_GET['tgl_akhir'] ?? '';

// 2. Query untuk Rekap Per Unit (Mengikuti Patokan Versi Admin Mas)
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

// Logika pembatasan unit kerja jika bukan admin/pimpinan (mengikuti patokan admin)
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
$page = 'laporan_arsip.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Laporan Lengkap - Pimpinan - SIAPSIJUNJUNG</title>
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

        @media print {
            #sidebar,
            nav,
            #navbar,
            header,
            .filter-card,
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
            <!-- HEADER NAVIGASI SEJAJAR -->
            <div class="head-title">
                <div class="left">
                    <h1>Laporan Arsip</h1>
                    <ul class="breadcrumb">
                        <li><a href="dashboard.php">Pimpinan</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Rekapitulasi Sistem</a></li>
                    </ul>
                </div>
                <button onclick="window.print()" class="btn-action-custom btn-print" style="background: var(--blue);">
                    <i class='bx bxs-printer' style="font-size: 18px;"></i> <span class="text">Cetak Laporan</span>
                </button>
            </div>

            <!-- KOP SURAT RESMI (Hanya Muncul Saat Dicetak) -->
            <div class="print-only" style="text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 30px;">
                <h2 style="text-transform: uppercase; margin: 0; font-size: 18px;">Pemerintah Kabupaten Sijunjung</h2>
                <h1 style="text-transform: uppercase; margin: 5px 0; font-size: 22px;">Puskesmas Sijunjung</h1>
                <p style="margin: 0; font-size: 12px; font-style: italic;">Jl. Jenderal Sudirman No. 123, Sijunjung, Sumatera Barat | Email: puskesmassijunjung@gmail.com</p>
            </div>

            <div class="print-only" style="margin-bottom: 20px;">
                <p style="font-size: 12px; margin: 5px 0 0 0;">Dicetak oleh: <?= htmlspecialchars($nama_pimpinan); ?> (<?= ucfirst($role); ?>)</p>
                <p style="font-size: 12px; margin: 2px 0 0 0;">Tanggal: <?= date('d/m/Y H:i'); ?> WIB</p>
            </div>

            <!-- CONTAINER FILTRASI DATA (Style Disamakan Sesuai Canva/Saran Visual) -->
            <div class="filter-card">
                <form action="" method="GET">
                    <div class="filter-row">
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
                            <label>Periode Mulai</label>
                            <input type="date" name="tgl_awal" value="<?= $tgl_awal; ?>" onchange="this.form.submit()" class="form-control-custom" style="cursor: pointer;">
                        </div>
                        <div class="form-group">
                            <label>Periode Akhir</label>
                            <input type="date" name="tgl_akhir" value="<?= $tgl_akhir; ?>" onchange="this.form.submit()" class="form-control-custom" style="cursor: pointer;">
                        </div>
                        <a href="laporan_arsip.php" class="btn-action-custom" style="background: #eee; color: #333 !important; font-weight: 600;">
                            <i class='bx bx-refresh' style="font-size: 18px;"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- RINGKASAN DATA KOTAK STATISTIK (Mengikuti Patokan Admin) -->
            <div class="report-stats">
                <div class="stat-card">
                    <p>Total Arsip Ditemukan</p>
                    <h3 style="color: var(--dark);"><?= $total_arsip; ?> <span style="font-size: 14px; font-weight: normal; color: var(--dark-grey);">Berkas</span></h3>
                </div>
                <?php if ($f_unit == '') : ?>
                    <div class="stat-card" style="border-left-color: var(--orange);">
                        <p>Unit Aktif</p>
                        <h3 style="color: var(--dark);"><?= mysqli_num_rows($rekap_data); ?> <span style="font-size: 14px; font-weight: normal; color: var(--dark-grey);">Unit</span></h3>
                    </div>
                <?php endif; ?>
            </div>

            <!-- TAMPILAN TABEL PERSENTASE PER UNIT (100% Mengikuti Patokan Admin Mas) -->
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

            <!-- DETAIL DAFTAR ARSIP (Sama Persis Kolomnya Dengan Patokan Admin) -->
            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3 style="color: var(--dark);">Detail Daftar Arsip</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Kode</th>
                                <th>Judul Arsip</th>
                                <th>Unit</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_arsip > 0) : $no = 1; ?>
                                <?php while ($row = mysqli_fetch_assoc($query_laporan)) : ?>
                                    <!-- Dialihkan ke preview pimpinan arsip_view.php tanpa merusak data -->
                                    <tr onclick="window.location='../admin/arsip_view.php?id=<?= $row['id_arsip']; ?>'" style="cursor: pointer;" title="Klik untuk detail">
                                        <td><?= $no++; ?></td>
                                        <td><span style="font-family: monospace; font-weight: bold;"><?= $row['kode_arsip']; ?></span></td>
                                        <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                        <td><?= $row['nama_unit'] ?? 'GLOBAL'; ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 30px; color: var(--dark-grey);">Tidak ada data pada periode ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <!-- PANEL TANDA TANGAN -->
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