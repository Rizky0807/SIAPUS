<?php
session_start();
if (!isset($_SESSION['login'])) {
  header("Location: ../../index.php");
  exit;
}
include "../../config/koneksi.php";
if (!isset($koneksi)) {
  die("Database connection error. Please check your configuration.");
}

$role = $_SESSION['role'];
$id_unit_user = $_SESSION['id_unit'];
$nama_user = $_SESSION['nama'];

// 1. Ambil parameter filter & pencarian
$keyword = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$f_unit  = isset($_GET['filter_unit']) ? mysqli_real_escape_string($koneksi, $_GET['filter_unit']) : '';
$f_kat   = isset($_GET['filter_kategori']) ? mysqli_real_escape_string($koneksi, $_GET['filter_kategori']) : '';

// 2. Query Dasar
$query_base = "SELECT a.*, u.nama_unit, k.nama_kategori 
               FROM arsip a 
               LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
               LEFT JOIN kategori k ON a.id_kategori = k.id_kategori WHERE 1=1";

// 3. LOGIKA HAK AKSES UNIT
if ($role == 'petugas') {
  // Petugas dikunci hanya pada unitnya sendiri
  $query_base .= " AND a.id_unit = '$id_unit_user'";
}

// 4. Tambahkan Filter Pencarian & Dropdown
if ($keyword != '') {
  $query_base .= " AND (a.nama_arsip LIKE '%$keyword%' OR a.kode_arsip LIKE '%$keyword%')";
}
if ($f_unit != '' && ($role == 'admin' || $role == 'pimpinan')) {
  $query_base .= " AND a.id_unit = '$f_unit'";
}
if ($f_kat != '') {
  $query_base .= " AND a.id_kategori = '$f_kat'";
}

$query_arsip = mysqli_query($koneksi, $query_base . " ORDER BY a.created_at DESC");

// Data Dropdown
$units = mysqli_query($koneksi, "SELECT * FROM unit_kerja ORDER BY nama_unit ASC");
$kats  = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

$page = 'data_arsip.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <title>Data Arsip - SIAPSIJUNJUNG</title>
</head>

