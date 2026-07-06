<?php
session_start();

date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../../index.php");
  exit;
}

include "../../config/koneksi.php";

if (!isset($koneksi) || !$koneksi) {
  die("Database connection failed.");
}

// relasi Unit dan Kategori
$query_activity = "SELECT a.*, u.nama_unit, k.nama_kategori FROM arsip a 
                   LEFT JOIN unit_kerja u ON a.id_unit = u.id_unit 
                   LEFT JOIN kategori k ON a.id_kategori = k.id_kategori
                   ORDER BY a.id_arsip DESC LIMIT 15";
$recent_activity = mysqli_query($koneksi, $query_activity);

// statistik untuk Box Info
$count_arsip = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_arsip FROM arsip"));
$count_user = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_user FROM users"));
$count_unit = mysqli_num_rows(mysqli_query($koneksi, "SELECT id_unit FROM unit_kerja"));

// DATA GRAPH GLOBAL
$query_all_unit = "SELECT u.nama_unit, COUNT(a.id_arsip) as total_arsip 
                   FROM unit_kerja u 
                   LEFT JOIN arsip a ON u.id_unit = a.id_unit 
                   GROUP BY u.id_unit 
                   ORDER BY total_arsip DESC";
$result_all_unit = mysqli_query($koneksi, $query_all_unit);

$unit_graph_indexes = [];
$unit_percentages = [];
$unit_totals = [];
$unit_names_real = [];
$unit_legend_data = [];

$color_palette = ['#3C91E6', '#4ECE3D', '#FFB534', '#FF6B6B', '#9b59b6', '#ff9f40', '#e84393', '#00cec9', '#6c5ce7', '#fdcb6e'];

