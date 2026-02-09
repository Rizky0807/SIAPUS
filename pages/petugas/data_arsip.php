<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

$role = $_SESSION['role'];
$id_unit_user = $_SESSION['id_unit'];

// Filter & Search Logic
$keyword = $_GET['search'] ?? '';
$f_unit  = $_GET['filter_unit'] ?? '';

// Query Dasar
$query_base = "SELECT a.*, u.nama_unit, k.nama_kategori 
               FROM arsip a 
               LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
               LEFT JOIN kategori k ON a.id_kategori = k.id_kategori WHERE 1=1";

// PROTEKSI DATA: Petugas hanya lihat unitnya sendiri
if ($role == 'petugas') {
    $query_base .= " AND a.id_unit = '$id_unit_user'";
}

if ($keyword != '') {
    $query_base .= " AND (a.nama_arsip LIKE '%$keyword%' OR a.kode_arsip LIKE '%$keyword%')";
}
if ($f_unit != '' && ($role == 'admin' || $role == 'pimpinan')) {
    $query_base .= " AND a.id_unit = '$f_unit'";
}

$query_arsip = mysqli_query($koneksi, $query_base . " ORDER BY a.created_at DESC");
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
                <?php if ($role !== 'pimpinan') : ?>
                    <a href="arsip_tambah.php" class="btn-add"><i class='bx bx-cloud-upload'></i><span class="text">Upload Arsip</span></a>
                <?php endif; ?>
            </div>

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
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1;
                            while ($row = mysqli_fetch_assoc($query_arsip)) : ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><span style="font-family: monospace; font-weight: bold;"><?= $row['kode_arsip']; ?></span></td>
                                    <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                    <td><?= $row['nama_unit'] ?? 'GLOBAL'; ?></td>
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
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>

</html>