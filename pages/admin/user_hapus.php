<?php
session_start();
include "../../config/koneksi.php";

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

    // Ambil data foto untuk dihapus dari folder
    $cek = mysqli_query($koneksi, "SELECT foto FROM users WHERE id_user = '$id'");
    $data = mysqli_fetch_assoc($cek);

    // Hapus file foto jika bukan default
    if ($data['foto'] != 'default.jpg' && file_exists("../../assets/img/profiles/" . $data['foto'])) {
        unlink("../../assets/img/profiles/" . $data['foto']);
    }

    $delete = mysqli_query($koneksi, "DELETE FROM users WHERE id_user = '$id'");
    
    if ($delete) {
        echo "<script>alert('User berhasil dihapus!'); window.location='data_user.php';</script>";
    }
} else {
    header("Location: data_user.php");
}
?>