if (isset($_GET['hapus'])) {
$id_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);


$delete = mysqli_query($koneksi, "DELETE FROM unit_kerja WHERE id_unit = '$id_hapus'");

if ($delete) {
echo "<script>
    alert('Unit kerja berhasil dihapus!');
    window.location = 'data_unit.php';
</script>";
} else {
echo "<script>
    alert('Gagal menghapus! Data mungkin sedang digunakan oleh user atau arsip.');
    window.location = 'data_unit.php';
</script>";
}
}