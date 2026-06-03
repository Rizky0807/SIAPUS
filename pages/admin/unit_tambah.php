<?php
session_start();
// Proteksi halaman: Hanya Admin yang boleh masuk
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";

// Ensure $koneksi is defined
if (!isset($koneksi)) {
    die("Database connection error.");
}

// Proses Simpan Data
if (isset($_POST['simpan'])) {
    $nama_unit = mysqli_real_escape_string($koneksi, $_POST['nama_unit']);
    
    // Validasi agar tidak ada nama unit ganda
    $cek = mysqli_query($koneksi, "SELECT * FROM unit_kerja WHERE nama_unit = '$nama_unit'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Nama unit tersebut sudah ada!');</script>";
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO unit_kerja (nama_unit) VALUES ('$nama_unit')");
        if ($insert) {
            echo "<script>alert('Unit kerja berhasil ditambahkan!'); window.location='data_unit.php';</script>";
        }
    }
}

$page = 'data_unit.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css"> <title>Tambah Unit Kerja - SIAPSIJUNJUNG</title>
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
                    <h1>Tambah Unit Kerja</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_unit.php">Data Unit Kerja</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Tambah Data</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-box">
                <div class="head" style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <h3 style="color: var(--dark); font-size: 18px;">Form Input Unit Kerja</h3>
                    <p style="font-size: 13px; color: var(--dark-grey);">Masukkan nama unit kerja.</p>
                </div>

                <form action="" method="POST">
                    <div class="form-group">
                        <label>Nama Unit Kerja</label>
                        <input type="text" name="nama_unit" placeholder="Contoh: Klaster 2 (Ibu dan Anak)" required autocomplete="off">
                    </div>

                    <div class="form-action">
                        <button type="submit" name="simpan" class="btn-save">
                            <i class='bx bxs-save'></i> Simpan Unit
                        </button>
                        <a href="data_unit.php" class="btn-cancel">
                            <i class='bx bx-arrow-back'></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </section>

    <script src="../../assets/js/script.js"></script>
</body>
</html>