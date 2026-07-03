<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
if (!isset($koneksi)) {
    die("Database connection error. Please check your 'koneksi.php' file.");
}

// Query Join untuk mengambil Nama Unit Kerja
$query_user = mysqli_query($koneksi, "SELECT users.*, unit_kerja.nama_unit 
                                      FROM users 
                                      LEFT JOIN unit_kerja ON users.id_unit = unit_kerja.id_unit 
                                      ORDER BY users.role ASC");
$page = 'user.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Data User - SIAPSIJUNJUNG</title>
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

        /* Styling Breadcrumb agar Sejajar */
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

        /* CONTAINER PANEL BOX UTAMA + INTERNAL SCROLL AREA */
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

        /* GRUP ACTION FLEXBOX SINKRON WARNA REVISI USER */
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
                    <h1>Manajemen User</h1>
                    <ul class="breadcrumb">
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li><a class="active" href="#">Data User</a></li>
                    </ul>
                </div>
                <a href="user_tambah.php" class="btn-add" style="text-decoration: none;">
                    <i class='bx bx-user-plus'></i>
                    <span class="text">Tambah User</span>
                </a>
            </div>

            <div class="table-data">
                <div style="flex-shrink: 0; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 16px; font-weight: 600; color: var(--dark);">Daftar Pengguna Sistem</h3>
                    <small style="color: var(--dark-grey); font-size: 12px;">Total Akun: <strong><?= mysqli_num_rows($query_user); ?></strong> User</small>
                </div>

                <div class="table-scroll-area">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th style="width: 70px; text-align: center;">Profil</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Unit Kerja</th>
                                <th>Role</th>
                                <th style="text-align: center;">Status</th>
                                <th style="width: 180px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_user) > 0) : ?>
                                <?php while($row = mysqli_fetch_assoc($query_user)) : ?>
                                <tr>
                                    <td style="text-align: center;">
                                        <img src="../../assets/img/profiles/<?= !empty($row['foto']) ? $row['foto'] : 'default.jpg'; ?>" 
                                             style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-color);">
                                    </td>
                                    <td style="font-family: monospace; font-weight: bold;"><?= htmlspecialchars($row['username']); ?></td>
                                    <td style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?= !empty($row['nama_unit']) ? htmlspecialchars($row['nama_unit']) : '<span style="color:orange;">-</span>'; ?></td>
                                    <td><span class="status <?= $row['role']; ?>"><?= ucfirst($row['role']); ?></span></td>
                                    <td style="text-align: center;">
                                        <span style="color: <?= $row['status'] == 'aktif' ? '#4cbb17' : 'rgb(235, 22, 22)'; ?>; font-weight: bold; font-size: 12px;">
                                            <?= strtoupper($row['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-flex-group">
                                            <a href="user_edit.php?id=<?= $row['id_user']; ?>" class="btn-action-edit">
                                                <i class='bx bxs-edit'></i><span>Edit</span>
                                            </a>
                                            <a href="user_hapus.php?id=<?= $row['id_user']; ?>" class="btn-action-delete" onclick="return confirm('Yakin ingin menghapus akun user ini secara permanen?')">
                                                <i class='bx bxs-trash'></i><span>Hapus</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--dark-grey); padding: 30px; font-style: italic;">Belum ada data user terdaftar.</td>
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