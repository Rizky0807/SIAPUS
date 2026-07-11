<?php
session_start();
if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'petugas') {
    header("Location: ../../index.php");
    exit;
}
include "../../config/koneksi.php";
/** @var mysqli $koneksi */

$id_unit = $_SESSION['id_unit'];
$nama_user = $_SESSION['nama'];


// 1. Hitung total arsip di unit ini saja (Sesuai kolom tabel arsip)
$q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM arsip WHERE id_unit = '$id_unit'");
$total_arsip = mysqli_fetch_assoc($q_total)['total'];

// 2. Hitung jumlah kategori yang digunakan oleh unit ini
$q_kat = mysqli_query($koneksi, "SELECT COUNT(DISTINCT id_kategori) as jml_kat FROM arsip WHERE id_unit = '$id_unit'");
$total_kat = mysqli_fetch_assoc($q_kat)['jml_kat'];

// 3. Ambil Arsip terbaru dari unit ini (Join ke tabel kategori)
$arsip_terbaru = mysqli_query($koneksi, "SELECT a.*, k.nama_kategori 
                                         FROM arsip a 
                                         JOIN kategori k ON a.id_kategori = k.id_kategori 
                                         WHERE a.id_unit = '$id_unit' 
                                         ORDER BY a.created_at DESC LIMIT 15");

// 💡 4. QUERY GRAFIK AKURAT: Rekapitulasi Pembagian Kategori Khusus Internal Unit Sesuai db_siapus
$query_all_kat_unit = "SELECT k.nama_kategori, COUNT(a.id_arsip) as total_arsip 
                       FROM kategori k 
                       LEFT JOIN arsip a ON k.id_kategori = a.id_kategori AND a.id_unit = '$id_unit'
                       GROUP BY k.id_kategori
                       HAVING total_arsip > 0
                       ORDER BY total_arsip DESC";
$result_kat_unit = mysqli_query($koneksi, $query_all_kat_unit);

$kat_graph_indexes = [];
$kat_percentages = [];
$kat_totals = [];
$kat_names_real = [];
$kat_legend_data = [];

$color_palette = ['#4ECE3D', '#3C91E6', '#FFB534', '#FF6B6B', '#9b59b6', '#ff9f40', '#e84393', '#00cec9', '#6c5ce7', '#fdcb6e'];

$nomor_indeks = 1;
while ($row_kat = mysqli_fetch_assoc($result_kat_unit)) {
    $kat_graph_indexes[] = $nomor_indeks;
    $jumlah_k = (int)$row_kat['total_arsip'];
    $kat_totals[] = $jumlah_k;
    $kat_names_real[] = $row_kat['nama_kategori'];

    $persen_k = ($total_arsip > 0) ? round(($jumlah_k / $total_arsip) * 100, 1) : 0;
    $kat_percentages[] = $persen_k;
    $assigned_color = $color_palette[($nomor_indeks - 1) % count($color_palette)];

    $kat_legend_data[] = [
        'indeks' => $nomor_indeks,
        'nama' => $row_kat['nama_kategori'],
        'warna' => $assigned_color,
        'persen' => $persen_k
    ];
    $nomor_indeks++;
}

$page = 'dashboard.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/boxicons-2.1.4/css/boxicons.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <title>Dashboard Petugas - SIAPSIJUNJUNG</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <style>
        /* 💡 AMANKAN VIEWPORT: Membuka jalur scroll internal saat zoom in ekstrim */
        html,
        body {
            min-height: 100vh;
            overflow-y: auto !important;
        }

        #content main {
            min-height: calc(100vh - 56px);
            display: flex;
            flex-direction: column;
            padding: 24px;
            box-sizing: border-box;
        }

        /* 💡 REVISI HERO WRAPPER: Menyatukan Banner & Card statistik dalam 1 background foto puskesmas */
        .hero-container {
            flex-shrink: 0;
            margin-bottom: 24px !important;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7) 0%, rgba(67, 66, 66, 0.47) 50%, rgba(17, 153, 19, 0.67) 100%),
                url('../../assets/img/puskesmas2.png');
            background-size: cover;
            background-position: center;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Hilangkan background & style banner lama karena sudah dilingkupi container baru */
        .head-title {
            background: transparent !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin-bottom: 20px !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .head-title .left h1 {
            color: #ffffff !important;
            font-size: 26px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .head-title .left p {
            color: #f1f5f9 !important;
            font-size: 14px;
            margin-top: 4px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        }

        /* Penataan Box Info di dalam container hero */
        .box-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            grid-gap: 20px !important;
            margin-top: 0px !important;
            margin-bottom: 5px !important;
        }

        /* 💡 ESTETIK GLASSMORPHISM: Card statistik semi-transparan putih mewah */
        .box-info li {
            padding: 15px 20px !important;
            background: rgba(255, 255, 255, 0.92) !important;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 15px !important;
            display: flex;
            align-items: center;
            grid-gap: 20px !important;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08) !important;
            transition: transform 0.3s ease;
        }

        .box-info li:hover {
            transform: translateY(-3px);
        }

        /* 💡 KUNCI STRATEGI BARU: Memaksa sejajar lurus dari container induk */
        .info-data {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 24px;
            flex-grow: 1;
            min-height: 0;
            /* 🛠 nighttime fix: Paksa kepala grid kiri & kanan sejajar rata atas satu garis */
            align-items: start !important;
            /* Jarak pas antara card statistik di atas dengan panel data di bawah */
            margin-top: 20px !important;
        }

        @media (max-width: 1100px) {
            .info-data {
                grid-template-columns: 1fr;
            }
        }

        /* 🎨 STYLE TABEL MODERN (RATA ATAS & SAMAKAN PADDING) */
        .table-data {
            background: var(--white-card, #fff);
            /* 🛠️ DISERAGAMKAN: Atas-bawah 24px, kiri-kanan 25px */
            padding: 24px 25px !important;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            width: 100%;
            margin-top: 0px !important;
            /* Reset margin bawaan style.css */
        }

        .activity-scroll-area {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 5px;
            max-height: 380px;
        }

        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }

        .modern-table th {
            position: sticky;
            top: 0;
            background: var(--white-card, #fff);
            z-index: 10;
            text-align: left;
            padding: 12px 15px;
            font-size: 13px;
            color: var(--dark-grey);
            border-bottom: 2px solid var(--border-color);
        }

        .modern-table td {
            padding: 14px 15px;
            font-size: 13px;
            border-bottom: 1px solid var(--border-color);
            color: var(--dark);
        }

        .badge-cat {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            color: var(--dark);
        }

        .right-dashboard-panel {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* 🎨 STYLE GRAFIK MODERN (SAMAKAN PADDING ATAS AGAR SEJAJAR) */
        .chart-card {
            background: var(--white-card, #fff);
            /* 🛠️ DISERAGAMKAN: Wajib sama persis dengan .table-data biar teks judul sejajar lurus */
            padding: 24px 25px !important;
            border-radius: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            width: 100%;
        }

        /* 🛠️ KUNCI SINKRONISASI BARIS JUDUL INTERN */
        .table-data .head,
        .chart-card .head {
            height: 32px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            margin-bottom: 20px !important;
            flex-shrink: 0 !important;
            box-sizing: border-box !important;
            width: 100%;
        }

        .chart-container-wrapper {
            flex-grow: 1;
            min-height: 200px;
            position: relative;
            width: 100%;
        }

        .chart-caption-box {
            margin-top: 15px;
            padding: 12px;
            background: var(--light, #f8fafc);
            border-top: 3px solid #4cbb17;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .legend-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            max-height: 140px;
            overflow-y: auto;
        }

        .legend-item {
            font-size: 12px;
            color: var(--dark-grey);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-right: 5px;
        }

        .color-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        .legend-badge {
            font-weight: 700;
        }

        .activity-scroll-area::-webkit-scrollbar,
        .legend-grid::-webkit-scrollbar {
            width: 5px;
        }

        .activity-scroll-area::-webkit-scrollbar-thumb,
        .legend-grid::-webkit-scrollbar-thumb {
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
            <div class="hero-container">

                <div class="head-title">
                    <div class="left">
                        <h1>Dashboard Petugas</h1>
                        <p style="color: #f1f5f9 !important; font-size: 14px; margin-top: 4px; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">
                            Selamat Datang, <strong style="color: #ffffff;"><?= $nama_user; ?></strong> | Unit <span style="background: rgba(130, 237, 122, 0.2); color: #ffffff; padding: 2px 10px; border-radius: 20px; font-weight: 700; border: 1px solid rgba(130, 237, 122, 0.3);"><?= $_SESSION['nama_unit'] ?? 'Unit Kerja'; ?></span>
                        </p>
                    </div>
                </div>

                <ul class="box-info">
                    <li>
                        <i class='bx bxs-file-pdf'></i>
                        <span class="text">
                            <h3><?= $total_arsip; ?></h3>
                            <p>Total Arsip Unit Anda</p>
                        </span>
                    </li>
                    <li>
                        <i class='bx bxs-category'></i>
                        <span class="text">
                            <h3><?= $total_kat; ?></h3>
                            <p>Kategori Terpakai</p>
                        </span>
                    </li>
                </ul>

            </div>
            <div class="info-data">
                <div class="table-data">
                    <div class="head">
                        <h3 style="font-size: 16px; color: var(--dark); font-weight: 600;">Arsip Terbaru Unit Anda</h3>
                        <small style="color: var(--dark-grey); font-size: 11px;">Data Internal</small>
                    </div>

                    <div class="activity-scroll-area">
                        <table class="modern-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Arsip</th>
                                    <th>Kategori</th>
                                    <th>Tanggal Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($arsip_terbaru) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($arsip_terbaru)) : ?>
                                        <tr>
                                            <td><span style="font-family: monospace; font-weight: bold; color: var(--dark);"><?= $row['kode_arsip']; ?></span></td>
                                            <td style="max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= htmlspecialchars($row['nama_arsip']); ?></td>
                                            <td><span class="badge-cat" style="background: var(--light-green); color: var(--green); padding: 2px 8px; border-radius: 20px; font-size: 11px;"><?= htmlspecialchars($row['nama_kategori']); ?></span></td>
                                            <td style="color: var(--dark-grey); font-size: 12px;"><?= date('d/m/Y', strtotime($row['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; color: var(--dark-grey); padding: 30px;">Unit Anda belum mengunggah berkas.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="right-dashboard-panel">
                    <div class="chart-card">
                        <div class="head">
                            <h3 style="font-size: 16px; color: var(--dark); font-weight: 600;">Persentase Distribusi Kategori</h3>
                        </div>

                        <div class="chart-container-wrapper">
                            <canvas id="cleanLineChart"></canvas>
                        </div>

                        <div class="chart-caption-box">
                            <div class="legend-grid">
                                <div class="head">
                                    <h3 style="font-size: 13px; color: var(--dark); font-weight: 600;"><i class='bx bx-paint' style="padding-right:5px; color: #4cbb17; vertical-align: middle; font-size: 16px;"></i>Indikator Kategori Internal</h3>
                                </div>
                                <?php if (count($kat_legend_data) > 0): ?>
                                    <?php foreach ($kat_legend_data as $legend): ?>
                                        <div class="legend-item">
                                            <div style="display: flex; align-items: center; gap: 8px;">
                                                <span class="color-dot" style="background-color: <?= $legend['warna']; ?>;"></span>
                                                <span><?= htmlspecialchars($legend['nama']); ?></span>
                                            </div>
                                            <span class="legend-badge" style="color: #4cbb17;"><?= $legend['persen']; ?>%</span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="legend-item" style="justify-content: center; font-style: italic;">Belum menggunakan kategori dokumen.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </section>

    <script>
        const ctxLine = document.getElementById('cleanLineChart').getContext('2d');
        const realUnitNames = <?= json_encode($kat_names_real); ?>;
        const realUnitTotals = <?= json_encode($kat_totals); ?>;

        const gradientBg = ctxLine.createLinearGradient(0, 0, 0, 180);
        gradientBg.addColorStop(0, 'rgba(55, 211, 58, 0.43)');
        gradientBg.addColorStop(1, 'rgba(55, 211, 58, 0.14)');

        const myLineChart = new Chart(ctxLine, {
            type: 'line',
            plugins: [ChartDataLabels],
            data: {
                labels: <?= json_encode($kat_graph_indexes); ?>,
                datasets: [{
                    data: <?= json_encode($kat_percentages); ?>,
                    borderColor: '#4cbb17',
                    backgroundColor: gradientBg,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: <?= json_encode(array_column($kat_legend_data, 'warna')); ?>,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 15,
                        right: 15,
                        top: 15,
                        bottom: 5
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#4cbb17',
                        font: {
                            weight: '700',
                            size: 10
                        },
                        formatter: function(value) {
                            return value + '%';
                        }
                    },
                    tooltip: {
                        enabled: true,
                        callbacks: {
                            title: function(context) {
                                return realUnitNames[context[0].dataIndex];
                            },
                            label: function(context) {
                                return [
                                    ' Jumlah: ' + realUnitTotals[context.dataIndex] + ' berkas',
                                    ' Rasio internal: ' + context.raw + '%'
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            display: false
                        },
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        grace: '20%',
                        ticks: {
                            display: false
                        },
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    }
                }
            }
        });

        window.addEventListener('resize', () => {
            myLineChart.resize();
            myLineChart.update();
        });
    </script>
    <script src="../../assets/js/script.js"></script>
</body>

</html>