<?php
session_start();
// Proteksi halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";

// Ensure $koneksi is defined
if (!isset($koneksi)) {
    die("Database connection error.");
}

// Ambil ID dari URL
if (!isset($_GET['id'])) {
    header("Location: data_kategori.php");
    exit;
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = mysqli_query($koneksi, "SELECT * FROM kategori WHERE id_kategori = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (mysqli_num_rows($query) < 1) {
    header("Location: data_kategori.php");
    exit;
}

// Proses Update Data
if (isset($_POST['update'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    
    $update = mysqli_query($koneksi, "UPDATE kategori SET nama_kategori = '$nama_kategori' WHERE id_kategori = '$id'");
    
    if ($update) {
        echo "<script>alert('Kategori berhasil diperbarui!'); window.location='data_kategori.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}

$page = 'kategori.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Edit Kategori - SIAPSIJUNJUNG</title>
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
<body>
    <?php include '../partials/sidebar.php'; ?>

    <section id="content">
        <?php include '../partials/navbar.php'; ?>

        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Edit Kategori</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_kategori.php">Data Kategori</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Edit Kategori</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-box">
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Nama Kategori Arsip</label>
                        <input type="text" name="nama_kategori" value="<?= htmlspecialchars($data['nama_kategori']); ?>" required autocomplete="off">
                    </div>
                    <div class="form-action">
                        <button type="submit" name="update" class="btn-save">
                            <i class='bx bxs-save'></i> Simpan Perubahan
                        </button>
                        <a href="data_kategori.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>
</html>