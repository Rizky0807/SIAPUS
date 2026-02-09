<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

$role = $_SESSION['role'];
$id_unit_user = $_SESSION['id_unit'];
$nama_unit_user = $_SESSION['nama_unit'];

// Ambil data Kategori untuk dropdown
$kategori_query = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Ambil data Unit (Hanya untuk Admin/Pimpinan)
$unit_query = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");

if (isset($_POST['simpan'])) {
    $kode_arsip   = mysqli_real_escape_string($koneksi, $_POST['kode_arsip']);
    $nama_arsip   = mysqli_real_escape_string($koneksi, $_POST['nama_arsip']);
    $id_kategori  = $_POST['id_kategori'];
    $deskripsi    = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $tgl_upload   = date('Y-m-d H:i:s');
    
    // Tentukan ID Unit (Jika petugas, pakai unitnya sendiri. Jika admin, pakai pilihan form)
    $id_unit = ($role == 'petugas') ? $id_unit_user : $_POST['id_unit'];

    // Proses Upload File
    $file_name = $_FILES['file_arsip']['name'];
    $file_tmp  = $_FILES['file_arsip']['tmp_name'];
    $file_size = $_FILES['file_arsip']['size'];
    $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
    
    // Rename file agar unik: KODE_WAKTU.ekstensi
    $new_file_name = str_replace('/', '-', $kode_arsip) . "_" . time() . "." . $ext;
    $target_path   = "../../assets/uploads/arsip/" . $new_file_name;

    // Batasi tipe file (PDF, JPG, PNG)
    $allowed_ext = ['pdf', 'jpg', 'jpeg', 'png'];

    if (in_array(strtolower($ext), $allowed_ext)) {
        if ($file_size <= 5000000) { // Maksimal 5MB
            if (move_uploaded_file($file_tmp, $target_path)) {
                $insert = mysqli_query($koneksi, "INSERT INTO arsip (kode_arsip, nama_arsip, id_kategori, id_unit, file_arsip, deskripsi, created_at) 
                                                  VALUES ('$kode_arsip', '$nama_arsip', '$id_kategori', '$id_unit', '$new_file_name', '$deskripsi', '$tgl_upload')");
                if ($insert) {
                    echo "<script>alert('Arsip Berhasil Diunggah!'); window.location='data_arsip.php';</script>";
                }
            }
        } else {
            echo "<script>alert('Ukuran file terlalu besar! Maksimal 5MB');</script>";
        }
    } else {
        echo "<script>alert('Format file tidak didukung! Gunakan PDF/JPG/PNG');</script>";
    }
}

$page = 'data_arsip.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Upload Arsip - SIAPSIJUNJUNG</title>
</head>
<body>
    <?php include '../partials/sidebar.php'; ?>
    <section id="content">
        <?php include '../partials/navbar.php'; ?>
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Upload Arsip Baru</h1>
                </div>
            </div>

            <div class="table-data">
                <div class="order">
                    <form action="" method="POST" enctype="multipart/form-data" style="max-width: 800px;">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Kode Arsip / Nomor Surat</label>
                            <input type="text" name="kode_arsip" placeholder="Contoh: 001/SK/2026" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                        </div>

                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Nama / Judul Arsip</label>
                            <input type="text" name="nama_arsip" placeholder="Masukkan judul dokumen..." required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                        </div>

                        <div style="display: flex; gap: 20px; margin-bottom: 15px;">
                            <div class="form-group" style="flex: 1;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Kategori</label>
                                <select name="id_kategori" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php while($k = mysqli_fetch_assoc($kategori_query)) : ?>
                                        <option value="<?= $k['id_kategori']; ?>"><?= $k['nama_kategori']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group" style="flex: 1;">
                                <label style="display: block; margin-bottom: 5px; font-weight: 600;">Unit Kerja</label>
                                <?php if($role == 'petugas') : ?>
                                    <input type="text" value="<?= $nama_unit_user; ?>" readonly style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd; background: #eee;">
                                    <input type="hidden" name="id_unit" value="<?= $id_unit_user; ?>">
                                <?php else : ?>
                                    <select name="id_unit" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
                                        <option value="">-- Pilih Unit --</option>
                                        <?php while($u = mysqli_fetch_assoc($unit_query)) : ?>
                                            <option value="<?= $u['id_unit']; ?>"><?= $u['nama_unit']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 15px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">File Arsip (PDF/JPG/PNG)</label>
                            <input type="file" name="file_arsip" required style="width: 100%; padding: 8px; border: 1px dashed #bbb; border-radius: 8px;">
                            <small style="color: #666;">* Maksimal ukuran file 5MB</small>
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display: block; margin-bottom: 5px; font-weight: 600;">Deskripsi Singkat</label>
                            <textarea name="deskripsi" rows="3" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #ddd;"></textarea>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="simpan" class="btn-add" style="border: none; cursor: pointer;">
                                <i class='bx bx-save'></i> Simpan & Upload
                            </button>
                            <a href="data_arsip.php" class="btn-cancel" style="padding: 10px 20px; text-decoration: none; border-radius: 36px; font-size: 14px; background: #eee; color: #333;">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>
</html>