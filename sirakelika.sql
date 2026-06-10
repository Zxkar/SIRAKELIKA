-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 08 Jun 2026 pada 09.41
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
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','superadmin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `nama_admin`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'admin@sirakelika.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin', '2026-06-08 07:36:55', '2026-06-08 07:36:55');

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
  `id_admin` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `edukasi`
--

INSERT INTO `edukasi` (`id_edukasi`, `judul`, `kategori`, `konten`, `id_admin`, `created_at`, `updated_at`) VALUES
(1, 'Mengenal Kekerasan Fisik di Kampus', 'fisik', 'Kekerasan fisik adalah tindakan menyakiti tubuh seseorang secara langsung seperti memukul, mendorong, atau melukai. Di lingkungan kampus, kekerasan fisik sering terjadi dalam bentuk perkelahian atau tindakan senioritas yang berlebihan.', NULL, '2026-06-08 07:36:55', '2026-06-08 07:36:55'),
(2, 'Apa itu Kekerasan Verbal?', 'verbal', 'Kekerasan verbal meliputi penghinaan, ancaman, kata-kata kasar, atau perundungan melalui ucapan yang menyakiti perasaan orang lain. Meski tidak meninggalkan bekas fisik, dampak psikologisnya sangat serius.', NULL, '2026-06-08 07:36:55', '2026-06-08 07:36:55'),
(3, 'Memahami Kekerasan Seksual', 'seksual', 'Kekerasan seksual adalah segala tindakan seksual yang dilakukan tanpa persetujuan korban. Ini termasuk pelecehan verbal bernuansa seksual, sentuhan tidak senonoh, hingga pemaksaan.', NULL, '2026-06-08 07:36:55', '2026-06-08 07:36:55'),
(4, 'Kekerasan Psikologis dan Dampaknya', 'psikologis', 'Kekerasan psikologis adalah tindakan yang menyebabkan tekanan mental seperti intimidasi, manipulasi, isolasi sosial, atau ancaman. Dampaknya bisa berupa depresi, kecemasan, dan trauma jangka panjang.', NULL, '2026-06-08 07:36:55', '2026-06-08 07:36:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `faq`
--

CREATE TABLE `faq` (
  `id_faq` int(11) NOT NULL,
  `pertanyaan` text NOT NULL,
  `jawaban` text NOT NULL,
  `urutan` int(11) DEFAULT 0,
  `id_admin` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `faq`
--

INSERT INTO `faq` (`id_faq`, `pertanyaan`, `jawaban`, `urutan`, `id_admin`, `created_at`) VALUES
(1, 'Apakah laporan saya bisa anonim?', 'Ya, sistem menyediakan mode pelaporan UMUM yang tidak memerlukan identitas pelapor. Identitas kamu sepenuhnya terlindungi.', 1, NULL, '2026-06-08 07:36:55'),
(2, 'Berapa lama laporan akan diproses?', 'Laporan akan diverifikasi dalam 1x24 jam oleh admin. Proses investigasi menyesuaikan kompleksitas kasus.', 2, NULL, '2026-06-08 07:36:55'),
(3, 'Apakah data saya aman?', 'Seluruh data tersimpan terenkripsi dan hanya dapat diakses oleh pihak yang berwenang sesuai peran masing-masing.', 3, NULL, '2026-06-08 07:36:55'),
(4, 'Bagaimana cara cek status laporan anonim?', 'Saat membuat laporan anonim, kamu akan mendapat kode unik. Gunakan kode tersebut di menu \"Cek Status\" tanpa perlu login.', 4, NULL, '2026-06-08 07:36:55'),
(5, 'Jenis kekerasan apa saja yang bisa dilaporkan?', 'Kekerasan fisik, verbal, seksual, psikologis, dan perundungan (bullying) di lingkungan kampus.', 5, NULL, '2026-06-08 07:36:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `konsultasi`
--

CREATE TABLE `konsultasi` (
  `id_konsultasi` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `pesan` text NOT NULL,
  `pengirim` enum('mahasiswa','admin') NOT NULL,
  `is_anonim` tinyint(1) DEFAULT 0,
  `waktu_kirim` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `jenis_kekerasan` enum('fisik','verbal','seksual','psikologis','perundungan','lainnya') NOT NULL,
  `jenis_pelaporan` enum('UMUM','KHUSUS') NOT NULL DEFAULT 'UMUM',
  `waktu_kejadian` datetime NOT NULL,
  `lokasi_kejadian` varchar(255) NOT NULL,
  `status_laporan` enum('menunggu','diproses','ditindaklanjuti','selesai','ditolak') DEFAULT 'menunggu',
  `kode_laporan` varchar(20) DEFAULT NULL,
  `tanggal_laporan` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `id_mahasiswa` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_mahasiswa` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `status_akun` enum('aktif','nonaktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `manajemen_kampus`
--

CREATE TABLE `manajemen_kampus` (
  `id_manajemen` int(11) NOT NULL,
  `nama_manajemen` varchar(100) NOT NULL,
  `jabatan` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `manajemen_kampus`
--

INSERT INTO `manajemen_kampus` (`id_manajemen`, `nama_manajemen`, `jabatan`, `email`, `created_at`) VALUES
(1, 'Wakil Rektor Bidang Kemahasiswaan', 'Wakil Rektor III', 'warek3@ithabibie.ac.id', '2026-06-08 07:36:55');

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id_notifikasi` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `id_laporan` int(11) DEFAULT NULL,
  `judul` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `status_laporan_log`
--

CREATE TABLE `status_laporan_log` (
  `id_log` int(11) NOT NULL,
  `id_laporan` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `status_lama` enum('menunggu','diproses','ditindaklanjuti','selesai','ditolak') DEFAULT NULL,
  `status_baru` enum('menunggu','diproses','ditindaklanjuti','selesai','ditolak') NOT NULL,
  `catatan` text DEFAULT NULL,
  `tanggal_update` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `tim_investigasi`
--

CREATE TABLE `tim_investigasi` (
  `id_tim` int(11) NOT NULL,
  `nama_tim` varchar(100) NOT NULL,
  `kontak_tim` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `tim_investigasi`
--

INSERT INTO `tim_investigasi` (`id_tim`, `nama_tim`, `kontak_tim`, `email`, `created_at`) VALUES
(1, 'Tim Satgas PPKS', '081234567890', 'satgas@ithabibie.ac.id', '2026-06-08 07:36:55');

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
  `tanggal_tindakan` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `email` (`email`);

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
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`id_faq`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD PRIMARY KEY (`id_konsultasi`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD UNIQUE KEY `kode_laporan` (`kode_laporan`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`);

--
-- Indeks untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`id_mahasiswa`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `manajemen_kampus`
--
ALTER TABLE `manajemen_kampus`
  ADD PRIMARY KEY (`id_manajemen`);

--
-- Indeks untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id_notifikasi`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`),
  ADD KEY `id_laporan` (`id_laporan`);

--
-- Indeks untuk tabel `status_laporan_log`
--
ALTER TABLE `status_laporan_log`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_laporan` (`id_laporan`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indeks untuk tabel `tim_investigasi`
--
ALTER TABLE `tim_investigasi`
  ADD PRIMARY KEY (`id_tim`);

--
-- Indeks untuk tabel `tindak_lanjut`
--
ALTER TABLE `tindak_lanjut`
  ADD PRIMARY KEY (`id_tindak_lanjut`),
  ADD KEY `id_laporan` (`id_laporan`),
  ADD KEY `id_admin` (`id_admin`),
  ADD KEY `id_tim` (`id_tim`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `bukti`
--
ALTER TABLE `bukti`
  MODIFY `id_bukti` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `edukasi`
--
ALTER TABLE `edukasi`
  MODIFY `id_edukasi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `faq`
--
ALTER TABLE `faq`
  MODIFY `id_faq` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `konsultasi`
--
ALTER TABLE `konsultasi`
  MODIFY `id_konsultasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `mahasiswa`
--
ALTER TABLE `mahasiswa`
  MODIFY `id_mahasiswa` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `manajemen_kampus`
--
ALTER TABLE `manajemen_kampus`
  MODIFY `id_manajemen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id_notifikasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `status_laporan_log`
--
ALTER TABLE `status_laporan_log`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `tim_investigasi`
--
ALTER TABLE `tim_investigasi`
  MODIFY `id_tim` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `tindak_lanjut`
--
ALTER TABLE `tindak_lanjut`
  MODIFY `id_tindak_lanjut` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bukti`
--
ALTER TABLE `bukti`
  ADD CONSTRAINT `bukti_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `edukasi`
--
ALTER TABLE `edukasi`
  ADD CONSTRAINT `edukasi_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `faq`
--
ALTER TABLE `faq`
  ADD CONSTRAINT `faq_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `konsultasi`
--
ALTER TABLE `konsultasi`
  ADD CONSTRAINT `konsultasi_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE SET NULL,
  ADD CONSTRAINT `konsultasi_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `mahasiswa` (`id_mahasiswa`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifikasi_ibfk_2` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `status_laporan_log`
--
ALTER TABLE `status_laporan_log`
  ADD CONSTRAINT `status_laporan_log_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE,
  ADD CONSTRAINT `status_laporan_log_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `tindak_lanjut`
--
ALTER TABLE `tindak_lanjut`
  ADD CONSTRAINT `tindak_lanjut_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE,
  ADD CONSTRAINT `tindak_lanjut_ibfk_2` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL,
  ADD CONSTRAINT `tindak_lanjut_ibfk_3` FOREIGN KEY (`id_tim`) REFERENCES `tim_investigasi` (`id_tim`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
