<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pimpinan') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

$id_unit = $_SESSION['id_unit'];
$nama_user = $_SESSION['nama'];


// 1. Hitung total arsip di unit ini saja
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM arsip WHERE id_unit = '$id_unit'");
$total_arsip = mysqli_fetch_assoc($q_total)['total'];

// 2. Hitung jumlah kategori yang digunakan oleh unit ini
$q_kat = mysqli_query($koneksi, "SELECT COUNT(DISTINCT id_kategori) as jml_kat FROM arsip WHERE id_unit = '$id_unit'");
$total_kat = mysqli_fetch_assoc($q_kat)['jml_kat'];

// 3. Ambil 5 Arsip terbaru dari unit ini
$arsip_terbaru = mysqli_query($koneksi, "SELECT a.*, k.nama_kategori 
                                         FROM arsip a 
                                         JOIN kategori k ON a.id_kategori = k.id_kategori 
                                         WHERE a.id_unit = '$id_unit' 
                                         ORDER BY a.created_at DESC LIMIT 5");

$page = 'dashboard.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Dashboard Petugas - SIAPSIJUNJUNG</title>
</head>
<body>
    <?php include '../partials/sidebar.php'; ?>
    <section id="content">
        <?php include '../partials/navbar.php'; ?>
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard Petugas</h1>
                    <p>Selamat Datang, <strong><?= $nama_user; ?></strong> (Unit: <?= $_SESSION['nama_unit'] ?? 'Unit Kerja'; ?>)</p>
                </div>
            </div>

            <ul class="box-info">
                <li>
                    <i class='bx bxs-file-pdf'></i>
                    <span class="text">
                        <h3><?= $total_arsip; ?></h3>
                        <p>Total Arsip Unit Anda</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-category'></i>
                    <span class="text">
                        <h3><?= $total_kat; ?></h3>
                        <p>Kategori Terpakai</p>
                    </span>
                </li>
            </ul>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Arsip Terbaru Unit Anda</h3>
                        <a href="../admin/data_arsip.php" style="font-size: 12px; color: var(--blue);">Lihat Semua</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Arsip</th>
                                <th>Kategori</th>
                                <th>Tanggal Upload</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($arsip_terbaru)) : ?>
                            <tr>
                                <td><span style="font-family: monospace; font-weight: bold;"><?= $row['kode_arsip']; ?></span></td>
                                <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                <td><span class="status completed"><?= $row['nama_kategori']; ?></span></td>
                                <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
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