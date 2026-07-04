<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
if (!isset($koneksi)) {
    die("Database connection error. Please check your configuration.");
}

$role = $_SESSION['role'];
$id_unit_user = $_SESSION['id_unit'];
$nama_unit_user = $_SESSION['nama_unit'];

// Ambil data kategori untuk dropdown petugas
$kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

if (isset($_POST['simpan'])) {
    $kode_arsip = mysqli_real_escape_string($koneksi, $_POST['kode_arsip']);
    $nama_arsip = mysqli_real_escape_string($koneksi, $_POST['nama_arsip']);
    $id_kategori = $_POST['id_kategori'];
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    
    // Karena role petugas, ID unit otomatis terkunci ke unitnya sendiri dari session
    $id_unit = $id_unit_user;

    // Proses Upload Multi-Format (Disamakan dengan logika Admin)
    $file_name = $_FILES['file_arsip']['name'];
    $tmp_name = $_FILES['file_arsip']['tmp_name'];
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Filter ekstensi sesuai standar sistem Admin Mas
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
            // Asumsi $nama_arsip adalah variabel yang menangkap input judul arsip dari form
            catat_log($koneksi, $_SESSION['id_user'], 'Upload Arsip', $nama_arsip); 
            if ($insert) {
                echo "<script>alert('Arsip berhasil diunggah!'); window.location='data_arsip.php';</script>";
            } else {
                echo "<script>alert('Gagal menyimpan ke database!');</script>";
            }
        }
    }
}
$page = 'data_arsip.php';
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
    /* Styling Breadcrumb agar Sejajar (Persis seperti Admin) */
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

                    <div class="form-group">
                        <label>Unit Kerja</label>
                        <input type="text" value="<?= htmlspecialchars($nama_unit_user); ?>" readonly style="background: #eee; color: #666; cursor: not-allowed;">
                    </div>

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