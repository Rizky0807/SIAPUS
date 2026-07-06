<?php
session_start(); // ✅ Aktifkan session agar id_user terbaca oleh sistem log
include '../../config/koneksi.php'; 

// Proteksi Akses (Opsional tapi disarankan agar file tidak ditembak langsung dari URL)
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

if (!isset($koneksi)) {
    die("Database connection not established. Please check 'koneksi.php'.");
}

if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Cek apakah kategori ini masih digunakan oleh data arsip
    $cek_arsip = mysqli_query($koneksi, "SELECT * FROM arsip WHERE id_kategori = '$id_hapus'");
    
    if (mysqli_num_rows($cek_arsip) > 0) {
        echo "<script>alert('Kategori tidak bisa dihapus karena masih digunakan oleh beberapa arsip!'); window.location='data_kategori.php';</script>";
    } else {
        // ✅ 1. Ambil nama kategori sebelum datanya dihapus dari database
        $cek_kat = mysqli_query($koneksi, "SELECT nama_kategori FROM kategori WHERE id_kategori = '$id_hapus'");
        $data_kat = mysqli_fetch_assoc($cek_kat);
        $nama_kategori_dihapus = $data_kat ? $data_kat['nama_kategori'] : 'Kategori Tidak Diketahui';

        $delete = mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = '$id_hapus'");
        
        if ($delete) {
            // ✅ 2. CCTV MENCATAT LOG AKTIVITAS HAPUS
            catat_log($koneksi, $_SESSION['id_user'], 'Hapus Kategori', $nama_kategori_dihapus);

            echo "<script>alert('Kategori berhasil dihapus!'); window.location='data_kategori.php';</script>";
            exit;
        }
    }
}
?>