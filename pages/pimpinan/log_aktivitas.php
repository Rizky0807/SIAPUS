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

// 💡 INISIALISASI FILTER MULTI-KRITERIA DARI METHOD GET (BULAN, TAHUN, AKSI, ROLE, UNIT)
$f_bulan = $_GET['filter_bulan'] ?? '';
$f_tahun = $_GET['filter_tahun'] ?? '';
$f_aksi  = $_GET['filter_aksi'] ?? '';
$f_role  = $_GET['filter_role'] ?? '';
$f_unit  = $_GET['filter_unit'] ?? '';

// Ambil list unit kerja secara dinamis dari database untuk dropdown filter pimpinan
$list_unit = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");

// 💡 MIGRASI KUERI GLOBAL SINKRON DENGAN ADMIN
$query_base = "SELECT l.*, u.nama_lengkap, u.role, uk.nama_unit 
               FROM log_aktivitas l 
               JOIN users u ON l.id_user = u.id_user 
               LEFT JOIN unit_kerja uk ON u.id_unit = uk.id_unit 
               WHERE 1=1";

// 💡 1. Jalankan Filter Bulanan (MONTH)
if ($f_bulan != '') {
    $f_bulan_clean = mysqli_real_escape_string($koneksi, $f_bulan);
    $query_base .= " AND MONTH(l.waktu) = '$f_bulan_clean'";
}

// 💡 2. Jalankan Filter Tahunan (YEAR)
if ($f_tahun != '') {
    $f_tahun_clean = mysqli_real_escape_string($koneksi, $f_tahun);
    $query_base .= " AND YEAR(l.waktu) = '$f_tahun_clean'";
}

// 💡 3. Jalankan Filter Jenis Aktivitas (Fokus pada pengolahan arsip/data master)
if ($f_aksi != '') {
    $f_aksi_clean = mysqli_real_escape_string($koneksi, $f_aksi);
    $query_base .= " AND l.aktivitas = '$f_aksi_clean'";
}

// 💡 4. Jalankan Filter Hak Akses (Role)
if ($f_role != '') {
    $f_role_clean = mysqli_real_escape_string($koneksi, $f_role);
    $query_base .= " AND u.role = '$f_role_clean'";
}

// 💡 5. Jalankan Filter Unit Kerja
if ($f_unit != '') {
    $f_unit_clean = mysqli_real_escape_string($koneksi, $f_unit);
    $query_base .= " AND u.id_unit = '$f_unit_clean'";
}

// Urutkan data berdasarkan waktu terbaru (Desc)
$query_log = mysqli_query($koneksi, $query_base . " ORDER BY l.waktu DESC");

// Array Nama Bulan Indonesia untuk metadata & dropdown
$nama_bulan_indo = [
    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
    '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
    '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
];

