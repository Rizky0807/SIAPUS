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
    // 💡 AMBIL DATA DARI INPUTAN BARU
    $kode_unit = mysqli_real_escape_string($koneksi, trim($_POST['kode_unit']));
    $nama_unit = mysqli_real_escape_string($koneksi, trim($_POST['nama_unit']));
    $penanggung_jawab = mysqli_real_escape_string($koneksi, trim($_POST['penanggung_jawab']));
    
    // Validasi agar tidak ada nama unit ganda
    $cek = mysqli_query($koneksi, "SELECT * FROM unit_kerja WHERE nama_unit = '$nama_unit'");
    // Validasi agar tidak ada kode unit ganda
    $cek_kode = mysqli_query($koneksi, "SELECT * FROM unit_kerja WHERE kode_unit = '$kode_unit'");

    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Nama unit tersebut sudah ada!');</script>";
    } elseif (mysqli_num_rows($cek_kode) > 0) {
        echo "<script>alert('Kode unit tersebut sudah ada!');</script>";
    } else {
        // 💡 INSERT TIGA KOLOM SEKALIGUS KE TABEL unit_kerja
        $insert = mysqli_query($koneksi, "INSERT INTO unit_kerja (kode_unit, nama_unit, penanggung_jawab) VALUES ('$kode_unit', '$nama_unit', '$penanggung_jawab')");
        if ($insert) {
            echo "<script>alert('Unit kerja berhasil ditambahkan!'); window.location='data_unit.php';</script>";
            exit;
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
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Kode Unit</label>
                        <input type="text" name="kode_unit" placeholder="Contoh: UK-KIA233" required autocomplete="off" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Nama Unit Kerja</label>
                        <input type="text" name="nama_unit" placeholder="Contoh: Pelayanan Rawat Inap" required autocomplete="off" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px;">Penanggung Jawab (Kepala Unit)</label>
                        <input type="text" name="penanggung_jawab" placeholder="Contoh: dr. Ahmad Fauzi" required autocomplete="off" style="width: 100%; padding: 8px; box-sizing: border-box;">
                    </div>

                    <div class="form-action" style="margin-top: 20px;">
                        <button type="submit" name="simpan" class="btn-save">
                            <i class='bx bxs-save'></i> Simpan Unit
                        </button>
                        <a href="data_unit.php" class="btn-cancel" style="text-decoration: none; margin-left: 10px;">
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