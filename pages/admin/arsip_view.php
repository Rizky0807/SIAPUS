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

// Join tabel untuk informasi detail berkas
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Preview Arsip - SIAPSIJUNJUNG</title>
    <style>
        /* 💡 LOCK LAYOUT SATU LAYAR DESKTOP TOTAL KHAS SIAPUS */
        html, body {
            height: 100vh;
            overflow: hidden !important;
        }

        #content main {
            height: calc(100vh - 56px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 24px;
            box-sizing: border-box;
        }

        .head-title {
            flex-shrink: 0;
            margin-bottom: 15px !important;
        }

        /* 💡 MENGATUR GRID AGAR BERBAGI RUANG SECARA DINAMIS */
        .view-wrapper {
            display: grid;
            grid-template-columns: 2.5fr 1fr;
            gap: 20px;
            flex-grow: 1;
            min-height: 0;
            width: 100%;
        }

        .preview-card {
            background: #444;
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        /* 💡 MEMBENTUK PANEL DETAIL MENJADI WADAH SCROLL MANDIRI */
        .detail-card {
            background: var(--white-card, #fff);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            height: 100%;
            box-sizing: border-box;
            min-height: 0;
        }

        .detail-scroll-area {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 5px;
            margin-top: 10px;
        }

        .info-row {
            margin-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .info-row label {
            display: block;
            font-size: 11px;
            color: var(--dark-grey);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-row p {
            font-size: 14px;
            color: var(--dark);
            margin-top: 4px;
            word-break: break-word;
        }

        /* Modifikasi scrollbar internal panel detail */
        .detail-scroll-area::-webkit-scrollbar {
            width: 4px;
        }
        .detail-scroll-area::-webkit-scrollbar-thumb {
            background: var(--dark-grey);
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <?php include '../partials/sidebar.php'; ?>
    <section id="content">
        <?php include '../partials/navbar.php'; ?>
        <main>
            <!-- HEAD TITLE -->
            <div class="head-title">
                <div class="left">
                    <h1>Pratinjau Dokumen</h1>
                    <p style="color: var(--dark-grey); font-size: 13px; margin-top: 2px;">Kode Arsip: <strong style="color: var(--dark); font-family: monospace; font-size: 14px;"><?= htmlspecialchars($data['kode_arsip']); ?></strong></p>
                </div>
                <a href="arsip_download.php?id=<?= $data['id_arsip']; ?>" class="btn-add" style="background: var(--green); text-decoration: none;">
                    <i class='bx bxs-cloud-download'></i>
                    <span class="text">Unduh Berkas</span>
                </a>
            </div>

            <!-- VIEW GRID WRAPPER -->
            <div class="view-wrapper">
                
                <!-- CONTAINER FILE ARSIP -->
                <div class="preview-card">
                    <?php
                    $file = $data['file_arsip'];
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $path = "../../assets/uploads/arsip/" . $file;

                    if (file_exists($path)) {
                        if ($ext == 'pdf') {
                            echo '<iframe src="' . $path . '#toolbar=0" width="100%" height="100%" style="border:none;"></iframe>';
                        } elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                            echo '<img src="' . $path . '" style="max-width:100%; max-height:100%; object-fit:contain;">';
                        } else {
                            echo '<div style="text-align:center; color:#fff;"><i class="bx bxs-file-blank" style="font-size:80px; color:var(--dark-grey);"></i><p style="margin-top:10px;">Format .' . $ext . ' tidak mendukung preview.</p></div>';
                        }
                    } else {
                        echo "<div style='text-align:center; color:#fff;'><i class='bx bx-error-circle' style='font-size:50px; color:#DB504A;'></i><p style='margin-top:10px;'>Maaf, file fisik tidak ditemukan di server Puskesmas.</p></div>";
                    }
                    ?>
                </div>

                <!-- CONTAINER DETAIL INFORMASI -->
                <div class="detail-card">
                    <h3 style="font-size: 16px; font-weight: 600; color: var(--dark); flex-shrink: 0;"><i class='bx bx-info-circle' style="color: var(--blue);"></i> Detail Arsip</h3>

                    <!-- Areal scroll internal berkas dimulai -->
                    <div class="detail-scroll-area">
                        <div class="info-row">
                            <label>Nama / Judul Dokumen</label>
                            <p style="font-weight: 600;"><?= htmlspecialchars($data['nama_arsip']); ?></p>
                        </div>
                        <div class="info-row">
                            <label>Unit Kerja Pemilik</label>
                            <p><?= htmlspecialchars($data['nama_unit'] ?? 'Global'); ?></p>
                        </div>
                        <div class="info-row">
                            <label>Kategori</label>
                            <p style="margin-top: 6px;"><span class="status completed" style="font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px;"><?= htmlspecialchars($data['nama_kategori']); ?></span></p>
                        </div>
                        <div class="info-row">
                            <label>Waktu Upload</label>
                            <p><?= date('d F Y, H:i', strtotime($data['created_at'])); ?> WIB</p>
                        </div>
                        <div class="info-row" style="border: none; margin-bottom: 0; padding-bottom: 0;">
                            <label>Deskripsi / Catatan</label>
                            <p style="font-style: italic; color: var(--dark-grey); font-size: 13px; line-height: 1.5; margin-top: 5px;">
                                <?= !empty($data['deskripsi']) ? nl2br(htmlspecialchars($data['deskripsi'])) : 'Tidak ada catatan tambahan.'; ?>
                            </p>
                        </div>
                    </div>

                    <!-- Tombol Kembali Terkunci di Paling Bawah Panel -->
                    <div style="margin-top: 20px; flex-shrink: 0;">
                        <a href="data_arsip.php" class="btn-cancel" style="display:block; text-align:center; text-decoration:none; font-weight: 600; padding: 10px;">Kembali ke Daftar</a>
                    </div>
                </div>

            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>

</html>