<?php

date_default_timezone_set('Asia/Jakarta');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_siapus";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    error_log("Database Connection Error : " . mysqli_connect_error());
    die("Terjadi kesalahan saat menghubungkan ke database.");
}

mysqli_set_charset($koneksi, "utf8mb4");

/**
 * Mencatat aktivitas pengguna.
 */
function catat_log($koneksi, $id_user, $aktivitas, $objek_aktivitas)
{
    // Login & Logout tidak dicatat
    if ($aktivitas === "Login" || $aktivitas === "Logout") {
        return true;
    }

    $id_user = (int)$id_user;

    $waktu = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO log_aktivitas
        (waktu,id_user,aktivitas,objek_aktivitas)
        VALUES (?,?,?,?)"
    );

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "siss",
        $waktu,
        $id_user,
        $aktivitas,
        $objek_aktivitas
    );

    $hasil = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $hasil;
}