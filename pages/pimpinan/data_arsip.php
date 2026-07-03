<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
if (!isset($koneksi)) {
    die("Database connection error.");
}

$role = $_SESSION['role'];
$id_unit_user = $_SESSION['id_unit'];

// Ambil parameter filter & pencarian
$keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$f_unit = isset($_GET['filter_unit']) ? mysqli_real_escape_string($koneksi, $_GET['filter_unit']) : '';
$f_kat  = isset($_GET['filter_kategori']) ? mysqli_real_escape_string($koneksi, $_GET['filter_kategori']) : '';

// Query Dasar
$query_base = "SELECT a.*, u.nama_unit, k.nama_kategori 
               FROM arsip a 
               LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
               LEFT JOIN kategori k ON a.id_kategori = k.id_kategori WHERE 1=1";

// Batasan Role (Admin dan Pimpinan bisa melihat semua data unit)
if ($role !== 'admin' && $role !== 'pimpinan') {
    $query_base .= " AND a.id_unit = '$id_unit_user'";
}

// Tambahkan Filter Pencarian
if ($keyword != '') {
    $query_base .= " AND (a.nama_arsip LIKE '%$keyword%' OR a.kode_arsip LIKE '%$keyword%')";
}
if ($f_unit != '') {
    $query_base .= " AND a.id_unit = '$f_unit'";
}
if ($f_kat != '') {
    $query_base .= " AND a.id_kategori = '$f_kat'";
}

// Urutkan berdasarkan data terbaru
$query_arsip = mysqli_query($koneksi, $query_base . " ORDER BY a.created_at DESC");

// Data untuk Dropdown Filter
$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");
$kats  = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

