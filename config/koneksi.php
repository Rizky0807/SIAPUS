<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "db_siapus";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
  die("Koneksi gagal: " . mysqli_connect_error());
}

// FUNGSI MENCATAT LOG AKTIVITAS
function catat_log($koneksi, $id_user, $aktivitas, $objek_aktivitas) {
  // mencegah error SQL/Injeksi
  $aktivitas = mysqli_real_escape_string($koneksi, $aktivitas);
  $objek = mysqli_real_escape_string($koneksi, $objek_aktivitas);
  

  date_default_timezone_set('Asia/Jakarta');
  $waktu_sekarang = date('Y-m-d H:i:s');
  
  $query_log = "INSERT INTO log_aktivitas (waktu, id_user, aktivitas, objek_aktivitas) 
                VALUES ('$waktu_sekarang', '$id_user', '$aktivitas', '$objek')";
                
  mysqli_query($koneksi, $query_log);
}