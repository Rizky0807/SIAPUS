<?php
session_start();
include "../../config/koneksi.php";
/** @var mysqli $koneksi */

if (!isset($_SESSION['login'])) { exit; }

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
// Ambil ID user dari session untuk pencatatan log baru
$id_user = $_SESSION['id_user']; 

// 1. Ambil data nama_arsip dan file_arsip dari database
$query = mysqli_query($koneksi, "SELECT nama_arsip, file_arsip FROM arsip WHERE id_arsip = '$id'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    $file_path = "../../assets/uploads/arsip/" . $data['file_arsip'];

    if (file_exists($file_path)) {
        // 2. PANGGIL FUNGSI GLOBAL CCTV LOG AKTIVITAS
        $nama_arsip = $data['nama_arsip'];
        catat_log($koneksi, $id_user, 'Download Arsip', $nama_arsip);

        // 3. Proses Download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
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