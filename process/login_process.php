<?php

session_start();

require '../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

if (!isset($koneksi)) {
    die("Terjadi kesalahan koneksi database.");
}

$username   = trim($_POST['username'] ?? '');
$password   = $_POST['password'] ?? '';
$login_type = $_POST['login_type'] ?? '';
$role_input = $_POST['role'] ?? '';

if ($username === '' || $password === '') {

    echo "<script>
            alert('Username dan Password wajib diisi.');
            window.location='../index.php';
          </script>";
    exit;
}

if ($login_type === 'unit') {
    $allowed_roles = ['petugas', 'pimpinan'];

    if (!in_array($role_input, $allowed_roles, true)) {
        echo "<script>
                alert('Silakan pilih peran Petugas atau Pimpinan.');
                window.location='../index.php';
              </script>";
        exit;
    }
}

$sql = "SELECT
            u.*,
            uk.nama_unit
        FROM users u
        LEFT JOIN unit_kerja uk
            ON u.id_unit = uk.id_unit
        WHERE u.username = ?
        LIMIT 1";

$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {

    die("Terjadi kesalahan sistem.");
}

mysqli_stmt_bind_param($stmt, "s", $username);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$data || !password_verify($password, $data['password'])) {

    echo "<script>
            alert('Username atau password salah!');
            window.location='../index.php';
          </script>";

    exit;
}

if ($data['status'] !== 'aktif') {

    echo "<script>
            alert('Akun Anda telah dinonaktifkan.');
            window.location='../index.php';
          </script>";

    exit;
}

if ($login_type === 'admin') {
    if ($data['role'] !== 'admin') {
        echo "<script>
                alert('Akun ini bukan akun Administrator. Silakan login melalui halaman Login Unit.');
                window.location='../index.php';
              </script>";
        exit;
    }
} elseif ($login_type === 'unit') {
    if ($data['role'] !== $role_input) {
        echo "<script>
                alert('Peran yang dipilih tidak sesuai dengan akun Anda!');
                window.location='../index.php';
              </script>";
        exit;
    }
} else {
    echo "<script>
            alert('Jenis login tidak valid.');
            window.location='../index.php';
          </script>";
    exit;
}

session_unset();

session_regenerate_id(true);

$_SESSION['login']      = true;
$_SESSION['id_user']    = $data['id_user'];
$_SESSION['username']   = $data['username'];
$_SESSION['nama']       = $data['nama_lengkap'];
$_SESSION['role']       = $data['role'];
$_SESSION['id_unit']    = $data['id_unit'];
$_SESSION['foto']       = $data['foto'];
$_SESSION['nama_unit']  = $data['nama_unit'] ?? 'Tanpa Unit';

catat_log(
    $koneksi,
    $data['id_user'],
    'Login',
    'Akses Masuk Sistem'
);

switch ($data['role']) {

    case 'admin':
        header("Location: ../pages/admin/dashboard.php");
        break;

    case 'pimpinan':
        header("Location: ../pages/pimpinan/dashboard.php");
        break;

    case 'petugas':
        header("Location: ../pages/petugas/dashboard.php");
        break;

    default:

        session_destroy();

        header("Location: ../index.php");
        break;
}

exit;