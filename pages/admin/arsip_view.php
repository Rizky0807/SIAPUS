<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

if (!isset($koneksi)) {
    die("Koneksi ke database gagal. Silakan periksa konfigurasi koneksi.");
}

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
    <title>Preview Arsip - SIAPSIJUNJUNG</title>
    <style>
        .view-wrapper { display: grid; grid-template-columns: 2.5fr 1fr; gap: 20px; margin-top: 20px; }
        .preview-card { background: #444; border-radius: 12px; height: 82vh; overflow: hidden; display: flex; align-items: center; justify-content: center; }
        .detail-card { background: var(--light); padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .info-row { margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .info-row label { display: block; font-size: 12px; color: var(--dark-grey); font-weight: 600; text-transform: uppercase; }
        .info-row p { font-size: 15px; color: var(--dark); margin-top: 3px; }
    </style>
</head>
<body>
    <?php include '../partials/sidebar.php'; ?>
    <section id="content">
        <?php include '../partials/navbar.php'; ?>
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Pratinjau Dokumen</h1>
                    <p style="color: var(--dark);">Kode Arsip: <strong><?= $data['kode_arsip']; ?></strong></p>
                </div>
                <a href="arsip_download.php?id=<?= $data['id_arsip']; ?>" class="btn-add" style="background: var(--green);">
                    <i class='bx bxs-cloud-download'></i>
                    <span class="text">Unduh Berkas</span>
                </a>
            </div>

            <div class="view-wrapper">
                <div class="preview-card">
                    <?php 
                    $file = $data['file_arsip'];
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $path = "../../assets/uploads/arsip/" . $file;

                    if (file_exists($path)) {
                        if ($ext == 'pdf') {
                            echo '<iframe src="'.$path.'#toolbar=0" width="100%" height="100%"></iframe>';
                        } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            echo '<img src="'.$path.'" style="max-width:100%; max-height:100%; object-fit:contain;">';
                        } else {
                            echo '<div style="text-align:center;"><i class="bx bxs-file-blank" style="font-size:80px;"></i><p>Format .'.$ext.' tidak mendukung preview.</p></div>';
                        }
                    } else {
                        echo "<p>Maaf, file fisik tidak ditemukan di server.</p>";
                    }
                    ?>
                </div>

                <div class="detail-card">
                    <h3 style="margin-bottom: 20px; color: var(--dark-grey);"><i class='bx bx-info-circle'></i> Detail Arsip</h3>
                    
                    <div class="info-row">
                        <label>Nama / Judul Dokumen</label>
                        <p><?= htmlspecialchars($data['nama_arsip']); ?></p>
                    </div>
                    <div class="info-row">
                        <label>Unit Kerja Pemilik</label>
                        <p><?= $data['nama_unit'] ?? 'Global'; ?></p>
                    </div>
                    <div class="info-row">
                        <label>Kategori</label>
                        <p><span class="status completed"><?= $data['nama_kategori']; ?></span></p>
                    </div>
                    <div class="info-row">
                        <label>Tanggal Masuk Sistem</label>
                        <p><?= date('d F Y, H:i', strtotime($data['created_at'])); ?> WIB</p>
                    </div>
                    <div class="info-row" style="border: none;">
                        <label>Deskripsi / Catatan</label>
                        <p style="font-style: italic; color: #666; font-size: 13px;">
                            <?= !empty($data['deskripsi']) ? nl2br(htmlspecialchars($data['deskripsi'])) : 'Tidak ada catatan tambahan.'; ?>
                        </p>
                    </div>

                    <div style="margin-top: 30px;">
                        <a href="data_arsip.php" class="btn-cancel" style="display:block; text-align:center; text-decoration:none;">Kembali ke Daftar</a>
                    </div>
                </div>
            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>
</html>