<?php
session_start();
include "../../config/koneksi.php";

// Ensure $koneksi is defined
if (!isset($koneksi)) {
    die("Database connection error.");
}

if (!isset($_SESSION['login']) || $_SESSION['role'] == 'pimpinan') {
    header("Location: data_arsip.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);

    // 1. Ambil nama file dan nama arsip (nama_arsip ditambah untuk objek log)
    $cek = mysqli_query($koneksi, "SELECT nama_arsip, file_arsip FROM arsip WHERE id_arsip = '$id'");
    $data = mysqli_fetch_assoc($cek);

    if ($data) {
        $path = "../../assets/uploads/arsip/" . $data['file_arsip'];
        if (file_exists($path)) {
            unlink($path);
        }

        // 2. Simpan nama arsip ke variabel sebelum kueri DELETE dijalankan
        $nama_arsip_dihapus = $data['nama_arsip'];

        $delete = mysqli_query($koneksi, "DELETE FROM arsip WHERE id_arsip = '$id'");
        
        if ($delete) {
            // ✅ CCTV mencatat HANYA ketika database terbukti sukses menghapus data
            catat_log($koneksi, $_SESSION['id_user'], 'Hapus Arsip', $nama_arsip_dihapus);
        }

        echo "<script>alert('Arsip berhasil dihapus!'); window.location='data_arsip.php';</script>";
    }
} else {
    header("Location: data_arsip.php");
}
?>