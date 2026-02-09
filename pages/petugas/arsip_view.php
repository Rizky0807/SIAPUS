<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data detail arsip
$query = mysqli_query($koneksi, "SELECT a.*, u.nama_unit, k.nama_kategori 
                                FROM arsip a 
                                LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
                                LEFT JOIN kategori k ON a.id_kategori = k.id_kategori 
                                WHERE a.id_arsip = '$id'");
$data = mysqli_fetch_assoc($query);

// PROTEKSI: Jika petugas mencoba akses arsip unit lain via URL
if ($_SESSION['role'] == 'petugas' && $data['id_unit'] != $_SESSION['id_unit']) {
    echo "<script>alert('Akses ditolak! Ini bukan arsip unit Anda.'); window.location='data_arsip.php';</script>";
    exit;
}

if (!$data) {
    header("Location: data_arsip.php");
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
    <title>View Arsip - <?= $data['nama_arsip']; ?></title>
    <style>
        .view-container { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-top: 20px; }
        .preview-box { background: #333; border-radius: 12px; height: 80vh; overflow: hidden; display: flex; align-items: center; justify-content: center; color: #fff; }
        .detail-box { background: var(--light); padding: 20px; border-radius: 12px; height: fit-content; }
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
                    <h1>Detail & Preview Arsip</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_arsip.php">Data Arsip</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li class="active"><?= $data['kode_arsip']; ?></li>
                    </ul>
                </div>
                <a href="arsip_download.php?id=<?= $data['id_arsip']; ?>" class="btn-download" style="background: var(--blue); color: #fff; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
                    <i class='bx bxs-cloud-download'></i>
                    <span class="text">Download Berkas</span>
                </a>
            </div>

            <div class="view-container">
                <div class="preview-box">
                    <?php 
                    $file = $data['file_arsip'];
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $path = "../../assets/uploads/arsip/" . $file;

                    if (file_exists($path)) {
                        if ($ext == 'pdf') {
                            echo '<iframe src="'.$path.'#toolbar=0"></iframe>';
                        } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            echo '<img src="'.$path.'" alt="Preview Gambar">';
                        } else {
                            echo '<div style="text-align:center;"><i class="bx bx-file" style="font-size:100px;"></i><p>Preview tidak tersedia untuk format .'.$ext.'</p></div>';
                        }
                    } else {
                        echo "File fisik tidak ditemukan di server.";
                    }
                    ?>
                </div>

                <div class="detail-box">
                    <h3>Informasi Dokumen</h3>
                    <hr style="margin: 15px 0; opacity: 0.2;">
                    <table style="width: 100%; font-size: 14px; border-spacing: 0 10px;">
                        <tr><td width="100" style="color: var(--dark-grey);">Kode</td><td>: <b><?= $data['kode_arsip']; ?></b></td></tr>
                        <tr><td style="color: var(--dark-grey);">Judul</td><td>: <?= $data['nama_arsip']; ?></td></tr>
                        <tr><td style="color: var(--dark-grey);">Kategori</td><td>: <span class="status completed" style="padding: 2px 10px;"><?= $data['nama_kategori']; ?></span></td></tr>
                        <tr><td style="color: var(--dark-grey);">Unit Kerja</td><td>: <?= $data['nama_unit']; ?></td></tr>
                        <tr><td style="color: var(--dark-grey);">Tgl Upload</td><td>: <?= date('d M Y', strtotime($data['created_at'])); ?></td></tr>
                    </table>
                    <div style="margin-top: 20px;">
                        <p style="color: var(--dark-grey); font-weight: 600;">Deskripsi:</p>
                        <p style="font-size: 13px; line-height: 1.6; margin-top: 5px;"><?= !empty($data['deskripsi']) ? $data['deskripsi'] : '<i>Tidak ada deskripsi tambahan.</i>'; ?></p>
                    </div>
                </div>
            </div>
        </main>
    </section>
</body>
</html>