$nomor_indeks = 1;
while ($row_unit = mysqli_fetch_assoc($result_all_unit)) {
  $unit_graph_indexes[] = $nomor_indeks;
  $jumlah = (int)$row_unit['total_arsip'];
  $unit_totals[] = $jumlah;
  $unit_names_real[] = $row_unit['nama_unit'];
  $persen = ($count_arsip > 0) ? round(($jumlah / $count_arsip) * 100, 1) : 0;
  $unit_percentages[] = $persen;
  $assigned_color = $color_palette[($nomor_indeks - 1) % count($color_palette)];

  $unit_legend_data[] = [
    'indeks' => $nomor_indeks,
    'nama' => $row_unit['nama_unit'],
    'warna' => $assigned_color,
    'persen' => $persen
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
  <title>Dashboard - SIAPuskesmas</title>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

  <style>
    html,
    body {
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

    .head-title,
    .box-info {
      flex-shrink: 0;
      margin-bottom: 15px !important;
    }

    .head-title,
    h3 {
      color: var(--dark);
    }

    .info-data {
      display: grid;
      grid-template-columns: 1.2fr 1fr;
      /* Sedikit dilebarkan porsi kiri untuk tabel */
      gap: 24px;
      flex-grow: 1;
      min-height: 0;
      align-items: stretch;
    }

    /* 🎨 STYLE TABEL MODERN */
    .table-data {
      background: var(--white-card);
      padding: 25px;
      border-radius: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      display: flex;
      flex-direction: column;
      min-height: 0;
    }

    .activity-scroll-area {
      flex-grow: 1;
      overflow-y: auto;
      padding-right: 5px;
    }

    .modern-table {
      width: 100%;
      border-collapse: collapse;
    }

    .modern-table th {
      position: sticky;
      top: 0;
      background: var(--white-card);
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

    .modern-table tr:hover td {
      background: var(--light-bg);
    }

    .badge-cat {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
    }

    /* 💡 CEGAH GRAFIK PENYEK */
    .right-dashboard-panel {
      display: flex;
      flex-direction: column;
      min-height: 0;
    }

    .chart-card {
      background: var(--light);
      padding: 25px;
      border-radius: 20px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
      border: 1px solid var(--border-color, transparent);
      display: flex;
      flex-direction: column;
      height: 100%;
      min-height: 0;
    }

    .chart-container-wrapper {
      flex-grow: 1;
      /* Biar grafik ambil sisa ruang paling banyak */
      min-height: 200px;
      position: relative;
    }

    .chart-caption-box {
      margin-top: 15px;
      padding: 12px;
      background: var(--light);
      border-top: 3px solid #4cbb17;
      border-radius: 8px;
      flex-shrink: 0;
    }

    .legend-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 8px;
      max-height: 90px;
      /* Batasi tinggi legenda biar grafik gak penyek */
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

    .activity-scroll-area::-webkit-scrollbar,
    .legend-grid::-webkit-scrollbar {
      width: 5px;
    }

    .activity-scroll-area::-webkit-scrollbar-thumb,
    .legend-grid::-webkit-scrollbar-thumb {
      background: var(--dark-grey);
      border-radius: 5px;
    }

    .h3 {
      color: var(--dark);
    }

    .h3,
    p {
      color: var(--dark);
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
          <p style="color: var(--dark-grey); font-size: 14px; margin-top: 2px;">
            Selamat datang Administrator, <span style="color: var(--green); font-weight: 600;">
              <?= isset($_SESSION['nama']) ? $_SESSION['nama'] : 'Administrator'; ?>
            </span>! 👋
          </p>
        </div>
        <div class="right" style="text-align: right;">
          <h4 id="clock" style="color: var(--dark); margin: 0; font-size: 16px;"></h4>
          <p style="color: var(--dark-grey); font-size: 11px; margin-top: 1px;"><?= date('l, d F Y') ?></p>
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
        </li>
      </ul>

      <div class="info-data">
        <div class="table-data">
          <div class="head" style="margin-bottom: 15px; flex-shrink: 0; display: flex; justify-content: space-between;">
            <h3 style="font-size: 16px; color: var(--dark); font-weight: 600;">Log Aktivitas Unggahan</h3>
            <small style="color: var(--dark-grey); font-size: 11px;">Aktivitas Terkini</small>
          </div>

          <div class="activity-scroll-area">
            <table class="modern-table">
              <thead>
                <tr>
                  <th>Nama Unit</th>
                  <th>Nama Dokumen</th>
                  <th>Waktu</th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysqli_num_rows($recent_activity) > 0): ?>
                  <?php while ($act = mysqli_fetch_assoc($recent_activity)): ?>
                    <tr>
                      <td style="font-weight: 600;"><?= htmlspecialchars($act['nama_unit'] ?? 'Admin'); ?></td>
                      <td style="max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        <?= htmlspecialchars($act['nama_arsip']); ?>
                      </td>
                      <td style="color: var(--dark); font-size: 11px;"><?= date('H:i - d M', strtotime($act['created_at'])); ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="4" style="text-align: center; color: var(--dark-grey);">Belum ada aktivitas.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <div class="right-dashboard-panel">
          <div class="chart-card">
            <div class="head" style="margin-bottom: 15px; flex-shrink: 0;">
              <h3 style="font-size: 16px; color: var(--dark); font-weight: 600;">Persentase Distribusi Arsip Per Unit Kerja</h3>
            </div>

            <div class="chart-container-wrapper">
              <canvas id="cleanLineChart"></canvas>
            </div>

            <div class="chart-caption-box">
              <div class="legend-grid">
              <div class="head" style="flex-shrink: 0;">
              <h3 style="font-size: 13px; color: var(--dark); font-weight: 600;"><i class='bx bx-paint' style="padding-right:5px; color: #4cbb17; vertical-align: middle; font-size: 16px;"></i>Indikator dan Kontribusi Unit Kerja</h3>
            </div>
                <?php foreach ($unit_legend_data as $legend): ?>
                  <div class="legend-item">
                    <div style="display: flex; align-items: center; gap: 8px;">
                      <span class="color-dot" style="background-color: <?= $legend['warna']; ?>;"></span>
                      <span><?= htmlspecialchars($legend['nama']); ?></span>
                    </div>
                    <span style="font-weight: 700; color: #4cbb17;"><?= $legend['persen']; ?>%</span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </section>

  <script>
    const ctxLine = document.getElementById('cleanLineChart').getContext('2d');
    const realUnitNames = <?= json_encode($unit_names_real); ?>;
    const realUnitTotals = <?= json_encode($unit_totals); ?>;
    const gradientBg = ctxLine.createLinearGradient(0, 0, 0, 180);
    gradientBg.addColorStop(0, 'rgba(55, 211, 58, 0.43)');
    gradientBg.addColorStop(1, 'rgba(55, 211, 58, 0.14)');

    new Chart(ctxLine, {
      type: 'line',
      plugins: [ChartDataLabels],
      data: {
        labels: <?= json_encode($unit_graph_indexes); ?>,
        datasets: [{
          data: <?= json_encode($unit_percentages); ?>,
          borderColor: '#4cbb17',
          backgroundColor: gradientBg,
          fill: true,
          tension: 0.4,
          borderWidth: 3,
          pointBackgroundColor: <?= json_encode(array_column($unit_legend_data, 'warna')); ?>,
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
                  ' Rasio: ' + context.raw + '%'
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
  </script>

  <script src="../../assets/js/script.js"></script>
</body>

</html>