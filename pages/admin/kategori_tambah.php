<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
/** @var mysqli $koneksi */

if (!isset($koneksi)) {
    die("Koneksi ke database gagal. Periksa konfigurasi koneksi.");
}

if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    $insert = mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    if ($insert) {
        catat_log($koneksi, $_SESSION['id_user'], 'Tambah Kategori', $nama);
        echo "<script>alert('Kategori berhasil ditambah!'); window.location='data_kategori.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Tambah Kategori - SIAPSIJUNJUNG</title>
</head>\

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
                    <h1>Tambah Kategori</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_unit.php">Data Kategori</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Tambah Data</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-box">
                <div class="head" style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <h3 style="color: var(--dark); font-size: 18px;">Form Tambah Kategori Arsip</h3>
                    <p style="font-size: 13px; color: var(--dark-grey);">Masukkan Nama Kategori Arsip</p>
                </div>

                <form action="" method="POST">
                    <div class="form-group">
                        <label>Nama Kategori Arsip</label>
                        <input type="text" name="nama_kategori" placeholder="Contoh: Surat Keputusan" required autocomplete="off">
                    </div>

                    <div class="form-action">
                        <button type="submit" name="simpan" class="btn-save">
                            <i class='bx bxs-save'></i> Simpan Kategori
                        </button>
                        <a href="data_kategori.php" class="btn-cancel">
                            <i class='bx bx-arrow-back'></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </section>

</body>
<script src="../../assets/js/script.js"></script>
</html>