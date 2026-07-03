<?php
session_start();
// Proteksi halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";
/** @var mysqli $koneksi */

// Ambil ID dari URL dan amankan dengan escape string
$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = mysqli_query($koneksi, "SELECT * FROM unit_kerja WHERE id_unit = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika ID tidak ditemukan
if (mysqli_num_rows($query) < 1) {
    header("Location: data_unit.php");
    exit;
}

// Proses Update Data
if (isset($_POST['update'])) {
    // 💡 AMBIL DATA DARI INPUTAN BARU
    $kode_unit = mysqli_real_escape_string($koneksi, trim($_POST['kode_unit']));
    $nama_unit = mysqli_real_escape_string($koneksi, trim($_POST['nama_unit']));
    $penanggung_jawab = mysqli_real_escape_string($koneksi, trim($_POST['penanggung_jawab']));
    
    // 💡 VALIDASI KODE UNIT GANDA (Kecuali milik unit ini sendiri)
    $cek_kode = mysqli_query($koneksi, "SELECT * FROM unit_kerja WHERE kode_unit = '$kode_unit' AND id_unit != '$id'");
    if (mysqli_num_rows($cek_kode) > 0) {
        echo "<script>alert('Kode unit tersebut sudah digunakan oleh unit lain!');</script>";
    } else {
        // 💡 UPDATE TIGA KOLOM SEKALIGUS MENGGUNAKAN PRIMARY KEY id_unit
        $update = mysqli_query($koneksi, "UPDATE unit_kerja SET 
                                          kode_unit = '$kode_unit', 
                                          nama_unit = '$nama_unit', 
                                          penanggung_jawab = '$penanggung_jawab' 
                                          WHERE id_unit = '$id'");
        
        if ($update) {
            echo "<script>alert('Data unit berhasil diperbarui!'); window.location='data_unit.php';</script>";
            exit;
        } else {
            echo "<script>alert('Gagal memperbarui data.');</script>";
        }
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
        grid-gap: 10px; 
        margin-top: 10px;
    }

    .breadcrumb li {
        color: var(--dark);
        list-style: none; 
        display: flex;
        align-items: center;
    }

    .breadcrumb li a {
        color: var(--dark-grey);
        font-size: 14px;
    }

    .breadcrumb li a.active {
        color: var(--blue); 
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
                    <h1>Edit Unit Kerja</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_unit.php">Unit Kerja</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Edit Data</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-box">
                <div class="head" style="margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <h3 style="color: var(--dark); font-size: 18px;">Form Edit Unit Kerja</h3>
                    <p style="font-size: 13px; color: var(--dark-grey);">Masukkan Perubahan Atribut Unit.</p>
                </div>
                <form action="" method="POST">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Kode Unit</label>
                        <input type="text" name="kode_unit" value="<?= htmlspecialchars($data['kode_unit']); ?>" required placeholder="Contoh: Poli-KIA" style="width: 100%; padding: 8px; margin-top: 5px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Nama Unit Kerja</label>
                        <input type="text" name="nama_unit" value="<?= htmlspecialchars($data['nama_unit']); ?>" required placeholder="Contoh: Poli Kesehatan Ibu dan Anak" style="width: 100%; padding: 8px; margin-top: 5px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Penanggung Jawab (Kepala Unit)</label>
                        <input type="text" name="penanggung_jawab" value="<?= htmlspecialchars($data['penanggung_jawab']); ?>" required placeholder="Contoh: dr. Nila Sari" style="width: 100%; padding: 8px; margin-top: 5px;">
                    </div>

                    <div class="form-action" style="margin-top: 20px;">
                        <button type="submit" name="update" class="btn-save">
                            <i class='bx bxs-save'></i> Simpan Perubahan
                        </button>
                        <a href="data_unit.php" class="btn-cancel" style="text-decoration: none; margin-left: 10px;">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>
</html>