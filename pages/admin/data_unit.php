<?php
session_start();
// Proteksi halaman: Hanya Admin yang boleh masuk
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";
if (!isset($koneksi)) {
    die("Koneksi ke database gagal. Periksa konfigurasi koneksi.");
}
$page = 'data_unit.php';

// 💡 QUERY SINKRON DB: Mengambil data kode_unit dan penanggung_jawab yang baru ditambahkan
$query_string = "SELECT uk.*, 
                        COUNT(DISTINCT a.id_arsip) as total_berkas,
                        COUNT(DISTINCT u.id_user) as total_petugas
                 FROM unit_kerja uk
                 LEFT JOIN arsip a ON uk.id_unit = a.id_unit
                 LEFT JOIN users u ON uk.id_unit = u.id_unit AND u.role = 'petugas'
                 GROUP BY uk.id_unit
                 ORDER BY uk.nama_unit ASC";

$query_unit = mysqli_query($koneksi, $query_string);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css"> 
    <title>Data Unit Kerja - SIAPSIJUNJUNG</title>
    <style>
        /* Styling Layout agar Mengunci Satu Layar Rapi */
        html, body {
            height: 100vh;
            overflow: hidden !important;
        }

        #content main {
            height: calc(100vh - 56px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            padding: 24px;
            box-sizing: border-box;
        }

        .head-title {
            flex-shrink: 0;
            margin-bottom: 20px !important;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            grid-gap: 10px; 
            margin-top: 10px;
        }

        .breadcrumb li {
            color: var(--dark);
            list-style: none; 
            display: flex;
            align-items: center;
        }

        .breadcrumb li a {
            color: var(--dark-grey);
            font-size: 14px;
            text-decoration: none;
        }

        .breadcrumb li a.active {
            color: var(--dark-grey); 
            font-weight: 600;
        }

        .breadcrumb li i {
            font-size: 18px;
            color: var(--dark-grey);
        }

        /* PANEL TABEL DENGAN INTERNAL SCROLL BAR */
        .table-data {
            background: var(--white-card, #fff);
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            min-height: 0;
        }

        .table-scroll-area {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 5px;
            margin-top: 15px;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        .modern-table th {
            position: sticky;
            top: 0;
            background: var(--white-card, #fff);
            z-index: 10;
            padding: 12px 15px;
            font-size: 13px;
            color: var(--dark-grey);
            border-bottom: 2px solid var(--border-color);
        }

        .modern-table td {
            padding: 14px 15px;
            font-size: 14px;
            border-bottom: 1px solid var(--border-color);
            color: var(--dark);
        }

        .modern-table tr:hover td {
            background: var(--light-bg);
        }

        /* Badge Penanda Kode Unit agar Estetik */
        .badge-code {
            font-family: monospace;
            font-weight: 700;
            background: rgba(58, 185, 62, 0.1);
            color: rgb(58, 185, 62);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }

        /* GRUP ACTION FLEXBOX */
        .action-flex-group {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .btn-action-edit {
            background: rgba(58, 185, 62, 0.1);
            color: rgb(58, 185, 62);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: 0.2s;
        }

        .btn-action-edit:hover {
            background: #4cbb17;
            color: #fff;
        }

        .btn-action-delete {
            background: rgba(235, 22, 22, 0.23);
            color: rgb(235, 22, 22);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: 0.2s;
        }

        .btn-action-delete:hover {
            background: #EA5455;
            color: #fff;
        }

        .table-scroll-area::-webkit-scrollbar {
            width: 5px;
        }
        .table-scroll-area::-webkit-scrollbar-thumb {
            background: var(--dark-grey);
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include '../partials/sidebar.php'; ?>

    <section id="content">
        <?php include '../partials/navbar.php'; ?>

        <main>
            <!-- HEAD TITLE -->
            <div class="head-title">
                <div class="left">
                    <h1>Data Unit Kerja Puskesmas Sijunjung</h1>
                    <ul class="breadcrumb">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Unit Kerja</a></li>
                    </ul>
                </div>
                <a href="unit_tambah.php" class="btn-add" style="text-decoration: none;">
                    <i class='bx bx-plus'></i>
                    <span class="text">Tambah Unit</span>
                </a>
            </div>

            <!-- CONTAINER DATA MODERN -->
            <div class="table-data">
                <div style="flex-shrink: 0; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 16px; font-weight: 600; color: var(--dark);">Daftar Unit Kerja Puskesmas</h3>
                    <small style="color: var(--dark-grey); font-size: 12px;">Total Terdaftar: <strong><?= mysqli_num_rows($query_unit); ?></strong> Unit</small>
                </div>
                
                <div class="table-scroll-area">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th style="width: 50px; text-align: center;">No</th>
                                <th style="width: 120px;">Kode Unit</th>
                                <th>Nama Unit Kerja</th>
                                <th>Penanggung Jawab</th>
                                <th style="width: 130px; text-align: center;">Jumlah Petugas</th>
                                <th style="width: 130px; text-align: center;">Total Berkas</th>
                                <th style="width: 180px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if(mysqli_num_rows($query_unit) > 0) :
                                while($row = mysqli_fetch_assoc($query_unit)) : 
                            ?>
                            <tr>
                                <td style="text-align: center; color: var(--dark-grey);"><?= $no++; ?></td>
                                <!-- 💡 MENAMPILKAN KODE UNIT -->
                                <td><span class="badge-code"><?= htmlspecialchars($row['kode_unit']); ?></span></td>
                                <td style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($row['nama_unit']); ?></td>
                                <!-- 💡 MENAMPILKAN PENANGGUNG JAWAB -->
                                <td><?= htmlspecialchars($row['penanggung_jawab']); ?></td>
                                <td style="text-align: center;">
                                    <span class="badge-info-unit"><?= $row['total_petugas']; ?></span> Orang
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge-info-unit" style="color: #4cbb17;"><?= $row['total_berkas']; ?></span> Berkas
                                </td>
                                <td>
                                    <div class="action-flex-group">
                                        <a href="unit_edit.php?id=<?= $row['id_unit']; ?>" class="btn-action-edit">
                                            <i class='bx bxs-edit'></i><span>Edit</span>
                                        </a>
                                        <a href="unit_hapus.php?hapus=<?= $row['id_unit']; ?>" class="btn-action-delete" onclick="return confirm('Menghapus unit akan menghapus otomatis seluruh data akun petugas & berkas di unit ini secara permanen. Yakin?')">
                                            <i class='bx bxs-trash'></i><span>Hapus</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            else: 
                            ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: var(--dark-grey); padding: 30px; font-style: italic;">
                                    Belum ada data unit kerja terdaftar di sistem.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>
</html>