<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

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

// Batasan Role
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

// Menggunakan kolom tgl_upload sesuai database Anda
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
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Data Arsip - SIAPSIJUNJUNG</title>
    <style>
        .filter-container {
            background: var(--white-card);
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
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
        }

        .filter-group select,
        .filter-group input {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--light);
            color: var(--dark);
            outline: none;
        }

        .btn-filter {
            background: var(--green);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-filter:hover {
            opacity: 0.8;
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
                <?php if ($role !== 'pimpinan') : ?>
                    <a href="arsip_tambah.php" class="btn-add"><i class='bx bx-cloud-upload'></i><span class="text">Upload Arsip</span></a>
                <?php endif; ?>
            </div>

            <form action="" method="GET" class="filter-container" id="filterForm">
                <div class="filter-group">
                    <label>Cari Kata Kunci</label>
                    <input type="text" name="search" id="searchInput" placeholder="Nama atau Kode..." value="<?= $keyword; ?>" autocomplete="off">
                </div>

                <?php if ($role == 'admin' || $role == 'pimpinan') : ?>
                    <div class="filter-group">
                        <label>Unit Kerja</label>
                        <select name="filter_unit" onchange="this.form.submit()">
                            <option value="">Semua Unit</option>
                            <?php while ($u = mysqli_fetch_assoc($units)) : ?>
                                <option value="<?= $u['id_unit']; ?>" <?= ($f_unit == $u['id_unit']) ? 'selected' : ''; ?>><?= $u['nama_unit']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="filter-group">
                    <label>Kategori</label>
                    <select name="filter_kategori" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php while ($k = mysqli_fetch_assoc($kats)) : ?>
                            <option value="<?= $k['id_kategori']; ?>" <?= ($f_kat == $k['id_kategori']) ? 'selected' : ''; ?>><?= $k['nama_kategori']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <a href="data_arsip.php" class="btn-cancel" style="padding: 10px 15px; text-decoration: none; border-radius: 8px;">Reset</a>
            </form>

            <div class="table-data">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Arsip</th>
                                <th>Unit</th>
                                <th>Tgl Upload</th>
                                <th>Kategori</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_arsip) > 0) : ?>
                                <?php $no = 1;
                                while ($row = mysqli_fetch_assoc($query_arsip)) : ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><span style="font-family: monospace; font-weight: bold;"><?= $row['kode_arsip']; ?></span></td>
                                        <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                        <td><?= $row['nama_unit'] ?? 'GLOBAL'; ?></td>
                                        <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        <td><span class="status pending"><?= $row['nama_kategori']; ?></span></td>
                                        <td style="text-align: center;">
                                            <div class="btn-group-action">
                                                <a href="arsip_view.php?id=<?= $row['id_arsip']; ?>" class="btn-group-item" title="Lihat">
                                                    <i class='bx bxs-file-pdf' style="color: #DB504A;"></i>
                                                </a>
                                                <?php if ($role !== 'pimpinan') : ?>
                                                    <a href="arsip_edit.php?id=<?= $row['id_arsip']; ?>" class="btn-group-item">
                                                        <i class='bx bxs-edit' style="color: #3C91E6;"></i>
                                                    </a>
                                                    <a href="arsip_hapus.php?id=<?= $row['id_arsip']; ?>" class="btn-group-item btn-delete" onclick="return confirm('Yakin hapus arsip?')">
                                                        <i class='bx bxs-trash' style="color: #fff;"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 50px 0;">
                                        <i class='bx bx-search-alt' style="font-size: 80px; color: var(--dark-grey); opacity: 0.3;"></i>
                                        <h3 style="margin-top: 15px; color: var(--dark-grey);">Arsip Tidak Ditemukan</h3>
                                        <p style="color: var(--dark-grey); font-size: 14px;">Maaf, kami tidak menemukan arsip dengan kata kunci tersebut.</p>
                                        <a href="data_arsip.php" style="color: var(--green); text-decoration: underline; font-size: 14px; margin-top: 10px; display: inline-block;">
                                            Tampilkan Semua Arsip
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>
    <script>
        // Fungsi untuk submit form otomatis saat ngetik (dengan delay 500ms)
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        let typingTimer;
        const doneTypingInterval = 500; // jeda 0.5 detik

        searchInput.addEventListener('keyup', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                filterForm.submit();
            }, doneTypingInterval);
        });

        // Agar kursor tetap di akhir teks setelah refresh
        const val = searchInput.value;
        searchInput.value = '';
        searchInput.focus();
        searchInput.value = val;
    </script>
    <script src="../../assets/js/script.js"></script>
</body>

</html>