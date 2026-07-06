<?php
session_start(); // ✅ Aktifkan session agar id_user terbaca oleh log
include "../../config/koneksi.php";
/** @var mysqli $koneksi */

// Proteksi Akses
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    // 1. Ambil nama unit sebelum dihapus dari database
    $cek_unit = mysqli_query($koneksi, "SELECT nama_unit FROM unit_kerja WHERE id_unit = '$id_hapus'");
    $data_unit = mysqli_fetch_assoc($cek_unit);

    if ($data_unit) {
        $nama_unit_dihapus = $data_unit['nama_unit'];

        $delete = mysqli_query($koneksi, "DELETE FROM unit_kerja WHERE id_unit = '$id_hapus'");

        if ($delete) {
            // ✅ CCTV MENCATAT LOG AKTIVITAS
            catat_log($koneksi, $_SESSION['id_user'], 'Hapus Unit', $nama_unit_dihapus);

            echo "<script>
                alert('Unit kerja berhasil dihapus!');
                window.location = 'data_unit.php';
            </script>";
        } else {
            echo "<script>
                alert('Gagal menghapus! Data mungkin sedang digunakan oleh user atau arsip.');
                window.location = 'data_unit.php';
            </script>";
        }
    } else {
        header("Location: data_unit.php");
    }
}
?>