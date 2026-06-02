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

// Filter & Search Logic
$keyword = $_GET['search'] ?? '';
$f_unit  = $_GET['filter_unit'] ?? '';
$f_kat   = $_GET['filter_kategori'] ?? '';

// Query Dasar
$query_base = "SELECT a.*, u.nama_unit, k.nama_kategori 
               FROM arsip a 
               LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
               LEFT JOIN kategori k ON a.id_kategori = k.id_kategori WHERE 1=1";

// PROTEKSI DATA: Petugas hanya lihat unitnya sendiri
if ($role == 'petugas') {
    $query_base .= " AND a.id_unit = '$id_unit_user'";
}

// Fitur Pencarian Kata Kunci
if ($keyword != '') {
    $query_base .= " AND (a.nama_arsip LIKE '%$keyword%' OR a.kode_arsip LIKE '%$keyword%')";
}

// Fitur Dropdown Unit (Hanya Admin / Pimpinan)
if ($f_unit != '' && ($role == 'admin' || $role == 'pimpinan')) {
    $query_base .= " AND a.id_unit = '$f_unit'";
}

// Fitur Dropdown Kategori
if ($f_kat != '') {
    $query_base .= " AND a.id_kategori = '$f_kat'";
}

$query_arsip = mysqli_query($koneksi, $query_base . " ORDER BY a.created_at DESC");

// Ambil data untuk dropdown filter
$kats = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");

$page = 'data_arsip.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Data Arsip - SIAPSIJUNJUNG</title>
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
                <?php if ($role != 'pimpinan') : ?>
                    <a href="arsip_tambah.php" class="btn-add"><i class='bx bx-cloud-upload'></i><span class="text">Upload Arsip</span></a>
                <?php endif; ?>
            </div>

            <form action="" method="GET" id="filterForm" style="background: var(--light); padding: 15px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">

                <div style="display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 200px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--dark-grey);">CARI ARSIP</label>
                    <div style="position: relative; display: flex; align-items: center;">
                        <i class='bx bx-search' style="position: absolute; left: 12px; color: var(--dark-grey); font-size: 18px;"></i>
                        <input type="text" name="search" id="searchInput" placeholder="Nama atau kode arsip..." value="<?= htmlspecialchars($keyword); ?>" autocomplete="off" style="padding: 10px 10px 10px 38px; border-radius: 8px; border: 1px solid #ddd; width: 100%; font-size: 14px; outline: none;">
                    </div>
                </div>

                <?php if ($role == 'admin' || $role == 'pimpinan') : ?>
                    <div style="display: flex; flex-direction: column; gap: 5px; min-width: 180px;">
                        <label style="font-size: 12px; font-weight: 600; color: var(--dark-grey);">UNIT KERJA</label>
                        <select name="filter_unit" onchange="this.form.submit()" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; font-size: 14px; background: #fff; cursor: pointer;">
                            <option value="">Semua Unit</option>
                            <?php while ($u = mysqli_fetch_assoc($units)) : ?>
                                <option value="<?= $u['id_unit']; ?>" <?= ($f_unit == $u['id_unit']) ? 'selected' : ''; ?>><?= $u['nama_unit']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div style="display: flex; flex-direction: column; gap: 5px; min-width: 180px;">
                    <label style="font-size: 12px; font-weight: 600; color: var(--dark-grey);">KATEGORI</label>
                    <select name="filter_kategori" onchange="this.form.submit()" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; font-size: 14px; background: #fff; cursor: pointer;">
                        <option value="">Semua Kategori</option>
                        <?php while ($k = mysqli_fetch_assoc($kats)) : ?>
                            <option value="<?= $k['id_kategori']; ?>" <?= ($f_kat == $k['id_kategori']) ? 'selected' : ''; ?>><?= $k['nama_kategori']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <a href="data_arsip.php" style="padding: 11px 20px; text-decoration: none; border-radius: 8px; background: #eee; color: #333; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px;">
                    <i class='bx bx-refresh' style="font-size: 18px;"></i> Reset
                </a>
            </form>

            <div class="table-data">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th width="60">No</th>
                                <th>Kode</th>
                                <th>Nama Arsip</th>

                                <?php if ($role == 'petugas') : ?>
                                    <th>Kategori</th> <?php else : ?>
                                    <th>Unit Asal</th>
                                    <th>Kategori</th>
                                <?php endif; ?>

                                <th>Tgl Upload</th>
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

                                        <?php if ($role == 'petugas') : ?>
                                            <td><span class="status completed" style="background: #e1f5fe; color: #039be5; font-weight: 600;"><?= $row['nama_kategori'] ?? 'Belum Dikategorikan'; ?></span></td>
                                        <?php else : ?>
                                            <td><?= $row['nama_unit'] ?? 'GLOBAL'; ?></td>
                                            <td><span class="status completed" style="background: #e1f5fe; color: #039be5; font-weight: 600;"><?= $row['nama_kategori'] ?? 'Belum Dikategorikan'; ?></span></td>
                                        <?php endif; ?>

                                        <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        <td style="text-align: center;">
                                            <div class="btn-group-action" style="display: flex; gap: 15px; justify-content: center;">
                                                <a href="arsip_view.php?id=<?= $row['id_arsip']; ?>" title="Lihat Preview & Download">
                                                    <i class='bx bx-show' style="color: #3C91E6; font-size: 22px;"></i>
                                                </a>

                                                <?php if ($role == 'admin') : ?>
                                                    <a href="arsip_edit.php?id=<?= $row['id_arsip']; ?>" title="Edit">
                                                        <i class='bx bxs-edit' style="color: #FFCE26; font-size: 22px;"></i>
                                                    </a>
                                                    <a href="arsip_hapus.php?id=<?= $row['id_arsip']; ?>" onclick="return confirm('Yakin hapus arsip ini?')" title="Hapus">
                                                        <i class='bx bxs-trash' style="color: #DB504A; font-size: 22px;"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: var(--dark-grey);">Data arsip tidak ditemukan.</td>
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

        searchInput.addEventListener('keyup', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                filterForm.submit();
            }, 600);
        });

        const val = searchInput.value;
        searchInput.value = '';
        searchInput.focus();
        searchInput.value = val;
    </script>
</body>

</html>