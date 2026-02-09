<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pimpinan') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

// 1. Hitung total semua arsip
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM arsip");
$total_arsip = mysqli_fetch_assoc($q_total)['total'];

// 2. Hitung total unit kerja
$q_unit = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM unit_kerja");
$total_unit = mysqli_fetch_assoc($q_unit)['total'];

// 3. Data Rekap per Unit untuk Pimpinan
$rekap_unit = mysqli_query($koneksi, "SELECT uk.nama_unit, COUNT(a.id_arsip) as jml 
                                      FROM unit_kerja uk 
                                      LEFT JOIN arsip a ON uk.id_unit = a.id_unit 
                                      GROUP BY uk.id_unit 
                                      ORDER BY jml DESC");

$page = 'dashboard.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Dashboard Pimpinan - SIAPSIJUNJUNG</title>
</head>
<body>
    <?php include '../partials/sidebar.php'; ?>
    <section id="content">
        <?php include '../partials/navbar.php'; ?>
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard Pimpinan</h1>
                    <p>Ringkasan Data Arsip Digital Puskesmas Sijunjung</p>
                </div>
            </div>

            <ul class="box-info">
                <li>
                    <i class='bx bxs-file-archive'></i>
                    <span class="text">
                        <h3><?= $total_arsip; ?></h3>
                        <p>Total Seluruh Arsip</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-buildings'></i>
                    <span class="text">
                        <h3><?= $total_unit; ?></h3>
                        <p>Total Unit Kerja</p>
                    </span>
                </li>
            </ul>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Statistik Arsip Per Unit</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Unit Kerja</th>
                                <th>Jumlah Dokumen</th>
                                <th>Status Keaktifan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($rekap_unit)) : ?>
                            <tr>
                                <td><?= $row['nama_unit']; ?></td>
                                <td><strong><?= $row['jml']; ?></strong> Berkas</td>
                                <td>
                                    <?php if($row['jml'] > 0) : ?>
                                        <span class="status completed">Aktif</span>
                                    <?php else : ?>
                                        <span class="status pending">Kosong</span>
                                    <?php endif; ?>
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