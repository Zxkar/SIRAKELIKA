<?php
session_start();
include "connection.php";

if (!isset($_SESSION['id_mahasiswa'])) {
    header("Location: login.php");
    exit;
}
$id_mahasiswa = (int) $_SESSION['id_mahasiswa'];

// Ambil data nama untuk ditampilkan di topbar
$res_user = $conn->query("SELECT nama_mahasiswa FROM mahasiswa WHERE id_mahasiswa = $id_mahasiswa LIMIT 1");
$user_row = $res_user ? $res_user->fetch_assoc() : null;
$nama_user = $user_row['nama_mahasiswa'] ?? 'Pengguna';
$inisial_user = '';
foreach (explode(' ', $nama_user) as $part) { $inisial_user .= strtoupper(substr($part, 0, 1)); }
$inisial_user = substr($inisial_user, 0, 2);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <aside class="sidebar">
        <div class="logo-area">
            <div class="logo-icon"></div>
            <div>
                <h1 class="logo-title">SIRAKELIKA</h1>
                <p class="logo-sub">PELAPORAN KEKERASAN KAMPUS</p>
            </div>
        </div>

        <nav class="nav-container">
            <div class="nav-group">MENU UTAMA</div>
            <a href="dashboard.php" class="nav-link active">
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="laporan.php" class="nav-link">
                <span class="nav-text">Laporan Saya</span>
            </a>

            <div class="nav-group">PENGELOLAAN</div>
            <a href="manajemen.php" class="nav-link">
                <span class="nav-text">Manajemen Kasus</span>
            </a>
            <a href="edukasi.php" class="nav-link">
                <span class="nav-text">Edukasi & Informasi</span>
            </a>
            <a href="kenali.php" class="nav-link">
                <span class="nav-text">Kenali Situasi Anda</span>
            </a>

            <div class="nav-group">AKUN</div>
            <a href="profil.php" class="nav-link">
                <span class="nav-text">Profil</span>
            </a>
            <a href="pengaturan.php" class="nav-link">
                <span class="nav-text">Pengaturan</span>
            </a>
            <a href="logout.php" class="nav-link logout" onclick="return confirm('Yakin ingin keluar?')">
                <span class="nav-text">Keluar</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        
        <header class="topbar">
            <div></div> 
            <div class="user-profile">
                <div class="notif-btn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9J M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="avatar"><?= htmlspecialchars($inisial_user) ?></div>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($nama_user) ?></span>
                    <span class="user-role">Mahasiswa</span>
                </div>
            </div>
        </header>

        <section class="welcome-banner">
            <div class="banner-text">
                <h2>Selamat Datang di SIRAKELIKA</h2>
                <p>Sistem Pelaporan Kekerasan di Lingkungan Kampus. Laporkan dengan aman, anonim, dan terlindungi. Kami ada untuk kamu.</p>
            </div>
            <a href="buat_laporan.php" class="btn-report">+ Buat Laporan Baru</a>
        </section>

        <div class="content-title">
            <h2>Dashboard</h2>
            <p>Ringkasan aktivitas dan status laporan kekerasan kampus</p>
        </div>

        <section class="stats-grid">
            <div class="card card-total">
                <span class="card-num">0</span>
                <span class="card-title">Total Laporan</span>
            </div>
            <div class="card card-new">
                <span class="card-num">0</span>
                <span class="card-title">Laporan Baru</span>
            </div>
            <div class="card card-process">
                <span class="card-num">0</span>
                <span class="card-title">Dalam Proses</span>
            </div>
            <div class="card card-done">
                <span class="card-num">0</span>
                <span class="card-title">Selesai Ditangani</span>
            </div>
        </section>

        <div class="data-grid">
            
            <div class="table-container">
                <div class="table-header">
                    <div>
                        <h3>Laporan Terbaru</h3>
                        <p>Laporan masuk dalam 7 hari terakhir</p>
                    </div>
                </div>
                
                <div class="empty-state">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p>Belum ada riwayat laporan yang dibuat.</p>
                </div>
            </div>

            <div class="activity-container">
                <h3>Aktivitas Terbaru</h3>
                <p class="activity-sub">Update status kasus</p>
                
                <div class="empty-state" style="padding: 60px 20px;">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Tidak ada aktivitas terbaru.</p>
                </div>
            </div>

        </div>

    </main>

</body>
</html>