-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 22 Jul 2026 pada 17.40
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_siapus`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `arsip`
--

CREATE TABLE `arsip` (
  `id_arsip` int(11) NOT NULL,
  `kode_arsip` varchar(50) NOT NULL,
  `nama_arsip` varchar(255) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL,
  `id_unit` int(11) DEFAULT NULL,
  `file_arsip` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `arsip`
--

INSERT INTO `arsip` (`id_arsip`, `kode_arsip`, `nama_arsip`, `id_kategori`, `id_unit`, `file_arsip`, `deskripsi`, `created_at`) VALUES
(14, 'TU-SM-001', 'Surat Edaran Disiplin Pegawai', 8, 15, '1782105928_Surat_Edaran_Disiplin_Pegawai.pdf', '', '2026-06-22 05:25:28'),
(15, 'TU-SKR-001', 'Surat Permohonan Data Kepegawaian', 24, 15, '1782106006_Surat_Permohonan_Data_Kepegawaian.docx', '', '2026-06-22 05:26:46'),
(17, 'TU-SOP-001', 'SOP Pengelolaan Surat Masuk', 10, 15, '1782106115_SOP_Pengelolaan_Surat_Masuk.pdf', '', '2026-06-22 05:28:35'),
(18, 'TU-LAP-001', 'Laporan Ketatausahaan Januari 2026', 11, 15, '1782106161_Laporan_Ketatausahaan_Januari_2026.pdf', '', '2026-06-22 05:29:21'),
(19, 'KEU-DK-001', 'Laporan Realisasi Anggaran', 22, 17, '1782106216_Laporan_Realisasi_Anggaran.pdf', '', '2026-06-22 05:30:16'),
(20, 'KEU-LAP-001', 'Laporan Keuangan Bulanan', 22, 17, '1782106400_Laporan_Keuangan_Bulanan.pdf', '', '2026-06-22 05:33:20'),
(21, 'KEU-NR-001', 'Notulen Evaluasi Anggaran', 13, 17, '1782106446_Notulen_Evaluasi_Anggaran.pdf', '', '2026-06-22 05:34:06'),
(22, 'KEU-SOP-001', 'SOP Pengajuan Pembayaran', 10, 17, '1782106493_SOP_Pengajuan_Pembayaran.pdf', '', '2026-06-22 05:34:53'),
(23, 'UGD-DS-002', 'Statistik Kunjungan UGD', 20, 11, '1782106572_Statistik_Kunjungan_UGD.docx', '', '2026-06-22 05:36:12'),
(24, 'UGD-SKR-003', 'Permohonan Pengadaan Obat Emergensi', 24, 11, '1782106705_Permohonan_Pengadaan_Obat_Emergensi.docx', '', '2026-06-22 05:38:25'),
(25, 'SIM-SOP-021', 'SOP Backup Data', 10, 18, '1782106794_SOP_Backup_Data.pdf', '', '2026-06-22 05:39:54'),
(26, 'SIM-LAP-012', 'Laporan Kinerja Sistem', 11, 18, '1782106866_Laporan_Kinerja_Sistem.pdf', '', '2026-06-22 05:41:06'),
(27, 'SIM-SK-001', 'SK Tim Pengelola Sistem Informasi', 9, 18, '1782106923_SK_Tim_Pengelola_Sistem_Informasi.pdf', '', '2026-06-22 05:42:03'),
(28, 'LAB-DS-001', 'Statistik Pemeriksaan Laboratorium', 20, 13, '1782106972_Statistik_Pemeriksaan_Laboratorium.docx', '', '2026-06-22 05:42:52'),
(29, 'LAB-LPLPO-001', 'Laporan Pemakaian Reagen', 23, 13, '1782107017_Laporan_Pemakaian_Reagen.pdf', '', '2026-06-22 05:43:37'),
(30, 'LAB-SOP-132', 'SOP Pengambilan Sampel Darah', 10, 13, '1782107067_SOP_Pengambilan_Sampel_Darah.pdf', '', '2026-06-22 05:44:27'),
(31, 'SURV-DS-389', 'Statistik Penyakit Menular', 20, 21, '1782107141_Statistik_Penyakit_Menular.pdf', '', '2026-06-22 05:45:41'),
(32, 'SURV-SOP-443', 'SOP Pelaporan Kasus Penyakit Menular', 10, 21, '1782107199_SOP_Pelaporan_Kasus_Penyakit_Menular.pdf', '', '2026-06-22 05:46:39'),
(33, 'GIGI-DS-882', 'Statistik Kunjungan Poli Gigi', 20, 10, '1782107258_Statistik_Kunjungan_Poli_Gigi.pdf', '', '2026-06-22 05:47:38'),
(34, 'GIGI-LAP-198', 'Laporan Pelayanan Poli Gigi', 11, 10, '1782107319_Laporan_Pelayanan_Poli_Gigi.pdf', '', '2026-06-22 05:48:39'),
(35, 'GIGI-DOK-001', 'Dokumentasi Penyuluhan Kesehatan Gigi', 14, 10, '1782107437_Dokumentasi_Penyuluhan_Kesehatan_Gigi.pdf', '', '2026-06-22 05:50:37'),
(36, 'GIGI-SOP-342', 'SOP Pemeriksaan Gigi Dasar', 10, 10, '1782107488_SOP_Pemeriksaan_Gigi_Dasar.pdf', '', '2026-06-22 05:51:28'),
(37, 'KL-AKR-001', 'Evaluasi Mutu Program Kesehatan Lingkungan', 15, 9, '1782112005_Evaluasi_Mutu_Program_Kesehatan_Lingkungan.pdf', '', '2026-06-22 07:06:45'),
(38, 'KL-PEG-992', 'Daftar Petugas Kesehatan Lingkungan', 18, 9, '1782112077_Daftar_Petugas_Kesehatan_Lingkungan.pdf', '', '2026-06-22 07:07:57'),
(39, 'KL-DOK-001', 'Dokumentasi Pemeriksaan Sanitasi Lingkungan', 14, 9, '1782112129_Dokumentasi_Pemeriksaan_Sanitasi_Lingkungan.pdf', '', '2026-06-22 07:08:49'),
(40, 'KL-LPK-001', 'Program Kerja Kesehatan Lingkungan Tahun 2026', NULL, 9, '1782112185_Program_Kerja_Kesehatan_Lingkungan_Tahun_2026.pdf', '', '2026-06-22 07:09:45'),
(41, 'KL-LPLPO-001', 'Inventaris Peralatan Kesehatan Lingkungan', 23, 9, '1782112246_Inventaris_Peralatan_Kesehatan_Lingkungan.pdf', '', '2026-06-22 07:10:46'),
(47, 'FARM-LPLPO-231 ', 'Persediaan Obat ', 23, 12, '1782135340_Persediaan_Obat_.pdf', '', '2026-06-22 13:35:40'),
(48, 'FARM-AKR-331 ', 'Evaluasi Mutu Pelayanan Kefarmasian ', 15, 12, '1782135384_Evaluasi_Mutu_Pelayanan_Kefarmasian_.pdf', '', '2026-06-22 13:36:24'),
(50, 'FARM-PEG-001 ', 'Data Petugas Kefarmasian ', 18, 12, '1783106645_Data_Petugas_Kefarmasian_.pdf', '', '2026-07-03 19:24:05'),
(52, 'RI-ADM-001 ', 'Daftar Penempatan Ruang Perawatan ', 16, 29, '1783340638_Daftar_Penempatan_Ruang_Perawatan_.pdf', '', '2026-07-06 12:23:58'),
(55, 'RI-NR-001 ', 'Notulen Evaluasi Pelayanan Rawat Inap ', 13, 29, '1783340828_Notulen_Evaluasi_Pelayanan_Rawat_Inap_.pdf', '', '2026-07-06 12:27:08'),
(57, 'RI-LAP-002 ', 'Laporan Pelayanan Rawat Inap februari 2026 ', 11, 29, '1783792298_RI-LAP-002_.pdf', '', '2026-07-11 17:51:38'),
(58, 'RI-LAP-001', 'Laporan Pelayanan Rawat Inap Januari 2026', 11, 29, '1783792724_Laporan_Pelayanan_Rawat_Inap_Januari_2026.pdf', '', '2026-07-11 17:58:44'),
(59, 'FARM-AKR-331 ', 'Evaluasi Mutu Pelayanan Kefarmasian', 15, 12, '1783999344_Evaluasi_Mutu_Pelayanan_Kefarmasian.pdf', '', '2026-07-14 03:22:24');

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `created_at`) VALUES
(8, 'Surat Masuk', '2026-02-09 10:17:28'),
(9, 'Surat Keputusan (SK)', '2026-06-21 15:50:58'),
(10, 'Standar Operasional Prosedur (SOP)', '2026-06-21 15:51:20'),
(11, 'Laporan', '2026-06-21 15:51:31'),
(13, 'Notulen Rapat', '2026-06-21 15:51:48'),
(14, 'Dokumentasi Kegiatan', '2026-06-21 15:51:56'),
(15, 'Dokumen Akreditasi dan Mutu', '2026-06-21 15:52:06'),
(16, 'Dokumen Administrasi', '2026-06-21 15:52:17'),
(18, 'Dokumen Kepegawaian', '2026-06-21 15:52:32'),
(20, 'Data Statistik', '2026-06-21 15:53:02'),
(22, 'Dokumen Keuangan', '2026-06-21 17:01:37'),
(23, 'Logistik, Aset, dan LPLPO', '2026-06-21 17:02:33'),
(24, 'Surat Keluar', '2026-06-21 17:03:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id_log` int(11) NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_user` int(11) NOT NULL,
  `aktivitas` varchar(50) NOT NULL,
  `objek_aktivitas` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id_log`, `waktu`, `id_user`, `aktivitas`, `objek_aktivitas`) VALUES
