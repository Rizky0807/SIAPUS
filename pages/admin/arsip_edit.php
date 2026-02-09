<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] == 'pimpinan') {
    header("Location: data_arsip.php");
    exit;
}
include "../../config/koneksi.php";

$id = mysqli_real_escape_string($koneksi, $_GET['id']);

// Ambil data arsip lama sesuai db_siapus
$query_arsip = mysqli_query($koneksi, "SELECT * FROM arsip WHERE id_arsip = '$id'");
$data = mysqli_fetch_assoc($query_arsip);

if (!$data) {
    header("Location: data_arsip.php");
    exit;
}

// Ambil data kategori & unit kerja untuk dropdown
$kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");

if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_arsip']);
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode_arsip']);
    $id_kat = $_POST['id_kategori'];
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    // Logika Unit Kerja: Admin bisa ubah, Petugas tetap
    $id_unit = ($_SESSION['role'] == 'admin') ? $_POST['id_unit'] : $data['id_unit'];

    $file_sql = "";
    if (!empty($_FILES['file_arsip']['name'])) {
        $file_name = $_FILES['file_arsip']['name'];
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['pdf', 'docx', 'doc', 'jpg', 'jpeg', 'png'];

        if (!in_array($extension, $allowed_ext)) {
            echo "<script>alert('Format file tidak didukung!');</script>";
        } else {
            // Hapus file lama jika ada
            if (file_exists("../../assets/uploads/arsip/" . $data['file_arsip'])) {
                unlink("../../assets/uploads/arsip/" . $data['file_arsip']);
            }

            // Upload file baru dengan ekstensi dinamis
            $new_name = time() . "_" . str_replace(' ', '_', $nama) . "." . $extension;
            move_uploaded_file($_FILES['file_arsip']['tmp_name'], "../../assets/uploads/arsip/" . $new_name);
            $file_sql = ", file_arsip = '$new_name'";
        }
    }

    // Update query sesuai nama kolom db_siapus
    $update = mysqli_query($koneksi, "UPDATE arsip SET 
                nama_arsip = '$nama', 
                kode_arsip = '$kode', 
                id_kategori = '$id_kat',
                id_unit = '$id_unit',
                deskripsi = '$deskripsi'
                $file_sql 
                WHERE id_arsip = '$id'");

    if ($update) {
        echo "<script>alert('Arsip berhasil diperbarui!'); window.location='data_arsip.php';</script>";
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
    <title>Edit Arsip - SIAPSIJUNJUNG</title>
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
                    <h1>Edit Data Arsip</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_arsip.php">Data Arsip</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Edit</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-box">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama / Judul Arsip</label>
                        <input type="text" name="nama_arsip" value="<?= $data['nama_arsip']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kode Arsip</label>
                        <input type="text" name="kode_arsip" value="<?= $data['kode_arsip']; ?>" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="id_kategori" required>
                                <?php while ($k = mysqli_fetch_assoc($kategori)) : ?>
                                    <option value="<?= $k['id_kategori']; ?>" <?= ($k['id_kategori'] == $data['id_kategori']) ? 'selected' : ''; ?>>
                                        <?= $k['nama_kategori']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Unit Kerja</label>
                            <?php if ($_SESSION['role'] == 'admin') : ?>
                                <select name="id_unit" required>
                                    <?php while ($u = mysqli_fetch_assoc($units)) : ?>
                                        <option value="<?= $u['id_unit']; ?>" <?= ($u['id_unit'] == $data['id_unit']) ? 'selected' : ''; ?>>
                                            <?= $u['nama_unit']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            <?php else : ?>
                                <input type="text" value="<?= $data['id_unit']; ?>" readonly style="background: #eee;">
                                <input type="hidden" name="id_unit" value="<?= $data['id_unit']; ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="deskripsi" rows="3"><?= $data['deskripsi']; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Ganti File (PDF, DOCX, Gambar) <small style="color:red;">*Kosongkan jika tidak ganti</small></label>
                        <input type="file" name="file_arsip">
                        <p style="font-size: 12px; margin-top: 5px;">File saat ini: <strong><?= $data['file_arsip']; ?></strong></p>
                    </div>

                    <div class="form-action">
                        <button type="submit" name="update" class="btn-save"><i class='bx bxs-save'></i> Simpan Perubahan</button>
                        <a href="data_arsip.php" class="btn-cancel">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </section>
</body>

</html>