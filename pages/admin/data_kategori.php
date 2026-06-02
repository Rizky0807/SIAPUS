<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";
if (!isset($koneksi)) {
    die("Database connection error.");
}

// Proses Hapus Kategori
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $delete = mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = '$id'");
    if ($delete) {
        echo "<script>alert('Kategori berhasil dihapus!'); window.location='data_kategori.php';</script>";
    }
}

$page = 'data_kategori.php';
$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Data Kategori - SIAPSIJUNJUNG</title>
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
    pointer-events: none; /* Link tidak bisa diklik jika hanya teks navigasi */
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
<body>
    <?php include '../partials/sidebar.php'; ?>

    <section id="content">
        <?php include '../partials/navbar.php'; ?>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Data Kategori</h1>
                    <ul class="breadcrumb">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Kategori</a></li>
                    </ul>
                </div>
                <a href="kategori_tambah.php" class="btn-add">
                    <i class='bx bx-plus'></i>
                    <span class="text">Tambah Kategori</span>
                </a>
            </div>

            <div class="table-data">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50px;">No</th>
                                <th>Nama Kategori Arsip</th>
                                <th style="text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while($row = mysqli_fetch_assoc($query_kategori)) : 
                            ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_kategori']); ?></td>
                                <td style="text-align: center;">
                                    <div class="btn-group-action">
                                        <a href="kategori_edit.php?id=<?= $row['id_kategori']; ?>" class="btn-group-item">
                                            <i class='bx bxs-edit' style="color: #3C91E6;"></i>
                                            <span>Edit</span>
                                        </a>
                                        <a href="data_kategori.php?hapus=<?= $row['id_kategori']; ?>" class="btn-group-item btn-delete" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                            <i class='bx bxs-trash' style="color: #fff;"></i>
                                            <span>Hapus</span>
                                        </a>
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