<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
/** @var mysqli $koneksi */

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id'");
$data = mysqli_fetch_assoc($query);

// Jika data user tidak ditemukan
if (!$data) {
    echo "<script>alert('Data user tidak ditemukan!'); window.location='data_user.php';</script>";
    exit;
}

// Ambil data unit untuk dropdown
$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");

if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    
    // 💡 PERBAIKAN LOGIKA: Inisialisasi default nilai unit baru
    $id_unit = "NULL";
    $nama_unit = "NULL";

    // Jika yang diedit diubah/tetap menjadi petugas
    if ($role === 'petugas' && !empty($_POST['id_unit'])) {
        $id_unit_input = mysqli_real_escape_string($koneksi, $_POST['id_unit']);
        $id_unit = "'" . $id_unit_input . "'";

        // Ambil nama unit terbaru dari tabel unit_kerja
        $cari_nama_unit = mysqli_query($koneksi, "SELECT nama_unit FROM unit_kerja WHERE id_unit = '$id_unit_input'");
        $data_unit = mysqli_fetch_assoc($cari_nama_unit);
        
        if ($data_unit) {
            $nama_unit = "'" . mysqli_real_escape_string($koneksi, $data_unit['nama_unit']) . "'";
        }
    }
    
    // Logika Password: Jika diisi maka ganti, jika kosong tetap password lama
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql_pass = ", password = '$password'";
    } else {
        $sql_pass = "";
    }

    // Logika Foto Profil
    $foto_name = $data['foto'];
    if (!empty($_FILES['foto']['name'])) {
        $extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = time() . "_" . $username . "." . $extension;
        move_uploaded_file($_FILES['foto']['tmp_name'], "../../assets/img/profiles/" . $foto_name);
        
        // Hapus foto lama jika bukan default
        if ($data['foto'] != 'default.jpg' && file_exists("../../assets/img/profiles/" . $data['foto'])) {
            unlink("../../assets/img/profiles/" . $data['foto']);
        }
    }

    // 💡 SEKARANG UPDATE TERMASUK id_unit TANPA PETIK DAN nama_unit
    $update = mysqli_query($koneksi, "UPDATE users SET 
                nama_lengkap = '$nama', 
                username = '$username', 
                role = '$role', 
                id_unit = $id_unit, 
                nama_unit = $nama_unit, 
                status = '$status', 
                foto = '$foto_name' 
                $sql_pass 
                WHERE id_user = '$id'");

    if ($update) {
        echo "<script>alert('Data user berhasil diperbarui!'); window.location='data_user.php';</script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}

$page = 'data_user.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Edit User - SIAPSIJUNJUNG</title>
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
                    <h1>Edit Data User</h1>
                    <ul class="breadcrumb">
                        <li><a href="data_user.php">Data User</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Edit</a></li>
                    </ul>
                </div>
            </div>

            <div class="form-box">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($data['nama_lengkap']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?= htmlspecialchars($data['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Password (Kosongkan jika tidak ingin diubah)</label>
                        <input type="password" name="password" placeholder="******">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="role_select" onchange="toggleUnit()" required>
                            <option value="petugas" <?= $data['role'] == 'petugas' ? 'selected' : ''; ?>>Petugas Unit</option>
                            <option value="pimpinan" <?= $data['role'] == 'pimpinan' ? 'selected' : ''; ?>>Pimpinan</option>
                            <option value="admin" <?= $data['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="unit_box">
                        <label>Unit Kerja</label>
                        <select name="id_unit">
                            <option value="" disabled>-- Pilih Unit Kerja --</option>
                            <?php 
                            mysqli_data_seek($units, 0);
                            while($u = mysqli_fetch_assoc($units)) : 
                            ?>
                                <option value="<?= $u['id_unit']; ?>" <?= $data['id_unit'] == $u['id_unit'] ? 'selected' : ''; ?>>
                                    <?= $u['nama_unit']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status Akun</label>
                        <select name="status">
                            <option value="aktif" <?= $data['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                            <option value="nonaktif" <?= $data['status'] == 'nonaktif' ? 'selected' : ''; ?>>Non-Aktif</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Foto Profil Baru (Opsional)</label>
                        <input type="file" name="foto" accept="image/*">
                        <small style="display: block; margin-top: 5px; color: var(--dark-grey);">Foto saat ini: <strong><?= $data['foto']; ?></strong></small>
                    </div>
                    <div class="form-action" style="margin-top: 30px;">
                        <button type="submit" name="update" class="btn-save">Update User</button>
                        <a href="data_user.php" class="btn-cancel" style="text-decoration: none;">Batal</a>
                    </div>
                </form>
            </div>
        </main>
    </section>

    <script>
        function toggleUnit() {
            var role = document.getElementById('role_select').value;
            var unitBox = document.getElementById('unit_box');
            var unitSelect = unitBox.querySelector('select[name="id_unit"]');

            if (role === 'admin' || role === 'pimpinan') {
                unitBox.style.display = 'none';
                unitSelect.disabled = true;
            } else {
                unitBox.style.display = 'block';
                unitSelect.disabled = false;
            }
        }
        // Jalankan fungsi saat halaman dimuat pertama kali untuk menyesuaikan data user yang ditarik
        window.onload = toggleUnit;
    </script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>