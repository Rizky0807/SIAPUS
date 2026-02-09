<?php
if (isset($_GET['hapus'])) {
    $id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    
    // Cek apakah kategori ini masih digunakan oleh data arsip
    $cek_arsip = mysqli_query($koneksi, "SELECT * FROM arsip WHERE id_kategori = '$id_hapus'");
    
    if (mysqli_num_rows($cek_arsip) > 0) {
        echo "<script>alert('Kategori tidak bisa dihapus karena masih digunakan oleh beberapa arsip!'); window.location='data_kategori.php';</script>";
    } else {
        $delete = mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = '$id_hapus'");
        if ($delete) {
            echo "<script>alert('Kategori berhasil dihapus!'); window.location='data_kategori.php';</script>";
        }
    }
}

?>