<body>
  <?php include '../partials/sidebar.php'; ?>
  <section id="content">
    <?php include '../partials/navbar.php'; ?>
    <main>
      <div class="head-title">
        <div class="left">
          <h1>Data Arsip Digital</h1>
        </div>
        <?php if ($role == 'admin' || $role == 'petugas') : ?>
          <a href="arsip_tambah.php" class="btn-add"><i class='bx bx-cloud-upload'></i><span class="text">Upload Arsip</span></a>
        <?php endif; ?>
      </div>

      <form action="" method="GET" id="filterForm" class="filter-container" style="background: var(--light); padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
        <div class="filter-group" style="display: flex; flex-direction: column; gap: 5px;">
          <label style="font-size: 12px; font-weight: 600; color: var(--dark);">Cari Arsip</label>
          <input type="text" name="search" id="searchInput" placeholder="Judul atau Kode..." value="<?= $keyword; ?>" autocomplete="off" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd; width: 200px;">
        </div>

        <?php if ($role == 'admin' || $role == 'pimpinan') : ?>
          <div class="filter-group" style="display: flex; flex-direction: column; gap: 5px;">
            <label style="font-size: 12px; font-weight: 600; color: var(--dark);">Unit Kerja</label>
            <select name="filter_unit" onchange="this.form.submit()" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
              <option value="">Semua Unit</option>
              <?php while ($u = mysqli_fetch_assoc($units)) : ?>
                <option value="<?= $u['id_unit']; ?>" <?= ($f_unit == $u['id_unit']) ? 'selected' : ''; ?>><?= $u['nama_unit']; ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        <?php endif; ?>

        <div class="filter-group" style="display: flex; flex-direction: column; gap: 5px;">
          <label style="font-size: 12px; font-weight: 600; color: var(--dark);">Kategori</label>
          <select name="filter_kategori" onchange="this.form.submit()" style="padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
            <option value="">Semua Kategori</option>
            <?php while ($k = mysqli_fetch_assoc($kats)) : ?>
              <option value="<?= $k['id_kategori']; ?>" <?= ($f_kat == $k['id_kategori']) ? 'selected' : ''; ?>><?= $k['nama_kategori']; ?></option>
            <?php endwhile; ?>
          </select>
        </div>

        <a href="data_arsip.php" class="btn-cancel" style="padding: 11px 20px; text-decoration: none; border-radius: 8px; background: #eee; color: #333; font-size: 13px;">Reset</a>
      </form>

      <div class="table-data">
        <div class="order">
          <table>
            <thead>
              <tr>
                <th>No</th>
                <th>Kode</th>
                <th>Nama Arsip</th>
                <th>Unit</th>
                <th>Tgl Upload</th>
                <th style="text-align: center;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($query_arsip) > 0) : $no = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($query_arsip)) : ?>
                  <tr>
                    <td><?= $no++; ?></td>
                    <td><span style="font-family: monospace; font-weight: bold;"><?= $row['kode_arsip']; ?></span></td>
                    <td><?= htmlspecialchars($row['nama_arsip']); ?></td>
                    <td><?= $row['nama_unit'] ?? 'GLOBAL'; ?></td>
                    <td><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                    <td style="text-align: center;">
                      <div class="btn-group-action" style="display: flex; gap: 18px; justify-content: center; align-items: center;">

                        <a href="arsip_view.php?id=<?= $row['id_arsip']; ?>"
                          style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #3C91E6;"
                          title="Lihat Detail & Preview">
                          <i class='bx bx-show-alt' style="font-size: 24px;"></i>
                          <span style="font-size: 9px; font-weight: 600;">PREVIEW</span>
                        </a>

                        <a href="arsip_download.php?id=<?= $row['id_arsip']; ?>"
                          style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #198754;"
                          title="Download Berkas">
                          <i class='bx bxs-cloud-download' style="font-size: 24px;"></i>
                          <span style="font-size: 9px; font-weight: 600;">UNDUH</span>
                        </a>

                        <?php if ($role == 'admin') : ?>
                          <div style="width: 1px; height: 25px; background: #eee; margin: 0 5px;"></div> <a href="arsip_edit.php?id=<?= $row['id_arsip']; ?>"
                            style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #FFCE26;"
                            title="Edit Data">
                            <i class='bx bxs-edit' style="font-size: 22px;"></i>
                            <span style="font-size: 9px; font-weight: 600;">EDIT</span>
                          </a>

                          <a href="arsip_hapus.php?id=<?= $row['id_arsip']; ?>"
                            onclick="return confirm('Yakin ingin menghapus arsip ini secara permanen?')"
                            style="display: flex; flex-direction: column; align-items: center; text-decoration: none; color: #DB504A;"
                            title="Hapus Data">
                            <i class='bx bxs-trash' style="font-size: 22px;"></i>
                            <span style="font-size: 9px; font-weight: 600;">HAPUS</span>
                          </a>
                        <?php endif; ?>

                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else : ?>
                <tr>
                  <td colspan="6" style="text-align: center; padding: 30px;">Data tidak ditemukan.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </section>

  <script>
    // Live Search Logic
    const searchInput = document.getElementById('searchInput');
    const filterForm = document.getElementById('filterForm');
    let typingTimer;

    searchInput.addEventListener('keyup', () => {
      clearTimeout(typingTimer);
      typingTimer = setTimeout(() => {
        filterForm.submit();
      }, 600);
    });

    // Autofocus kursor ke posisi akhir teks
    const val = searchInput.value;
    searchInput.value = '';
    searchInput.focus();
    searchInput.value = val;
  </script>
  <script src="../../assets/js/script.js"></script>
</body>

</html>