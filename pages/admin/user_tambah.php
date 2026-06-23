<?php
session_start();
// Proteksi halaman
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";
/** @var mysqli $koneksi */

// Ambil data unit kerja untuk dropdown
$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");

// Proses Simpan Data
if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $status = 'aktif';

    // Inisialisasi default nilai unit
    $id_unit = "NULL";
    $nama_unit = "NULL";

    if ($role === 'petugas' && !empty($_POST['id_unit'])) {
        $id_unit_input = mysqli_real_escape_string($koneksi, $_POST['id_unit']);
        $id_unit = "'" . $id_unit_input . "'";

        $cari_nama_unit = mysqli_query($koneksi, "SELECT nama_unit FROM unit_kerja WHERE id_unit = '$id_unit_input'");
        $data_unit = mysqli_fetch_assoc($cari_nama_unit);
        
        if ($data_unit) {
            $nama_unit = "'" . mysqli_real_escape_string($koneksi, $data_unit['nama_unit']) . "'";
        }
    }

    // Proses Upload Foto Profil
    $foto_name = "default.jpg";
    if (!empty($_FILES['foto']['name'])) {
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = time() . "_" . $username . "." . $extension;
        $target = "../../assets/img/profiles/" . $foto_name;
        move_uploaded_file($_FILES['foto']['tmp_name'], $target);
    }

    $cek_user = mysqli_query($koneksi, "SELECT * FROM users WHERE username = '$username'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Username sudah digunakan, cari yang lain!');</script>";
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO users (username, password, nama_lengkap, id_unit, nama_unit, role, foto, status) 
                  VALUES ('$username', '$password', '$nama', $id_unit, $nama_unit, '$role', '$foto_name', '$status')");

        if ($insert) {
            echo "<script>alert('Akun user berhasil dibuat!'); window.location='data_user.php';</script>";
        } else {
            echo "Error: " . mysqli_error($koneksi);
        }
    }
}

$page = 'data_user.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Tambah User - SIAPSIJUNJUNG</title>
</head>

<style>
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
        text-decoration: none;
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
                    <h1>Tambah User Baru</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_user.php">Manajemen User</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Tambah User</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-box">
                <div class="head" style="margin-bottom: 25px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
                    <h3 style="color: var(--dark);">Form Registrasi Pegawai</h3>
                    <p style="font-size: 14px; color: var(--dark-grey);">Daftarkan akun petugas unit atau pimpinan untuk akses sistem.</p>
                </div>

                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Lengkap Pegawai</label>
                        <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap & gelar" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" placeholder="Untuk keperluan login" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Minimal 6 karakter" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Hak Akses (Role)</label>
                            <select name="role" id="role_select" onchange="toggleUnitField()" required>
                                <option value="petugas">Petugas Unit (Bidan/Perawat/Staf)</option>
                                <option value="pimpinan">Pimpinan (Kepala Puskesmas/KTU)</option>
                                <option value="admin">Administrator Sistem</option>
                            </select>
                        </div>

                        <div class="form-group" id="unit_field">
                            <label>Penempatan Unit Kerja</label>
                            <select name="id_unit">
                                <option value="" disabled selected>-- Pilih Unit Kerja --</option>
                                <?php while ($u = mysqli_fetch_assoc($units)) : ?>
                                    <option value="<?= $u['id_unit']; ?>"><?= $u['nama_unit']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Foto Profil <small style="color: var(--dark-grey);">(Opsional, format JPG/PNG)</small></label>
                        <input type="file" name="foto" accept="image/*">
                    </div>

                    <div class="form-action" style="margin-top: 30px;">
                        <button type="submit" name="simpan" class="btn-save">
                            <i class='bx bxs-user-check'></i> Buat Akun Sekarang
                        </button>
                        <a href="data_user.php" class="btn-cancel" style="text-decoration: none;">
                            <i class='bx bx-x'></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </main>
    </section>

    <script>
        function toggleUnitField() {
            var role = document.getElementById('role_select').value;
            var unitField = document.getElementById('unit_field');
            var unitSelect = unitField.querySelector('select[name="id_unit"]');

            if (role === 'admin' || role === 'pimpinan') {
                unitField.style.display = 'none';
                unitSelect.disabled = true;
                unitSelect.value = "";
            } else {
                unitField.style.display = 'block';
                unitSelect.disabled = false;
            }
        }
        window.onload = toggleUnitField;
    </script>
    <script src="../../assets/js/script.js"></script>
</body>

</html>