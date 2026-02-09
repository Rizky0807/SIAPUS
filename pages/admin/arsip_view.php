<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Join tabel untuk informasi detail
$query = mysqli_query($koneksi, "SELECT a.*, u.nama_unit, k.nama_kategori 
                                FROM arsip a 
                                LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
                                LEFT JOIN kategori k ON a.id_kategori = k.id_kategori 
                                WHERE a.id_arsip = '$id'");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Arsip tidak ditemukan!'); window.location='data_arsip.php';</script>";
    exit;
}

$page = 'data_arsip.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Preview Arsip - <?= $data['nama_arsip']; ?></title>
    <style>
        .preview-container { display: flex; gap: 20px; flex-wrap: wrap; }
        .pdf-viewer { flex: 2; min-width: 600px; height: 80vh; background: #eee; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .doc-info { flex: 1; min-width: 300px; }
        iframe { width: 100%; height: 100%; border: none; }
        img { max-width: 100%; max-height: 100%; object-fit: contain; }
    </style>
</head>
<body>
    <?php include '../partials/sidebar.php'; ?>
    <section id="content">
        <?php include '../partials/navbar.php'; ?>
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Preview Dokumen</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_arsip.php">Data Arsip</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#"><?= $data['kode_arsip']; ?></a></li>
                    </ul>
                </div>
                <a href="arsip_download.php?id=<?= $data['id_arsip']; ?>" class="btn-download">
                    <i class='bx bxs-cloud-download'></i>
                    <span class="text">Download Berkas</span>
                </a>
            </div>

            <div class="preview-container">
                <div class="pdf-viewer">
                    <?php 
                    $ext = strtolower(pathinfo($data['file_arsip'], PATHINFO_EXTENSION));
                    $file_path = "../../assets/uploads/arsip/" . $data['file_arsip'];

                    if ($ext == 'pdf') : ?>
                        <iframe src="<?= $file_path; ?>#toolbar=0"></iframe>
                    <?php elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) : ?>
                        <img src="<?= $file_path; ?>" alt="Preview Gambar">
                    <?php else : ?>
                        <div style="text-align: center;">
                            <i class='bx bx-file' style="font-size: 100px; color: var(--dark-grey);"></i>
                            <p style="margin-top: 15px;">File <strong>.<?= $ext; ?></strong> tidak mendukung preview langsung.</p>
                            <a href="arsip_download.php?id=<?= $data['id_arsip']; ?>" class="btn-save" style="display: inline-block; margin-top: 10px; padding: 10px 20px;">
                                Klik untuk Unduh
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="doc-info">
                    <div class="table-data">
                        <div class="order">
                            <div class="head"><h3>Detail Informasi</h3></div>
                            <table style="width: 100%;">
                                <tr><td><strong>Kode</strong></td><td>: <?= $data['kode_arsip']; ?></td></tr>
                                <tr><td><strong>Judul</strong></td><td>: <?= $data['nama_arsip']; ?></td></tr>
                                <tr><td><strong>Unit</strong></td><td>: <?= $data['nama_unit'] ?? 'Global'; ?></td></tr>
                                <tr><td><strong>Kategori</strong></td><td>: <?= $data['nama_kategori']; ?></td></tr>
                                <tr><td><strong>Tgl Upload</strong></td><td>: <?= date('d M Y', strtotime($data['created_at'])); ?></td></tr>
                            </table>
                            <div style="margin-top: 20px;">
                                <strong>Deskripsi:</strong>
                                <p style="font-size: 14px; color: var(--dark-grey); margin-top: 5px;">
                                    <?= !empty($data['deskripsi']) ? $data['deskripsi'] : 'Tidak ada deskripsi.'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>
</body>
</html>