(4, '2026-07-03 19:24:05', 17, 'Upload Arsip', 'Data Petugas Kefarmasian '),
(35, '2026-07-06 12:10:18', 2, 'Tambah Unit Kerja', 'Pelayanan Rawat Inap'),
(39, '2026-07-06 12:21:34', 2, 'Tambah Pengguna', 'rawatinap'),
(40, '2026-07-06 12:23:58', 23, 'Upload Arsip', 'Daftar Penempatan Ruang Perawatan '),
(41, '2026-07-06 12:26:00', 23, 'Upload Arsip', 'Statistik Kunjungan Rawat Inap Januari 2026 '),
(42, '2026-07-06 12:26:40', 23, 'Upload Arsip', 'Laporan Pelayanan Rawat Inap februari 2026 '),
(43, '2026-07-06 12:27:08', 23, 'Upload Arsip', 'Notulen Evaluasi Pelayanan Rawat Inap '),
(44, '2026-07-06 12:27:38', 23, 'Upload Arsip', 'SOP Penerimaan Pasien Rawat Inap'),
(45, '2026-07-06 12:30:17', 2, 'Hapus Arsip', 'Statistik Kunjungan Rawat Inap Januari 2026 '),
(46, '2026-07-06 12:32:41', 9, 'Download Arsip', 'Notulen Evaluasi Pelayanan Rawat Inap '),
(47, '2026-07-06 12:42:37', 11, 'Download Arsip', 'Laporan Realisasi Anggaran'),
(48, '2026-07-06 12:47:11', 9, 'Download Arsip', 'Notulen Evaluasi Anggaran'),
(49, '2026-07-11 17:43:48', 23, 'Download Arsip', 'Daftar Penempatan Ruang Perawatan '),
(52, '2026-07-11 17:56:11', 2, 'Hapus Arsip', 'SOP Penerimaan Pasien Rawat Inap'),
(55, '2026-07-11 17:58:44', 23, 'Upload Arsip', 'Laporan Pelayanan Rawat Inap Januari 2026'),
(56, '2026-07-11 17:59:42', 2, 'Hapus Arsip', 'Laporan Pelayanan Rawat Inap Januari 2026 '),
(57, '2026-07-14 03:22:24', 2, 'Upload Arsip', 'Evaluasi Mutu Pelayanan Kefarmasian');

