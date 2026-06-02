<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] == 'pimpinan') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
if (!isset($koneksi)) {
    die("Database connection error. Please check your configuration.");
}

// Ambil data kategori & unit untuk dropdown
$kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");

if (isset($_POST['simpan'])) {
    $kode_arsip = mysqli_real_escape_string($koneksi, $_POST['kode_arsip']);
    $nama_arsip = mysqli_real_escape_string($koneksi, $_POST['nama_arsip']);
    $id_kategori = $_POST['id_kategori'];
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    // Penentuan ID Unit berdasarkan role
    $id_unit = ($_SESSION['role'] == 'admin') ? $_POST['id_unit'] : $_SESSION['id_unit'];

    // Proses Upload Multi-Format
    $file_name = $_FILES['file_arsip']['name'];
    $tmp_name = $_FILES['file_arsip']['tmp_name'];
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Filter ekstensi yang diizinkan
    $allowed_ext = ['pdf', 'docx', 'doc', 'jpg', 'jpeg', 'png'];
    
    if (!in_array($extension, $allowed_ext)) {
        echo "<script>alert('Format file tidak didukung! Gunakan PDF, DOCX, atau Gambar.');</script>";
    } else {
        // Nama file unik agar tidak bentrok
        $new_file_name = time() . "_" . str_replace(' ', '_', $nama_arsip) . "." . $extension;
        $target = "../../assets/uploads/arsip/" . $new_file_name;
        
        if (move_uploaded_file($tmp_name, $target)) {
            // Insert ke database sesuai struktur db_siapus
            $insert = mysqli_query($koneksi, "INSERT INTO arsip (kode_arsip, nama_arsip, id_kategori, id_unit, file_arsip, deskripsi, created_at) 
                      VALUES ('$kode_arsip', '$nama_arsip', '$id_kategori', '$id_unit', '$new_file_name', '$deskripsi', NOW())");
            
            if ($insert) {
                echo "<script>alert('Arsip berhasil diunggah!'); window.location='data_arsip.php';</script>";
            } else {
                echo "<script>alert('Gagal menyimpan ke database!');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <title>Upload Arsip - SIAPSIJUNJUNG</title>
</head>

<style>
        /* Styling Breadcrumb agar Sejajar */
        .breadcrumb {
        display: flex;
        align-items: center;
        grid-gap: 10px;
        /* Jarak antar elemen */
        margin-top: 10px;
    }

    .breadcrumb li {
        color: var(--dark);
        list-style: none;
        /* Menghilangkan titik list */
        display: flex;
        align-items: center;
    }

    .breadcrumb li a {
        color: var(--dark-grey);
        font-size: 14px;
    }

    .breadcrumb li a.active {
        color: var(--blue);
        /* Warna khusus untuk halaman aktif */
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
                    <h1>Upload Arsip Digital</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_arsip.php">Data Arsip</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Upload</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-box">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Judul / Nama Arsip</label>
                        <input type="text" name="nama_arsip" placeholder="Contoh: SK Kader Posyandu" required>
                    </div>
                    <div class="form-group">
                        <label>Kode Arsip</label>
                        <input type="text" name="kode_arsip" placeholder="Masukkan Kode Arsip" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="id_kategori" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while($k = mysqli_fetch_assoc($kategori)) : ?>
                                <option value="<?= $k['id_kategori']; ?>"><?= $k['nama_kategori']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php if ($_SESSION['role'] == 'admin') : ?>
                    <div class="form-group">
                        <label>Unit Kerja Terkait</label>
                        <select name="id_unit" required>
                            <?php while($u = mysqli_fetch_assoc($units)) : ?>
                                <option value="<?= $u['id_unit']; ?>"><?= $u['nama_unit']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label>File Arsip (PDF, DOCX, JPG, PNG)</label>
                        <input type="file" name="file_arsip" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi Tambahan</label>
                        <textarea name="deskripsi" rows="4"></textarea>
                    </div>
                    <div class="form-action">
                        <button type="submit" name="simpan" class="btn-save"><i class='bx bx-upload'></i> Unggah Sekarang</button>
                        <a href="data_arsip.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </section>
</body>
</html>