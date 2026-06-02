<?php
session_start();
// Pastikan hanya Admin yang bisa akses halaman ini
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../../index.php");
  exit;
}

include "../../config/koneksi.php";

// Ensure $koneksi is defined and the connection is successful
if (!isset($koneksi) || !$koneksi) {
    die("Database connection failed.");
}

// 5 aktivitas terbaru - Sekarang mengambil Nama Unit juga
$query_activity = "SELECT a.*, u.nama_unit FROM arsip a 
                   LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
                   ORDER BY a.id_arsip DESC LIMIT 5";
$recent_activity = mysqli_query($koneksi, $query_activity);

// Hitung statistik untuk Box Info (Menambahkan Unit Kerja)
$count_arsip = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_arsip FROM arsip"));
$count_user = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_user FROM users"));
$count_unit = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_unit FROM unit_kerja"));
$count_kategori = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_kategori FROM kategori"));

$page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <title>Dashboard - SIAPuskesmas</title>

  <style>
    .info-data {
      display: grid;
      grid-template-columns: 1.2fr 1fr 1fr;
      gap: 24px;
      margin-top: 20px;
      align-items: flex-start;
    }

    .table-data {
      margin-top: 0;
      background: var(--white-card);
      padding: 25px;
      border-radius: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Kartu Shortcut */
    .quick-access {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
    }

    .access-card {
      background: var(--white-card);
      padding: 25px;
      border-radius: 12px;
      text-align: center;
      transition: 0.3s;
      border: 1px solid var(--border-color);
    }

    .access-card:hover {
      transform: translateY(-5px);
      background: var(--green);
      color: #fff;
    }

    .access-card i {
      font-size: 30px;
      margin-bottom: 10px;
    }

    .text h3 {
      font-size: 24px;
      color: var(--dark);
    }

    .text p {
      font-size: 14px;
      color: var(--dark);
    }

    .breadcrumb {
      display: flex;
      align-items: center;
      grid-gap: 12px;
      padding: 0;
      margin: 0;
    }

    .breadcrumb li {
      color: var(--dark);
      font-size: 14px;
    }

    .breadcrumb li a {
      color: var(--dark-grey);
      transition: all 0.3s ease;
      font-weight: 500;
    }

    .breadcrumb li a.active {
      color: var(--green);
      pointer-events: none;
      font-weight: 600;
    }

    .breadcrumb li a:hover {
      color: var(--green);
    }

    .breadcrumb li i {
      color: var(--dark-grey);
      font-size: 16px;
      vertical-align: middle;
    }
  </style>
</head>

<body>
  <?php include '../partials/sidebar.php'; ?>

  <section id="content">
    <?php include '../partials/navbar.php'; ?>

    <main>
      <div class="head-title">
        <div class="left">
          <h1>Dashboard Admin</h1>
          <p style="color: var(--dark-grey); font-size: 16px; margin-top: 4px;">
            Selamat datang Administrator, <span style="color: var(--green); font-weight: 600;">
              <?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin'; ?>
            </span>! 👋
          </p>
          </p>
        </div>
        <div class="right" style="text-align: right;">
          <h4 id="clock" style="color: var(--dark); margin: 0;"></h4>
          <p style="color: var(--dark-grey); font-size: 12px;"><?= date('l, d F Y') ?></p>
        </div>
      </div>

      <ul class="box-info">
        <li>
          <i class='bx bxs-file-archive'></i>
          <span class="text">
            <h3><?= $count_arsip; ?></h3>
            <p>Total Arsip</p>
          </span>
        </li>
        <li>
          <i class='bx bxs-group'></i>
          <span class="text">
            <h3><?= $count_user; ?></h3>
            <p>Pengguna</p>
          </span>
        </li>
        <li>

          <i class='bx bxs-city'></i>
          <span class="text">
            <h3><?= $count_unit; ?></h3>
            <p>Unit Kerja</p>
          </span>
      </ul>

      <div class="info-data">

        <div class="table-data">
          <div class="head" style="margin-bottom: 15px;">
            <h3 style="font-size: 18px; color: var(--dark);">Aktivitas Global Unit</h3>
          </div>
          <ul class="activity-list" style="list-style: none; padding: 0;">
            <?php while ($act = mysqli_fetch_assoc($recent_activity)): ?>
              <li style="display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border-color);">
                <div style="width: 35px; height: 35px; background: var(--light-green); color: var(--green); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                  <i class='bx bx-upload' style="font-size: 18px;"></i>
                </div>
                <div style="overflow: hidden; width: 100%;">
                  <p style="font-size: 13px; margin: 0; color: var(--dark); text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                    <strong><?= htmlspecialchars($act['nama_unit'] ?? 'Admin'); ?>:</strong> <?= htmlspecialchars($act['nama_arsip']) ?>
                  </p>
                  <small style="color: var(--dark-grey); font-size: 11px;">
                    <i class='bx bx-time'></i> <?= date('l, d F Y', strtotime($act['created_at'])) ?>
                  </small>
                </div>
              </li>
            <?php endwhile; ?>
          </ul>
        </div>

        <div class="quick-access">
          <a href="data_user.php" class="access-card">
            <i class='bx bx-user-plus' style="color: var(--green);"></i>
            <p>Tambah User</p>
          </a>
          <a href="data_unit.php" class="access-card">
            <i class='bx bx-buildings' style="color: var(--orange);"></i>
            <p>Unit Kerja</p>
          </a>
          <a href="laporan_arsip.php" class="access-card">
            <i class='bx bxs-printer' style="color: #3C91E6;"></i>
            <p>Cetak Laporan</p>
          </a>
          <a href="riwayat_unduhan.php" class="access-card">
            <i class='bx bx-history' style="color: var(--red);"></i>
            <p>Log Unduhan</p>
          </a>
        </div>
      </div>
    </main>
  </section>
  <script src="../../assets/js/script.js"></script>
</body>

</html>