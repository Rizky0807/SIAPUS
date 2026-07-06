<?php
session_start();
include "config/koneksi.php"; // Pastikan path ke koneksi.php ini sudah pas dengan letak logout.php Mas

// ✅ Ambil ID user dari session sebelum dihancurkan
if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];
    
    // ✅ CCTV mencatat aktivitas keluar sistem
    catat_log($koneksi, $id_user, 'Logout', 'Keluar dari sistem');
}

/* Hapus semua session */
session_unset();
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

/* Redirect ke login */
header("Location: index.php");
exit;