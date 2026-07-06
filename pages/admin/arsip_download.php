<?php
session_start();
include "../../config/koneksi.php";

// Ensure $koneksi is defined
if (!isset($koneksi)) {
    die("Database connection error: Undefined variable 'koneksi'.");
}

// Pastikan user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: ../../index.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: data_arsip.php");
    exit;
}

$id_arsip = mysqli_real_escape_string($koneksi, $_GET['id']);
// Ambil ID user dari session untuk pencatatan log baru
$id_user = $_SESSION['id_user']; 

// 1. Ambil data nama_arsip dan file_arsip dari database (ditambah nama_arsip untuk log)
$query = mysqli_query($koneksi, "SELECT nama_arsip, file_arsip FROM arsip WHERE id_arsip = '$id_arsip'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    // 2. Tentukan path file yang benar sesuai folder upload kita
    $file_path = "../../assets/uploads/arsip/" . $data['file_arsip'];

    if (file_exists($file_path)) {
        // 3. PANGGIL FUNGSI GLOBAL CCTV KITA
        // Menggantikan kueri log_download yang lama
        $nama_arsip = $data['nama_arsip'];
        catat_log($koneksi, $id_user, 'Download Arsip', $nama_arsip);

        // 4. Proses Force Download agar mendukung berbagai format (PDF, DOCX, Gambar)
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file_path).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        
        // Membersihkan output buffer untuk mencegah file korup
        ob_clean();
        flush();
        
        readfile($file_path);
        exit;
    } else {
        echo "<script>alert('Error: File fisik tidak ditemukan di server!'); window.location='data_arsip.php';</script>";
    }
} else {
    header("Location: data_arsip.php");
}
?>