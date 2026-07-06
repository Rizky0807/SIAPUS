<?php
session_start();
include "../../config/koneksi.php";
/** @var mysqli $koneksi */

// Proteksi Admin
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Cegah admin menghapus dirinya sendiri
    if ($id == $_SESSION['id_user']) {
        echo "<script>alert('Gagal! Anda tidak bisa menghapus akun yang sedang digunakan.'); window.location='data_user.php';</script>";
        exit;
    }

    // Ambil data profil (ditambahkan username untuk kebutuhan penamaan log)
    $cek = mysqli_query($koneksi, "SELECT username, foto FROM users WHERE id_user = '$id'");
    $data = mysqli_fetch_assoc($cek);

    if ($data) {
        // Simpan username target ke variabel penampung log sebelum baris data terhapus
        $username_dihapus = $data['username'];

        // Hapus file foto jika bukan default
        if ($data['foto'] != 'default.jpg' && file_exists("../../assets/img/profiles/" . $data['foto'])) {
            unlink("../../assets/img/profiles/" . $data['foto']);
        }

        $delete = mysqli_query($koneksi, "DELETE FROM users WHERE id_user = '$id'");
        
        if ($delete) {
            catat_log($koneksi, $_SESSION['id_user'], 'Hapus Pengguna', $username_dihapus);
            
            echo "<script>alert('User berhasil dihapus!'); window.location='data_user.php';</script>";
        }
    }
} else {
    header("Location: data_user.php");
}
?>