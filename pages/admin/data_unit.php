<?php
session_start();
// Proteksi halaman: Hanya Admin yang boleh masuk
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";

// Proses Hapus Data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $delete = mysqli_query($koneksi, "DELETE FROM unit_kerja WHERE id_unit = '$id'");
    if ($delete) {
        echo "<script>alert('Unit kerja berhasil dihapus!'); window.location='data_unit.php';</script>";
    }
}

$page = 'data_unit.php';
$query_unit = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css"> <title>Data Unit Kerja - SIAPSIJUNJUNG</title>
</head>

<style>
    /* Styling Breadcrumb agar Sejajar */
.breadcrumb {
    display: flex;
    align-items: center;
    grid-gap: 10px; /* Jarak antar elemen */
    margin-top: 10px;
}

.breadcrumb li {
    color: var(--dark);
    list-style: none; /* Menghilangkan titik list */
    display: flex;
    align-items: center;
}

.breadcrumb li a {
    color: var(--dark-grey);
    pointer-events: none;;
    font-size: 14px;
}

.breadcrumb li a.active {
    color: var(--blue); /* Warna khusus untuk halaman aktif */
    font-weight: 600;
}

.breadcrumb li i {
    font-size: 18px;
    color: var(--dark-grey);
}
</style>
</style>
<body>
    <?php include '../partials/sidebar.php'; ?>

    <section id="content">
        <?php include '../partials/navbar.php'; ?>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Data Unit Kerja</h1>
                    <ul class="breadcrumb">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Unit Kerja</a></li>
                    </ul>
                </div>
                <a href="unit_tambah.php" class="btn-add">
                    <i class='bx bx-plus'></i>
                    <span class="text">Tambah Unit</span>
                </a>
            </div>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Daftar Unit Kerja Puskesmas Sijunjung</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Unit Kerja</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($query_unit)) : 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_unit']); ?></td>
                                <td style="text-align: center;">
                                    <div class="btn-group-action">
                                        <a href="unit_edit.php?id=<?= $row['id_unit']; ?>" class="btn-group-item">
                                            <i class='bx bxs-edit' style="color: #3C91E6;"></i>
                                            <span>Edit</span>
                                        </a>
                                        <a href="data_unit.php?hapus=<?= $row['id_unit']; ?>" class="btn-group-item btn-delete" onclick="return confirm('Menghapus unit akan berdampak pada arsip dan user di unit ini. Yakin?')">
                                            <i class='bx bxs-trash' style="color: #fff;"></i>
                                            <span>Hapus</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if(mysqli_num_rows($query_unit) == 0) : ?>
                                <tr><td colspan="3" style="text-align: center;">Belum ada data unit.</td></tr>
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