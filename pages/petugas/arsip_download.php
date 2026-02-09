<?php
session_start();
include "../../config/koneksi.php";

if (!isset($_SESSION['login'])) { exit; }

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
// Gunakan nama lengkap dari session
$user_pengunduh = $_SESSION['nama']; 

$query = mysqli_query($koneksi, "SELECT file_arsip, id_unit FROM arsip WHERE id_arsip = '$id'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    // PROTEKSI: Mencegah petugas download arsip unit lain
    if ($_SESSION['role'] == 'petugas' && $data['id_unit'] != $_SESSION['id_unit']) {
        echo "<script>alert('Akses ilegal terdeteksi!'); window.location='data_arsip.php';</script>";
        exit;
    }

    $file_path = "../../assets/uploads/arsip/" . $data['file_arsip'];

    if (file_exists($file_path)) {
        // 1. Catat log unduhan
        mysqli_query($koneksi, "INSERT INTO log_download (id_arsip, user_pengunduh) VALUES ('$id', '$user_pengunduh')");

        // 2. Kirim file ke browser
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
        header('Content-Length: ' . filesize($file_path));
        
        ob_clean();
        flush();
        readfile($file_path);
        exit;
    } else {
        echo "<script>alert('File fisik tidak ditemukan!'); window.history.back();</script>";
    }
}
?>