$page = 'log_aktivitas.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Log Aktivitas - SIAPSIJUNJUNG</title>
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

    .action-box {
        display: flex;
        gap: 12px;
        align-items: center;
    }

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

    .form-group-custom select {
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

    .table-data .order table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-data .order table th {
        position: sticky;
        top: 0;
        background: var(--white-card, #fff);
        z-index: 10;
        text-align: left;
        padding: 12px;
        border-bottom: 2px solid var(--border-color);
    }

    .table-data .order table td {
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .table-data .order::-webkit-scrollbar {
        width: 5px;
    }
    .table-data .order::-webkit-scrollbar-thumb {
        background: var(--dark-grey);
        border-radius: 5px;
    }
</style>

<body>
    <?php include '../partials/sidebar.php'; ?>

    <section id="content">
        <?php include '../partials/navbar.php'; ?>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Log Aktivitas Sistem</h1>
                    <ul class="breadcrumb">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Log Aktivitas</a></li>
                    </ul>
                </div>
            </div>

            <div class="filter-card-custom">
                <form action="" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; width: 100%; align-items: flex-end;">
                    
                    <div class="form-group-custom">
                        <label>Bulan</label>
                        <select name="filter_bulan" onchange="this.form.submit()" style="cursor: pointer;">
                            <option value="">-- Semua Bulan --</option>
                            <?php foreach ($nama_bulan_indo as $key => $val) : ?>
                                <option value="<?= $key; ?>" <?= $f_bulan == $key ? 'selected' : ''; ?>><?= $val; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group-custom">
                        <label>Tahun</label>
                        <select name="filter_tahun" onchange="this.form.submit()" style="cursor: pointer;">
                            <option value="">-- Semua Tahun --</option>
                            <?php 
                            $tahun_sekarang = date('Y');
                            for ($i = $tahun_sekarang; $i >= $tahun_sekarang - 4; $i--) : 
                            ?>
                                <option value="<?= $i; ?>" <?= $f_tahun == $i ? 'selected' : ''; ?>><?= $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group-custom">
                        <label>Jenis Aktivitas</label>
                        <select name="filter_aksi" onchange="this.form.submit()" style="cursor: pointer;">
                            <option value="">-- Semua Aktivitas --</option>
                            <option value="Upload Arsip" <?= $f_aksi == 'Upload Arsip' ? 'selected' : ''; ?>>Upload Arsip</option>
                            <option value="Download Arsip" <?= $f_aksi == 'Download Arsip' ? 'selected' : ''; ?>>Download Arsip</option>
                            <option value="Edit Arsip" <?= $f_aksi == 'Edit Arsip' ? 'selected' : ''; ?>>Edit Arsip</option>
                            <option value="Hapus Arsip" <?= $f_aksi == 'Hapus Arsip' ? 'selected' : ''; ?>>Hapus Arsip</option>
                            <option value="Tambah Unit" <?= $f_aksi == 'Tambah Unit' ? 'selected' : ''; ?>>Tambah Unit</option>
                            <option value="Edit Unit" <?= $f_aksi == 'Edit Unit' ? 'selected' : ''; ?>>Edit Unit</option>
                            <option value="Hapus Unit" <?= $f_aksi == 'Hapus Unit' ? 'selected' : ''; ?>>Hapus Unit</option>
                            <option value="Tambah Pengguna" <?= $f_aksi == 'Tambah Pengguna' ? 'selected' : ''; ?>>Tambah User</option>
                            <option value="Edit Pengguna" <?= $f_aksi == 'Edit Pengguna' ? 'selected' : ''; ?>>Edit User</option>
                            <option value="Hapus Pengguna" <?= $f_aksi == 'Hapus Pengguna' ? 'selected' : ''; ?>>Hapus User</option>
                        </select>
                    </div>

                    <div class="form-group-custom">
                        <label>Hak Akses (Role)</label>
                        <select name="filter_role" onchange="this.form.submit()" style="cursor: pointer;">
                            <option value="">-- Semua Role --</option>
                            <option value="admin" <?= $f_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="petugas" <?= $f_role == 'petugas' ? 'selected' : ''; ?>>Petugas Unit</option>
                            <option value="pimpinan" <?= $f_role == 'pimpinan' ? 'selected' : ''; ?>>Pimpinan</option>
                        </select>
                    </div>

                    <div class="form-group-custom">
                        <label>Unit Kerja</label>
                        <select name="filter_unit" onchange="this.form.submit()" style="cursor: pointer;">
                            <option value="">-- Semua Unit --</option>
                            <?php while($u = mysqli_fetch_assoc($list_unit)) : ?>
                                <option value="<?= $u['id_unit']; ?>" <?= $f_unit == $u['id_unit'] ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($u['nama_unit']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group-custom">
                        <a href="log_aktivitas.php" class="btn-action-custom" style="background: #e2e8f0; color: #334155 !important; border-radius: 8px; height: 35px; padding: 0 15px;">
                            <i class='bx bx-refresh' style="font-size: 18px;"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3 style="color: var(--dark)">Data Log Aktivitas Pengguna Keseluruhan</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th width="50" style="text-align: center;">No</th>
                                <th width="160">Waktu</th>
                                <th>Nama Pengguna</th>
                                <th>Role</th>
                                <th>Unit Kerja</th>
                                <th>Aktivitas</th>
                                <th>Objek Aktivitas</th>
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
                                            <span style="color: var(--dark-grey); font-size: 13px; font-weight: 500;">
                                                <i class='bx bx-time-five' style="vertical-align: middle;"></i> <?= date('d/m/Y | H:i', strtotime($row['waktu'])); ?> WIB
                                            </span>
                                        </td>
                                        <td>
                                            <p style="font-weight: 600; color: var(--dark); margin:0;"><?= htmlspecialchars($row['nama_lengkap']); ?></p>
                                        </td>
                                        <td>
                                            <span class="status <?= $row['role']; ?>"><?= ucfirst($row['role']); ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($row['nama_unit'] ?? '-'); ?></td>
                                        <td>
                                            <span style="font-weight: bold; color: var(--blue);"><?= htmlspecialchars($row['aktivitas']); ?></span>
                                        </td>
                                        <td><?= htmlspecialchars($row['objek_aktivitas']); ?></td>
                                    </tr>
                            <?php
                                endwhile;
                            else :
                            ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px; color: var(--dark-grey);">
                                        Belum ada catatan aktivitas yang sesuai dengan kriteria filter saat ini.
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