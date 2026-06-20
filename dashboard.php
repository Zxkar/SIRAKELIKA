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

// =============================================
//  STATISTIK LAPORAN (milik mahasiswa yang login)
// =============================================
$total    = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_mahasiswa = $id_mahasiswa")->fetch_assoc()['c'] ?? 0);
$baru     = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_mahasiswa = $id_mahasiswa AND LOWER(status_laporan) = 'menunggu'")->fetch_assoc()['c'] ?? 0);
$proses   = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_mahasiswa = $id_mahasiswa AND LOWER(status_laporan) IN ('diproses','ditindaklanjuti','mediasi')")->fetch_assoc()['c'] ?? 0);
$selesai  = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_mahasiswa = $id_mahasiswa AND LOWER(status_laporan)='selesai'")->fetch_assoc()['c'] ?? 0);

// =============================================
//  LAPORAN TERBARU (7 hari terakhir, milik sendiri)
// =============================================
$laporan_terbaru = [];
$res = $conn->query("SELECT * FROM laporan WHERE id_mahasiswa = $id_mahasiswa AND tanggal_laporan >= (NOW() - INTERVAL 7 DAY) ORDER BY tanggal_laporan DESC LIMIT 5");
if ($res) while ($row = $res->fetch_assoc()) $laporan_terbaru[] = $row;

// =============================================
//  AKTIVITAS TERBARU (update status, milik sendiri)
// =============================================
$aktivitas_terbaru = [];
$res = $conn->query("SELECT * FROM laporan WHERE id_mahasiswa = $id_mahasiswa ORDER BY updated_at DESC LIMIT 5");
if ($res) while ($row = $res->fetch_assoc()) $aktivitas_terbaru[] = $row;

$status_badge = [
    'menunggu'        => 'badge-menunggu',
    'diproses'        => 'badge-diproses',
    'ditindaklanjuti' => 'badge-ditindaklanjuti',
    'mediasi'         => 'badge-mediasi',
    'selesai'         => 'badge-selesai',
];
function getBadgeD($status, $map) {
    return $map[strtolower(trim($status))] ?? 'badge-gray';
}
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
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
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
                <span class="card-num"><?= $total ?></span>
                <span class="card-title">Total Laporan</span>
            </div>
            <div class="card card-new">
                <span class="card-num"><?= $baru ?></span>
                <span class="card-title">Laporan Baru</span>
            </div>
            <div class="card card-process">
                <span class="card-num"><?= $proses ?></span>
                <span class="card-title">Dalam Proses</span>
            </div>
            <div class="card card-done">
                <span class="card-num"><?= $selesai ?></span>
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
                    <?php if (!empty($laporan_terbaru)): ?>
                    <a href="laporan.php" class="btn-view-all">Lihat Semua</a>
                    <?php endif; ?>
                </div>

                <?php if (empty($laporan_terbaru)): ?>
                <div class="empty-state">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p>Belum ada riwayat laporan yang dibuat.</p>
                </div>
                <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID Laporan</th>
                            <th>Jenis Kejadian</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($laporan_terbaru as $l):
                            $kode = $l['kode_laporan'] ?? 'KS-'.$l['id_laporan'];
                            $st   = getBadgeD($l['status_laporan'], $status_badge);
                            $tgl  = date('d/m/Y', strtotime($l['tanggal_laporan']));
                        ?>
                        <tr onclick="window.location='laporan.php?detail=<?= $l['id_laporan'] ?>'" style="cursor:pointer">
                            <td class="id-case">#<?= htmlspecialchars($kode) ?></td>
                            <td><?= htmlspecialchars($l['judul_laporan']) ?></td>
                            <td><span class="badge <?= $st ?>"><?= htmlspecialchars(ucfirst($l['status_laporan'])) ?></span></td>
                            <td><?= $tgl ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="activity-container">
                <h3>Aktivitas Terbaru</h3>
                <p class="activity-sub">Update status kasus</p>

                <?php if (empty($aktivitas_terbaru)): ?>
                <div class="empty-state" style="padding: 60px 20px;">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Tidak ada aktivitas terbaru.</p>
                </div>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($aktivitas_terbaru as $a):
                        $status = strtolower($a['status_laporan']);
                        $itemClass = $status === 'selesai' ? 'item-green' : ($status === 'menunggu' ? 'item-red' : ($status === 'mediasi' ? 'item-orange' : 'item-blue'));
                        $waktu = date('d M Y, H:i', strtotime($a['updated_at']));
                        $kode = $a['kode_laporan'] ?? 'KS-'.$a['id_laporan'];
                    ?>
                    <div class="timeline-item <?= $itemClass ?>">
                        <p class="timeline-text">
                            Laporan <strong>#<?= htmlspecialchars($kode) ?></strong> berstatus
                            <strong><?= htmlspecialchars(ucfirst($a['status_laporan'])) ?></strong>
                        </p>
                        <span class="timeline-time"><?= $waktu ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

        </div>

    </main>

</body>
</html>