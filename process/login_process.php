<?php
session_start();
require '../config/koneksi.php';

// Ensure $koneksi is defined
if (!isset($koneksi)) {
    die("Database connection error. Please check 'koneksi.php'.");
}

$username = mysqli_real_escape_string($koneksi, $_POST['username']);
$password = $_POST['password'];

// QUERY DIPERBAIKI: Menggunakan LEFT JOIN agar bisa mengambil 'nama_unit' dari tabel unit_kerja
$query = mysqli_query($koneksi, "SELECT u.*, uk.nama_unit 
                                  FROM users u 
                                  LEFT JOIN unit_kerja uk ON u.id_unit = uk.id_unit 
                                  WHERE u.username = '$username'");

$data = mysqli_fetch_assoc($query);

// Verifikasi password dan status akun
if ($data && password_verify($password, $data['password'])) {
    if ($data['status'] !== 'aktif') {
        echo "<script>alert('Akun Anda dinonaktifkan!'); window.location='../index.php';</script>";
        exit;
    }

    $_SESSION['login']    = true;
    $_SESSION['id_user']  = $data['id_user'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['nama']     = $data['nama_lengkap']; // Nama lengkap user
    $_SESSION['role']     = $data['role']; 
    $_SESSION['id_unit']  = $data['id_unit']; 
    $_SESSION['foto']     = $data['foto'];
    
    // SEKARANG NAMA UNIT SUDAH TERSEDIA KARENA HASIL JOIN
    $_SESSION['nama_unit'] = $data['nama_unit'] ?? 'Tanpa Unit';

    // Redirect otomatis berdasarkan role
    if ($data['role'] === 'admin') {
        header("Location: ../pages/admin/dashboard.php");
    } else if ($data['role'] === 'pimpinan') {
        header("Location: ../pages/pimpinan/dashboard.php");
    } else {
        header("Location: ../pages/petugas/dashboard.php");
    }
    exit;
} else {
    echo "<script>
            alert('Username atau password salah!');
            window.location='../index.php';
          </script>";
}
?>