$page = 'data_arsip.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Data Arsip - SIAPSIJUNJUNG</title>
    <style>
        /* 💡 KUNCI LAYAR SATU HALAMAN PENUH DESKTOP KHAS SIAPUS */
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

        .filter-container {
            background: var(--white-card, #fff);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            flex-shrink: 0;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--dark-grey);
            text-transform: uppercase;
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--light);
            color: var(--dark);
            outline: none;
            height: 38px;
            box-sizing: border-box;
        }

        /* CONFIG SCROLL LANGSUNG DI WADAH TABEL ASLI */
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

        /* Modifikasi scrollbar halus */
        .table-data .order::-webkit-scrollbar {
            width: 5px;
        }
        .table-data .order::-webkit-scrollbar-thumb {
            background: var(--dark-grey);
            border-radius: 5px;
        }

        /* GRUP BUTTON AKSI OUTLINE MINIMALIS PREMIUM SINKRON */
        .action-flex-group {
            display: flex;
            justify-content: center;
            gap: 6px;
        }

        .btn-action-view {
            background: transparent;
            color: #DB504A;
            border: 1.5px solid #DB504A;
            padding: 6px 10px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .btn-action-view:hover {
            background: #DB504A;
            color: #ffffff !important;
        }

        .btn-action-download {
            background: transparent;
            color: #4cbb17;
            border: 1.5px solid #4cbb17;
            padding: 6px 10px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .btn-action-download:hover {
            background: #4cbb17;
            color: #ffffff !important;
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
                    <h1>Data Arsip Digital</h1>
                </div>
                <!-- Pimpinan tidak memiliki tombol Upload/Tambah data -->
                <?php if ($role !== 'pimpinan' && $role !== 'petugas') : ?>
                    <a href="arsip_tambah.php" class="btn-add" style="text-decoration: none;"><i class='bx bx-cloud-upload'></i><span class="text">Upload Arsip</span></a>
                <?php endif; ?>
            </div>

            <!-- FORM FILTER BAR SINKRON -->
            <form action="" method="GET" class="filter-container" id="filterForm">
                <div class="filter-group" style="flex: 1; min-width: 200px;">
                    <label>Cari Kata Kunci</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <i class='bx bx-search' style="position: absolute; left: 12px; color: var(--dark-grey); font-size: 18px;"></i>
                        <input type="text" name="search" id="searchInput" placeholder="Nama atau Kode..." value="<?= htmlspecialchars($keyword); ?>" autocomplete="off" style="padding-left: 38px; width: 100%;">
                    </div>
                </div>

                <?php if ($role == 'admin' || $role == 'pimpinan') : ?>
                    <div class="filter-group" style="min-width: 180px;">
                        <label>Unit Kerja</label>
                        <select name="filter_unit" onchange="this.form.submit()">
                            <option value="">Semua Unit</option>
                            <?php while ($u = mysqli_fetch_assoc($units)) : ?>
                                <option value="<?= $u['id_unit']; ?>" <?= ($f_unit == $u['id_unit']) ? 'selected' : ''; ?>><?= htmlspecialchars($u['nama_unit']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="filter-group" style="min-width: 180px;">
                    <label>Kategori</label>
                    <select name="filter_kategori" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php while ($k = mysqli_fetch_assoc($kats)) : ?>
                            <option value="<?= $k['id_kategori']; ?>" <?= ($f_kat == $k['id_kategori']) ? 'selected' : ''; ?>><?= htmlspecialchars($k['nama_kategori']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <a href="data_arsip.php" class="btn-cancel" style="padding: 10px 15px; text-decoration: none; border-radius: 8px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; border: 1px solid var(--border-color); background: var(--light); color: var(--dark);">
                    <i class='bx bx-refresh' style="font-size: 18px;"></i> Reset
                </a>
            </form>

            <div class="table-data">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">No</th>
                                <th style="width: 110px;">Kode</th>
                                <th>Nama Arsip</th>
                                <th>Unit</th>
                                <th style="width: 120px;">Tgl Upload</th>
                                <th>Kategori</th>
                                <th style="width: 140px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_arsip) > 0) : ?>
                                <?php $no = 1;
                                while ($row = mysqli_fetch_assoc($query_arsip)) : ?>
                                    <tr>
                                        <td style="text-align: center; color: var(--dark);"><?= $no++; ?></td>
                                        <td><span style="font-family: monospace; font-weight: bold; color: var(--dark);"><?= htmlspecialchars($row['kode_arsip']); ?></span></td>
                                        <td style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_unit'] ?? 'GLOBAL'); ?></td>
                                        <td style="color: var(--dark); font-size: 13px;"><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        <td><span class="status pending" style=" color: var(--dark); font-weight: 600; padding: 4px 10px; border-radius: 20px; font-size: 12px;"><?= htmlspecialchars($row['nama_kategori']); ?></span></td>
                                        <td style="text-align: center;">
                                            <div class="action-flex-group">
                                                <!-- Tombol Preview Ikon PDF Merah Solid Soft Outline -->
                                                <a href="arsip_view.php?id=<?= $row['id_arsip']; ?>" class="btn-action-view" title="Lihat Preview">
                                                    <i class='bx bxs-file-pdf'></i>
                                                </a>
                                                
                                                <!-- Tombol Download Berkas -->
                                                <a href="arsip_download.php?id=<?= $row['id_arsip']; ?>" class="btn-action-download" title="Download Berkas">
                                                    <i class='bx bx-download'></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px 0;">
                                        <i class='bx bx-search-alt' style="font-size: 60px; color: var(--dark-grey); opacity: 0.3;"></i>
                                        <h3 style="margin-top: 10px; color: var(--dark-grey); font-size: 16px;">Arsip Tidak Ditemukan</h3>
                                        <p style="color: var(--dark-grey); font-size: 13px;">Maaf, tidak ada arsip terdaftar dalam kriteria pencarian pimpinan.</p>
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
    <script>
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        let typingTimer;
        const doneTypingInterval = 500; 

        searchInput.addEventListener('keyup', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                filterForm.submit();
            }, doneTypingInterval);
        });

        const val = searchInput.value;
        searchInput.value = '';
        searchInput.focus();
        searchInput.value = val;
    </script>
</body>

</html>