-- --------------------------------------------------------

--
-- Struktur dari tabel `unit_kerja`
--

CREATE TABLE `unit_kerja` (
  `id_unit` int(11) NOT NULL,
  `kode_unit` varchar(20) NOT NULL,
  `nama_unit` varchar(100) NOT NULL,
  `penanggung_jawab` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `unit_kerja`
--

INSERT INTO `unit_kerja` (`id_unit`, `kode_unit`, `nama_unit`, `penanggung_jawab`, `created_at`) VALUES
(9, 'UK-KSL420', 'Kesehatan Lingkungan', 'Adri Oktarizan, A.Md.kl, SKM', '2026-06-21 17:06:25'),
(10, 'UK-KGM431', 'Pelayanan Kesehatan Gigi dan Mulut', 'drg. Dola Siska', '2026-06-21 17:06:46'),
(11, 'UK-UGD991', 'Pelayanan Gawat Darurat', 'Jumini, A.Md.Kep', '2026-06-21 17:07:08'),
(12, 'UK-FARM212', 'Pelayanan Kefarmasian', 'Yuci Pramujianti, S.Farm.Apt', '2026-06-21 17:07:24'),
(13, 'UK-LAB332', 'Pelayanan Laboratorium dan Kesehatan Masyarakat', 'Rosi', '2026-06-21 17:07:48'),
(15, 'UK-KTU434', 'Manajemen Ketatausahaan', 'Dewi Palneti, SKM', '2026-06-21 17:10:38'),
(17, 'UK-KEU442', 'Manajemen Keuangan dan Aset/BMD', 'Gino Derianto, A.Md.AK', '2026-06-21 17:11:27'),
(18, 'UK-SID625', 'Manajemen Sistem Informasi Digital', 'Iche Risnaldi', '2026-06-21 17:11:54'),
(21, 'UK-SVR452', 'Surveilans dan Respon', 'Nelwati, AMKG', '2026-06-21 17:13:21'),
(29, 'UK-PRI221', 'Pelayanan Rawat Inap', 'Yusraini Jamil, A.Md.Kep', '2026-07-06 12:10:18');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `role` enum('admin','petugas','pimpinan') NOT NULL,
  `id_unit` int(11) DEFAULT NULL,
  `status` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `nama_lengkap`, `foto`, `role`, `id_unit`, `status`, `created_at`) VALUES
