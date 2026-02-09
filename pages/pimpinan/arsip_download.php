<?php
session_start();
include "../../config/koneksi.php";

if (!isset($_SESSION['login'])) { exit; }

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$nama_user = $_SESSION['nama']; // Mengambil nama lengkap Pimpinan dari session

$query = mysqli_query($koneksi, "SELECT file_arsip FROM arsip WHERE id_arsip = '$id'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    $file_path = "../../assets/uploads/arsip/" . $data['file_arsip'];

    if (file_exists($file_path)) {
        // CATAT KE LOG: Pimpinan mendownload berkas ini
        mysqli_query($koneksi, "INSERT INTO log_download (id_arsip, user_pengunduh, waktu_download) VALUES ('$id', '$nama_user', NOW())");

        // Proses Download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
        readfile($file_path);
        exit;
    }
}
?>