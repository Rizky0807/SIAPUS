<?php
session_start();
// Proteksi halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";

// Ambil ID dari URL
$id = $_GET['id'];
$query = mysqli_query($koneksi, "SELECT * FROM unit_kerja WHERE id_unit = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika ID tidak ditemukan
if (mysqli_num_rows($query) < 1) {
    header("Location: data_unit.php");
    exit;
}

// Proses Update Data
if (isset($_POST['update'])) {
    $nama_unit = mysqli_real_escape_string($koneksi, $_POST['nama_unit']);
    
    $update = mysqli_query($koneksi, "UPDATE unit_kerja SET nama_unit = '$nama_unit' WHERE id_unit = '$id'");
    
    if ($update) {
        echo "<script>alert('Data unit berhasil diperbarui!'); window.location='data_unit.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui data.');</script>";
    }
}

$page = 'data_unit.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Edit Unit Kerja - SIAPSIJUNJUNG</title>
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
                    <h1>Edit Unit Kerja</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_unit.php">Unit Kerja</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Edit Data</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-box">
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Nama Unit Kerja</label>
                        <input type="text" name="nama_unit" value="<?= htmlspecialchars($data['nama_unit']); ?>" required>
                    </div>
                    <div class="form-action">
                        <button type="submit" name="update" class="btn-save">
                            <i class='bx bxs-save'></i> Simpan Perubahan
                        </button>
                        <a href="data_unit.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>
</html>