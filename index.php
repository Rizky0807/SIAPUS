<?php
session_start();
if (isset($_SESSION['login'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: pages/admin/dashboard.php");
    } else if ($_SESSION['role'] == 'pimpinan') {
        header("Location: pages/pimpinan/dashboard.php");
    } else {
        header("Location: pages/petugas/dashboard.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Login | SIAPuskesmas Sijunjung</title>
  <link rel="stylesheet" href="assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>
  
<div class="container" id="container">
    
    <div class="form-container sign-up">
        <form action="process/login_process.php" method="POST">
            <h1 id="login-title">Login Petugas</h1>
            <p id="login-desc">Silakan masuk untuk mengelola arsip unit Anda.</p>

            <select name="role" required style="width: 100%; padding: 12px; margin: 10px 0; border-radius: 10px; border: 1px solid #e0e0e0; background: #f8f9fa; outline: none; font-size: 14px;">
            <option value="" disabled selected>-- Pilih Peran --</option>
            <option value="petugas">Petugas Unit</option>
            <option value="pimpinan">Pimpinan (Kepala Puskesmas)</option>
        </select>

            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>

    <div class="form-container sign-in">
        <form action="process/login_process.php" method="POST">
            <h1>Login Admin</h1>
            <p>Akses penuh konfigurasi sistem.</p>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>

    <div class="toggle-container">
        <div class="toggle">
            <div class="toggle-panel toggle-left">
                <h1>Selamat Datang </h1><h2>Sistem Informasi Arsip Digital Puskesmas Sijunjung</h2>
                <p>Klik tombol di bawah jika Anda adalah Administrator sistem.</p>
                <button class="hidden" id="admin">Login Admin</button>
            </div>

            <div class="toggle-panel toggle-right">
            <h1>Selamat Datang </h1><h2>Sistem Informasi Arsip Digital Puskesmas Sijunjung</h2>
                <p>Login sebagai <b>Petugas Unit</b> atau <b>Pimpinan</b> melalui halaman ini.</p>
                <button class="hidden" id="user">Login Unit</button>
            </div>
        </div>
    </div>
</div>
  <script src="assets/js/login.js"></script>
</body>


</html>