(2, 'admin', '$2y$10$y6DI/BhlJ0aNWgkjgTA8z.HXtK.Cvx.jGZK/UO7UXXgO5YilLs3ue', 'Administrator ', '1770615785_pp.jpeg', 'admin', NULL, 'aktif', '2026-02-09 06:16:52'),
(9, 'pimpinan01', '$2y$10$Re7XxJLQpQyDN85ZCDKGIeZ67fNrXp0oQzC1kdoWzEWX3WPND7Ip2', 'Sudeti Sartika Wani, SKM, M.M', '1770618020_pimpinan01.jpg', 'pimpinan', NULL, 'aktif', '2026-02-09 06:20:20'),
(10, 'tatausaha', '$2y$10$RwUyqa.OEQ5PfqnOweDOSuDvcy/xQzRCNZodLXkY1DAiGV.DhCC3m', 'Dewi Palneti, SKM', '1782063840_tatausaha.jpeg', 'petugas', 15, 'aktif', '2026-06-21 17:44:00'),
(11, 'keuangan', '$2y$10$nL2DER52MkDS1HwX74bkzOekN.czKMVSWi5AiQf5xpE3PYVA1bnmO', 'Gino Derianto, A.Md.AK', '1782063928_keuangan.jpeg', 'petugas', 17, 'aktif', '2026-06-21 17:45:28'),
(12, 'ugd', '$2y$10$JHund35KVwPKa6yOlnTRs.7eGZIa5.IFJ0.ys9xfsj89AY.P9C6Ru', 'Jumini, A.Md.Kep', '1782103999_ugd.png', 'petugas', 11, 'aktif', '2026-06-22 04:53:19'),
(13, 'sid', '$2y$10$npx8q7QN6KP.IluU3DecYOxutx4mnUDFwrWOsMGkaevqQCHi4Y.16', 'Iche Risnaldi', 'default.jpg', 'petugas', 18, 'aktif', '2026-06-22 04:55:10'),
(14, 'laboratotium', '$2y$10$sGVI4yKxMDS9Uh8zhBXTq.GS6g.zxjnm6UJI7YF/RVdRov8suwOPW', 'Rosi', 'default.jpg', 'petugas', 13, 'aktif', '2026-06-22 04:56:23'),
(15, 'surveilans', '$2y$10$zKh2jl50HlxlDsjlZsumPuVKcLtBJ5RB6n2UEV49gBkkJbp5bKP9a', 'Nelwati, AMKG', 'default.jpg', 'petugas', 21, 'aktif', '2026-06-22 04:56:49'),
(16, 'gigidanmulut', '$2y$10$zVwkDHDjABNZ9zOh7GZD1euJjIdDHcTLZqqiXFATBZ4nYhHrXxqe.', 'drg. Dola Siska', 'default.jpg', 'petugas', 10, 'aktif', '2026-06-22 04:57:40'),
(17, 'farmasi', '$2y$10$MiJ8sTMsi9GPqGP59e2Jeee/d4zjl4UbfE3IJMIJhf7EYUZaSG9pO', 'Yuci Pramujianti, S.Farm.Apt', '1782135273_farmasi.jpg', 'petugas', 12, 'aktif', '2026-06-22 13:34:33'),
(20, 'ksl', '$2y$10$j99sUfYjhqr4d.bTaUEkO.zrZ0Gx2yhpplUl8wCtrcc788bsIVSCq', 'Adri Oktarizan, A.Md.kl, SKM', 'default.jpg', 'petugas', 9, 'aktif', '2026-06-28 16:54:56'),
(23, 'rawatinap', '$2y$10$kPSm6Vm8WeqKwPUJD5D/PuNaSdt0e7iDpWbG5.ilVrOxlUxdBNEPi', 'Yusraini Jamil, A.Md.Kep', '1783340494_rawatinap.jpg', 'petugas', 29, 'aktif', '2026-07-06 12:21:34');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `arsip`
--
ALTER TABLE `arsip`
  ADD PRIMARY KEY (`id_arsip`),
  ADD KEY `id_kategori` (`id_kategori`),
  ADD KEY `id_unit` (`id_unit`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `unit_kerja`
--
ALTER TABLE `unit_kerja`
  ADD PRIMARY KEY (`id_unit`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_unit` (`id_unit`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `arsip`
--
ALTER TABLE `arsip`
  MODIFY `id_arsip` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT untuk tabel `unit_kerja`
--
ALTER TABLE `unit_kerja`
  MODIFY `id_unit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `arsip`
--
ALTER TABLE `arsip`
  ADD CONSTRAINT `arsip_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE SET NULL,
  ADD CONSTRAINT `arsip_ibfk_2` FOREIGN KEY (`id_unit`) REFERENCES `unit_kerja` (`id_unit`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_unit`) REFERENCES `unit_kerja` (`id_unit`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
