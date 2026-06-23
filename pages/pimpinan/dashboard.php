<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'pimpinan') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
/** @var mysqli $koneksi */

// 1. Hitung total semua arsip
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM arsip");
$total_arsip = mysqli_fetch_assoc($q_total)['total'];

// 2. Hitung total unit kerja
$q_unit = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM unit_kerja");
$total_unit = mysqli_fetch_assoc($q_unit)['total'];

// 3. Data Rekap per Unit dengan Perhitungan Kontribusi Arsip
$rekap_unit = mysqli_query($koneksi, "SELECT uk.nama_unit, COUNT(a.id_arsip) as jml 
                                      FROM unit_kerja uk 
                                      LEFT JOIN arsip a ON uk.id_unit = a.id_unit 
                                      GROUP BY uk.id_unit 
                                      ORDER BY jml DESC");

// Siapkan array kosong untuk menampung data yang akan dilempar ke Grafik JavaScript
$chart_labels = [];
$chart_data = [];

$page = 'dashboard.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Dashboard Pimpinan - SIAPSIJUNJUNG</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* CSS tambahan agar layout tabel dan grafik berdampingan rapi */
        .dashboard-layout {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .table-container {
            flex: 2;
            min-width: 400px;
        }
        .chart-container {
            flex: 1;
            min-width: 300px;
            background: var(--light);
            padding: 20px;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
                    <h1>Dashboard Pimpinan</h1>
                    <p style="color: var(--dark-grey); font-size: 16px; margin-top: 4px;">
            Selamat datang , <span style="color: var(--green); font-weight: 600;">
              <?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Admin'; ?>
            </span>! 👋
          </p>
                </div>
            </div>

            <ul class="box-info">
                <li>
                    <i class='bx bxs-file-archive'></i>
                    <span class="text">
                        <h3 style="color: var(--dark)"><?= $total_arsip; ?></h3>
                        <p style="color: var(--dark);">Total Seluruh Arsip</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-buildings'></i>
                    <span class="text">
                        <h3 style="color: var(--dark)"><?= $total_unit; ?></h3>
                        <p style="color: var(--dark)">Total Unit Kerja</p>
                    </span>
                </li>
            </ul>

            <div class="dashboard-layout">
                
                <div class="table-container table-data" style="margin-top: 0; padding: 0;">
                    <div class="order">
                        <div class="head">
                            <h3 style="color: var(--dark)">Rekapitulasi Jumlah Data Arsip per Unit</h3>
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Unit Kerja</th>
                                    <th>Jumlah Dokumen</th>
                                    <th width="120">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                while($row = mysqli_fetch_assoc($rekap_unit)) : 
                                    $persen = ($total_arsip > 0) ? round(($row['jml'] / $total_arsip) * 100, 1) : 0;
                                    
                                    // Masukkan data ke array untuk grafik
                                    $chart_labels[] = $row['nama_unit'];
                                    $chart_data[] = $row['jml'];
                                ?>
                                <tr>
                                    <td>
                                        <p style="font-weight: 600; color: var(--dark);"><?= htmlspecialchars($row['nama_unit']); ?></p>
                                    </td>
                                    <td><strong><?= $row['jml']; ?></strong> Berkas</td>
                                    <td>
                                        <span class="status completed" style="display: inline-block; width: 70px; text-align: center; font-weight: 600;">
                                            <?= $persen; ?>%
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="chart-container">
                    <h3 style="font-size: 16px; font-weight: 600; margin-bottom: 15px; color: var(--dark); text-align: center;">Visualisasi Data Rekapitulasi</h3>
                    <div style="width: 100%; max-width: 260px;">
                        <canvas id="kontribusiChart"></canvas>
                    </div>
                </div>

            </div>
        </main>
    </section>

    <script>
        const ctx = document.getElementById('kontribusiChart').getContext('2d');
        
        // Mengambil data dari array PHP yang sudah diisi di dalam perulangan tadi
        const labelsData = <?php echo json_encode($chart_labels); ?>;
        const totalData = <?php echo json_encode($chart_data); ?>;

        new Chart(ctx, {
            type: 'pie', // Tipe grafik lingkaran
            data: {
                labels: labelsData,
                datasets: [{
                    data: totalData,
                    backgroundColor: [
                        '#3C91E6', // Blue bawaan template
                        '#FF9F43', // Orange bawaan template
                        '#28C76F', // Green
                        '#EA5455', // Red
                        '#9F5F80', // Purple
                        '#7367F0', // Indigo
                        '#CDECF8'  // Light Blue
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom', // Letakkan keterangan label di bawah grafik
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    </script>
    <script src="../../assets/js/script.js"></script>
</body>
</html>