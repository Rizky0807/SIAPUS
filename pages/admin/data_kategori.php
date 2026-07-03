<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

include "../../config/koneksi.php";
if (!isset($koneksi)) {
    die("Database connection error.");
}

// Proses Hapus Kategori
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    $delete = mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = '$id'");
    if ($delete) {
        echo "<script>alert('Kategori berhasil dihapus!'); window.location='data_kategori.php';</script>";
        exit;
    }
}

$page = 'data_kategori.php';

// 💡 QUERY AGREGASI: Menghitung total arsip yang menggunakan kategori ini secara real-time
$query_string = "SELECT k.*, COUNT(a.id_arsip) as total_arsip 
                 FROM kategori k 
                 LEFT JOIN arsip a ON k.id_kategori = a.id_kategori 
                 GROUP BY k.id_kategori 
                 ORDER BY k.nama_kategori ASC";
$query_kategori = mysqli_query($koneksi, $query_string);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Data Kategori - SIAPSIJUNJUNG</title>
    <style>
        /* 💡 LOCK LAYOUT SATU HALAMAN PENUH DESKTOP SINKRON SIAPUS */
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

        /* Breadcrumb Navigasi */
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
            text-decoration: none;
            font-size: 14px;
        }

        .breadcrumb li a.active {
            color: var(--dark-grey); 
            font-weight: 600;
        }

        .breadcrumb li i {
            font-size: 18px;
            color: var(--dark-grey);
        }

        /* CONTAINER PANEL BOX UTAMA + SCROLL AREA */
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
            background: var(--light-bg, #f8fafc);
        }

        /* GRUP ACTION FLEXBOX SESUAI WARNA REVISI USER */
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
            <div class="head-title">
                <div class="left">
                    <h1>Data Kategori</h1>
                    <ul class="breadcrumb">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Kategori</a></li>
                    </ul>
                </div>
                <a href="kategori_tambah.php" class="btn-add" style="text-decoration: none;">
                    <i class='bx bx-plus'></i>
                    <span class="text">Tambah Kategori</span>
                </a>
            </div>

            <div class="table-data">
                <div style="flex-shrink: 0; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 16px; font-weight: 600; color: var(--dark);">Daftar Kategori Arsip Digital</h3>
                    <small style="color: var(--dark-grey); font-size: 12px;">Total Kategori: <strong><?= mysqli_num_rows($query_kategori); ?></strong> Entitas</small>
                </div>

                <div class="table-scroll-area">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th style="width: 60px; text-align: center;">No</th>
                                <th>Nama Kategori Arsip</th>
                                <th style="width: 200px; text-align: center;">Total Berkas Terkait</th>
                                <th style="width: 200px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if (mysqli_num_rows($query_kategori) > 0) :
                                while($row = mysqli_fetch_assoc($query_kategori)) : 
                            ?>
                            <tr>
                                <td style="text-align: center; color: var(--dark-grey);"><?= $no++; ?></td>
                                <td style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($row['nama_kategori']); ?></td>
                                <td style="text-align: center;">
                                    <strong style="color: #4cbb17;"><?= $row['total_arsip']; ?></strong> Berkas
                                </td>
                                <td style="text-align: center;">
                                    <div class="action-flex-group">
                                        <a href="kategori_edit.php?id=<?= $row['id_kategori']; ?>" class="btn-action-edit">
                                            <i class='bx bxs-edit'></i><span>Edit</span>
                                        </a>
                                        <a href="data_kategori.php?hapus=<?= $row['id_kategori']; ?>" class="btn-action-delete" onclick="return confirm('Menghapus kategori ini akan mengosongkan status kategori berkas terkait. Yakin?')">
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
                                <td colspan="4" style="text-align: center; color: var(--dark-grey); padding: 30px; font-style: italic;">
                                    Belum ada data kategori arsip terdaftar.
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