-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 27 Jun 2026 pada 11.09
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
-- Database: `sirakelika`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bukti`
--

CREATE TABLE `bukti` (
  `id_bukti` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `file_bukti` varchar(255) NOT NULL,
  `nama_asli` varchar(255) DEFAULT NULL,
  `tipe_file` varchar(50) DEFAULT NULL,
  `ukuran_file` int(11) DEFAULT NULL,
  `tanggal_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `edukasi`
--

CREATE TABLE `edukasi` (
  `id_edukasi` int(11) NOT NULL,
  `judul` varchar(255) NOT NULL,
  `kategori` enum('fisik','verbal','seksual','psikologis','perundungan','umum') NOT NULL,
  `konten` text NOT NULL,
  `id_user_author` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `faq`
--

CREATE TABLE `faq` (
  `id_faq` int(11) NOT NULL,
  `pertanyaan` text NOT NULL,
  `jawaban` text NOT NULL,
  `urutan` int(11) DEFAULT 0,
  `id_user_author` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `konsultasi`
--

CREATE TABLE `konsultasi` (
  `id_konsultasi` int(11) NOT NULL,
  `id_user_pengirim` int(11) NOT NULL,
  `id_user_penerima` int(11) NOT NULL,
  `pesan` text NOT NULL,
  `is_anonim` tinyint(1) DEFAULT 0,
  `waktu_kirim` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `jenis_kekerasan` enum('fisik','verbal','seksual','psikologis','perundungan','lainnya') NOT NULL,
  `jenis_pelaporan` enum('UMUM','KHUSUS') NOT NULL DEFAULT 'UMUM',
  `waktu_kejadian` datetime NOT NULL,
  `lokasi_kejadian` varchar(255) NOT NULL,
  `status_laporan` enum('menunggu','diproses','ditindaklanjuti','selesai','ditolak') DEFAULT 'menunggu',
  `kode_laporan` varchar(20) DEFAULT NULL,
  `tanggal_laporan` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `jenis_laporan` varchar(100) DEFAULT 'Umum',
  `rekomendasi_tim` text DEFAULT NULL,
  `arahan_manajemen` text DEFAULT NULL,
  `file_sk` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `laporan`
--

INSERT INTO `laporan` (`id_laporan`, `id_user`, `judul_laporan`, `deskripsi`, `jenis_kekerasan`, `jenis_pelaporan`, `waktu_kejadian`, `lokasi_kejadian`, `status_laporan`, `kode_laporan`, `tanggal_laporan`, `updated_at`, `jenis_laporan`, `rekomendasi_tim`, `arahan_manajemen`, `file_sk`) VALUES
(6, NULL, '', '', '', 'UMUM', '0000-00-00 00:00:00', '', 'selesai', NULL, '2026-06-26 05:52:53', '2026-06-26 05:55:20', 'Umum', 'Rekomendasi Pemberhentian Tetap (Drop Out) secara tidak hormat terhadap pelaku sesuai Permendikbudristek No. 30 Tahun 2021.\n\n[SK TERBIT] No SK: #KS-6-UMUM  - Putusan Sanksi: Diberhentikan ', NULL, NULL),
(10, NULL, '', '', 'fisik', 'UMUM', '0000-00-00 00:00:00', '', 'selesai', NULL, '2026-06-24 07:24:38', '2026-06-24 07:43:04', 'Kekerasan Seksual', 'Rekomendasi dari Satgas berupa sanksi berat Pemberhentian Tetap (Drop Out) bagi pelaku mahasiswa yang bersangkutan\n\n[SK TERBIT] No SK: #KS-10-FISIK/2026 - Putusan Sanksi: \"Diberhentikan secara tidak hormat (Drop Out) sebagai mahasiswa.\"', NULL, NULL),
(11, 11, 'Ancaman Verbal dan Tekanan Mental Berkelanjutan', 'Saya menerima pesan penuh ancaman secara berkala dari oknum mahasiswa pasca pemilihan ketua himpunan. Pelaku mengancam akan menyebarkan fitnah dan mempersulit posisi saya di kelas jika saya tidak mengundurkan diri dari kepengurusan. Tindakan ini merusak fokus belajar dan kesehatan mental saya.', 'psikologis', 'UMUM', '2026-06-26 15:00:00', 'Area Gazebo Utama Kampus', 'selesai', 'KS-015', '2026-06-26 14:35:55', '2026-06-26 14:40:05', 'Umum', NULL, NULL, NULL),
(12, 10, 'dibuly', 'dipukul sampe bonyok', 'fisik', 'UMUM', '2026-06-06 13:29:00', 'Gudang', 'selesai', 'KS-2026-F59AAA', '2026-06-27 04:29:16', '2026-06-27 07:00:31', 'Umum', 'Hasil Investigasi selesai', NULL, 'SK_12_1782543631.jpeg');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_laporan` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `notifikasi`
--

INSERT INTO `notifikasi` (`id_notifikasi`, `id_user`, `id_laporan`, `judul`, `pesan`, `is_read`, `created_at`) VALUES
(1, 10, 12, 'Update Status Laporan [KS-2026-F59AAA]', 'Laporan kamu sedang dalam proses review oleh admin.', 0, '2026-06-27 04:46:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_laporan_log`
--

CREATE TABLE `status_laporan_log` (
  `id_log` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `id_user_petugas` int(11) DEFAULT NULL,
  `status_lama` enum('menunggu','diproses','ditindaklanjuti','selesai','ditolak') DEFAULT NULL,
  `status_baru` enum('menunggu','diproses','ditindaklanjuti','selesai','ditolak') NOT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal_update` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `status_laporan_log`
--

INSERT INTO `status_laporan_log` (`id_log`, `id_laporan`, `id_user_petugas`, `status_lama`, `status_baru`, `catatan`, `tanggal_update`) VALUES
(5, 10, NULL, 'ditindaklanjuti', 'selesai', 'SK Sanksi Resmi No #KS-10-FISIK/2026 Berhasil Disahkan.', '2026-06-24 07:43:04'),
(6, 6, NULL, 'ditindaklanjuti', 'selesai', 'SK Sanksi Resmi No #KS-6-UMUM  Berhasil Disahkan.', '2026-06-26 05:55:20'),
(7, 11, NULL, 'menunggu', 'ditindaklanjuti', NULL, '2026-06-26 14:37:29'),
(8, 11, NULL, 'ditindaklanjuti', 'selesai', NULL, '2026-06-26 14:40:05'),
(9, 12, NULL, 'menunggu', 'diproses', '', '2026-06-27 04:46:50'),
(10, 12, NULL, 'diproses', 'ditindaklanjuti', NULL, '2026-06-27 04:47:41'),
(11, 12, NULL, 'ditindaklanjuti', 'diproses', NULL, '2026-06-27 04:47:58'),
(12, 12, NULL, 'diproses', 'diproses', NULL, '2026-06-27 04:48:17'),
(13, 12, NULL, 'diproses', 'diproses', 'Investigasi Selesai', '2026-06-27 05:13:09'),
(14, 12, NULL, 'diproses', 'ditindaklanjuti', 'Berkas hasil investigasi resmi dikirim ke Manajemen Kampus.', '2026-06-27 05:18:22'),
(15, 12, NULL, 'ditindaklanjuti', 'selesai', 'Surat Keputusan Sanksi No #KS-12-FISIK resmi diterbitkan lewat dokumen berkas.', '2026-06-27 07:00:31');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tindak_lanjut`
--

CREATE TABLE `tindak_lanjut` (
  `id_tindak_lanjut` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `id_tim` int(11) DEFAULT NULL,
  `deskripsi_tindakan` text NOT NULL,
  `tanggal_tindakan` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tindak_lanjut`
--

INSERT INTO `tindak_lanjut` (`id_tindak_lanjut`, `id_laporan`, `id_admin`, `id_tim`, `deskripsi_tindakan`, `tanggal_tindakan`) VALUES
(1, 12, NULL, 7, 'Investigasi Selesai', '2026-06-27 05:13:09'),
(2, 12, NULL, 7, 'Mengirimkan rekomendasi hasil investigasi: Hasil Investigasi selesai', '2026-06-27 05:18:22');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `role` enum('mahasiswa','investigasi','manajemen','admin','superadmin') NOT NULL DEFAULT 'mahasiswa',
  `status_akun` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `email`, `password`, `no_hp`, `foto_profil`, `role`, `status_akun`, `created_at`, `updated_at`) VALUES
(3, 'tim1', 'tim1@gmail.com', '$2y$10$H0LC4aSSdmBWxi9MXz9g7.OrugKdwVEBW/OHJZGe.mmUyYyn92/Ke', NULL, NULL, 'investigasi', 'aktif', '2026-06-20 08:35:26', '2026-06-20 08:35:26'),
(4, 'pihakkampus1', 'manajemen1@gmail.com', '$2y$10$2SHJ.9P2psdgzIWmxgmEuejdWki.bXJMTYhmQHS3nlFgJpCbLfXEO', NULL, NULL, 'manajemen', 'aktif', '2026-06-20 08:35:26', '2026-06-24 06:10:16'),
(5, 'tio', 'tiojjs69@gmail.com', '$2y$10$aBhiBZS8HKgHBUcN/SL71.v4aLUvy1yxxLDuMwlelLW9lrZ2JAEKy', NULL, NULL, 'mahasiswa', 'aktif', '2026-06-20 08:47:18', '2026-06-24 06:15:04'),
(7, 'tim investigasi1', 'tim1investigasi@gmail.com', '$2y$10$h1gsYL6/YCci89z02Eh2lOKgIIL0Q5YUfGHGsdRfKrUBs0kZTbJu.', NULL, NULL, 'investigasi', 'aktif', '2026-06-20 09:26:33', '2026-06-26 06:20:34'),
(8, 'admin1', 'admin1@gmail.com', '$2y$10$AdpFeR55XJr1esve3CpHz.uTaIY1U0dgHU30TA5DVAxnN/nKLUVDS', NULL, NULL, 'admin', 'aktif', '2026-06-20 11:16:11', '2026-06-27 04:40:42'),
(9, 'cambaros', 'classicusers001@gmail.com', '$2y$10$o4RxUBXXM8QjaVUYgpWDD.jbwhwfh7vxybeFhvfbnJqlMS1LmShpG', NULL, NULL, 'mahasiswa', 'aktif', '2026-06-21 10:28:40', '2026-06-21 10:28:40'),
(10, 'merdeka', 'christianosamuel33@gmail.com', '$2y$10$8EXzlouvxNNZiCpwKDqlV.51CywziDgM.4mr1K0DwTvDZMo8da1gW', NULL, NULL, 'mahasiswa', 'aktif', '2026-06-21 10:32:24', '2026-06-27 04:26:27'),
(11, 'muhammad alif al fathir', 'aliffathir123@gmail.com', '$2y$10$F7.mY9IjTG/j1Vcb9gMP6eneja..LSRNZc/.l3ybrTpAegGgF3/r6', NULL, NULL, 'mahasiswa', 'aktif', '2026-06-23 10:48:35', '2026-06-23 10:48:35'),
(12, 'master_admin', 'master_adminsirakelika@url.id', 'adminsirakelika1', NULL, NULL, 'superadmin', 'aktif', '2026-06-23 11:00:17', '2026-06-26 07:23:10'),
(13, 'gads', 'gads241011075@ith.ac.id', '$2y$10$ggkNOX5BMB5.NhxPNR3wcOgyMhok55mlEoAPti5KuLcK9HpYDc7fi', NULL, NULL, 'mahasiswa', 'aktif', '2026-06-23 14:08:51', '2026-06-23 14:08:51'),
(14, 'gadadmin', 'gadmasteradmin@gmail.com', '$2y$10$IblCL1Ye.V4n4OyVgweWqulp6AhFOpmDnEct0OYGX1bZzNN7zh9ia', NULL, NULL, 'superadmin', 'aktif', '2026-06-26 07:24:36', '2026-06-26 07:24:36');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bukti`
--
ALTER TABLE `bukti`
  ADD PRIMARY KEY (`id_bukti`),
  ADD KEY `id_laporan` (`id_laporan`);

--
-- Indeks untuk tabel `edukasi`
--
ALTER TABLE `edukasi`
  ADD PRIMARY KEY (`id_edukasi`),
  ADD KEY `id_user_author` (`id_user_author`);

--
-- Indeks untuk tabel `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id_faq`),
  ADD KEY `id_user_author` (`id_user_author`);

--
-- Indeks untuk tabel `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD PRIMARY KEY (`id_konsultasi`),
  ADD KEY `id_user_pengirim` (`id_user_pengirim`),
  ADD KEY `id_user_penerima` (`id_user_penerima`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD UNIQUE KEY `kode_laporan` (`kode_laporan`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_laporan` (`id_laporan`);

--
-- Indeks untuk tabel `status_laporan_log`
--
ALTER TABLE `status_laporan_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_laporan` (`id_laporan`),
  ADD KEY `id_user_petugas` (`id_user_petugas`);

--
-- Indeks untuk tabel `tindak_lanjut`
--
ALTER TABLE `tindak_lanjut`
  ADD PRIMARY KEY (`id_tindak_lanjut`),
  ADD KEY `id_laporan` (`id_laporan`),
  ADD KEY `id_user_penindak` (`id_admin`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bukti`
--
ALTER TABLE `bukti`
  MODIFY `id_bukti` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `edukasi`
--
ALTER TABLE `edukasi`
  MODIFY `id_edukasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `faq`
--
ALTER TABLE `faq`
  MODIFY `id_faq` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `konsultasi`
--
ALTER TABLE `konsultasi`
  MODIFY `id_konsultasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `status_laporan_log`
--
ALTER TABLE `status_laporan_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `tindak_lanjut`
--
ALTER TABLE `tindak_lanjut`
  MODIFY `id_tindak_lanjut` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bukti`
--
ALTER TABLE `bukti`
  ADD CONSTRAINT `fk_bukti_laporan` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `fk_laporan_users` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notif_laporan` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `status_laporan_log`
--
ALTER TABLE `status_laporan_log`
  ADD CONSTRAINT `fk_log_laporan` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_log_users` FOREIGN KEY (`id_user_petugas`) REFERENCES `users` (`id_user`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
