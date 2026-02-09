<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";

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
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Data User - SIAPSIJUNJUNG</title>
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
                <a href="user_tambah.php" class="btn-add">
                    <i class='bx bx-user-plus'></i>
                    <span class="text">Tambah User</span>
                </a>
            </div>

            <div class="table-data">
                <div class="order">
                    <table>
                        <thead>
                            <tr>
                                <th>Profil</th>
                                <th>Username</th>
                                <th>Nama Lengkap</th>
                                <th>Unit Kerja</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($query_user)) : ?>
                            <tr>
                                <td>
                                    <img src="../../assets/img/profiles/<?= !empty($row['foto']) ? $row['foto'] : 'default.jpg'; ?>" 
                                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                </td>
                                <td><?= $row['username']; ?></td>
                                <td><?= $row['nama_lengkap']; ?></td>
                                <td><?= $row['nama_unit'] ?? '<span style="color:orange;">GLOBAL (ADMIN)</span>'; ?></td>
                                <td><span class="status <?= $row['role']; ?>"><?= ucfirst($row['role']); ?></span></td>
                                <td>
                                    <span style="color: <?= $row['status'] == 'aktif' ? 'green' : 'red'; ?>; font-weight: bold;">
                                        <?= strtoupper($row['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group-action">
                                        <a href="user_edit.php?id=<?= $row['id_user']; ?>" class="btn-group-item">
                                            <i class='bx bxs-edit'></i>
                                        </a>
                                        <a href="user_hapus.php?id=<?= $row['id_user']; ?>" class="btn-group-item btn-delete" onclick="return confirm('Yakin hapus user ini?')">
                                            <i class='bx bxs-trash'></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>
    <script src="../../assets/js/script.js"></script>
</body>
</html>