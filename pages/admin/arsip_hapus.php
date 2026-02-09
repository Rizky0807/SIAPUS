<?php
session_start();
include "../../config/koneksi.php";

if (!isset($_SESSION['login']) || $_SESSION['role'] == 'pimpinan') {
    header("Location: data_arsip.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Ambil nama file arsip
    $cek = mysqli_query($koneksi, "SELECT file_arsip FROM arsip WHERE id_arsip = '$id'");
    $data = mysqli_fetch_assoc($cek);
    
    if ($data) {
        $path = "../../assets/files/" . $data['file_arsip'];
        if (file_exists($path)) {
            unlink($path); // Hapus file fisik
        }
        
        mysqli_query($koneksi, "DELETE FROM arsip WHERE id_arsip = '$id'");
        echo "<script>alert('Arsip berhasil dihapus!'); window.location='data_arsip.php';</script>";
    }
} else {
    header("Location: data_arsip.php");
}
?>