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
// Ambil nama pengunduh dari session nama_lengkap
$user_pengunduh = $_SESSION['nama']; 

// 1. Ambil data arsip dari database
$query = mysqli_query($koneksi, "SELECT file_arsip FROM arsip WHERE id_arsip = '$id_arsip'");
$data = mysqli_fetch_assoc($query);

if ($data) {
    // 2. Tentukan path file yang benar sesuai folder upload kita
    $file_path = "../../assets/uploads/arsip/" . $data['file_arsip'];

    if (file_exists($file_path)) {
        // 3. Simpan riwayat ke tabel log_download sesuai struktur database
        // created_at akan terisi otomatis oleh CURRENT_TIMESTAMP
        mysqli_query($koneksi, "INSERT INTO log_download (id_arsip, user_pengunduh) 
                                VALUES ('$id_arsip', '$user_pengunduh')");

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