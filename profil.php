<?php
session_start();
include "connection.php";

if (!isset($_SESSION['id_mahasiswa'])) {
    header("Location: login.php");
    exit;
}
$id_mahasiswa = (int) $_SESSION['id_mahasiswa'];

$status_progres = [
    'menunggu'        => 0,
    'diproses'        => 1,
    'ditindaklanjuti' => 2,
    'mediasi'         => 3,
    'selesai'         => 4,
];

$status_badge = [
    'menunggu'        => 'badge-menunggu',
    'diproses'        => 'badge-diproses',
    'ditindaklanjuti' => 'badge-ditindaklanjuti',
    'mediasi'         => 'badge-mediasi',
    'selesai'         => 'badge-selesai',
];

function getBadge($status, $map) {
    return $map[strtolower(trim($status))] ?? 'badge-gray';
}

// =============================================
//  AMBIL DATA MAHASISWA
// =============================================
$res = $conn->query("SELECT * FROM mahasiswa WHERE id_mahasiswa = $id_mahasiswa LIMIT 1");
$mhs = $res ? $res->fetch_assoc() : null;

if (!$mhs) {
    header("Location: login.php");
    exit;
}

$nama_mhs   = $mhs['nama_mahasiswa'];
$inisial    = '';
foreach (explode(' ', $nama_mhs) as $part) { $inisial .= strtoupper(substr($part, 0, 1)); }
$inisial    = substr($inisial, 0, 2);
$bergabung  = date('M Y', strtotime($mhs['created_at']));

// =============================================
//  STATISTIK LAPORAN
// =============================================
$total    = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_mahasiswa = $id_mahasiswa")->fetch_assoc()['c'] ?? 0);
$diproses = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_mahasiswa = $id_mahasiswa AND LOWER(status_laporan) IN ('diproses','ditindaklanjuti','mediasi')")->fetch_assoc()['c'] ?? 0);
$selesai  = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_mahasiswa = $id_mahasiswa AND LOWER(status_laporan)='selesai'")->fetch_assoc()['c'] ?? 0);

// =============================================
//  RINGKASAN LAPORAN (terbaru, max 6)
// =============================================
$laporan_list = [];
$res = $conn->query("SELECT * FROM laporan WHERE id_mahasiswa = $id_mahasiswa ORDER BY tanggal_laporan DESC LIMIT 6");
if ($res) while ($row = $res->fetch_assoc()) $laporan_list[] = $row;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya – SIRAKELIKA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="laporan.css">
    <style>
        /* ===================== PROFIL HEADER ===================== */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 20px;
            margin-bottom: 24px;
        }

        .profile-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #3b82f6;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .profile-tags {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }

        .profile-tag {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #475569;
        }

        .profile-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 32px;
            margin-bottom: 16px;
        }

        .profile-label {
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .profile-value {
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
        }

        .status-dot {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #16a34a;
        }

        .status-dot::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #16a34a;
        }

        /* ===================== PROFILE STATS (right column) ===================== */
        .profile-stats {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .pstat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #cbd5e1;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .pstat-card.blue   { border-left-color: #3b82f6; }
        .pstat-card.orange { border-left-color: #f59e0b; }
        .pstat-card.green  { border-left-color: #10b981; }

        .pstat-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pstat-card.blue .pstat-icon   { background: #eff6ff; color: #3b82f6; }
        .pstat-card.orange .pstat-icon { background: #fffbeb; color: #f59e0b; }
        .pstat-card.green .pstat-icon  { background: #f0fdf4; color: #10b981; }

        .pstat-num { font-size: 22px; font-weight: 700; color: #0f172a; }
        .pstat-lbl { font-size: 12px; color: #64748b; }

        /* ===================== TWO COLUMN BOTTOM ===================== */
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1.6fr;
            gap: 20px;
            align-items: start;
        }

        .panel {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 24px;
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .panel-head h3 {
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
        }

        .panel-link {
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
        }

        .panel-link:hover { text-decoration: underline; }

        /* ACTIVITY LIST */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .activity-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 13px;
            color: #334155;
        }

        .activity-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-top: 4px;
            flex-shrink: 0;
        }

        .activity-dot.green  { background: #10b981; }
        .activity-dot.orange { background: #f59e0b; }
        .activity-dot.blue   { background: #3b82f6; }

        .activity-text { flex: 1; }
        .activity-time {
            font-size: 11px;
            color: #94a3b8;
            white-space: nowrap;
        }

        /* RINGKASAN TABLE */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .summary-table th {
            text-align: left;
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 0 8px 10px;
            border-bottom: 1px solid #f1f5f9;
        }

        .summary-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .summary-table tr:last-child td { border-bottom: none; }

        .summary-id {
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #2563eb;
            font-size: 12px;
        }

        .summary-link {
            color: #2563eb;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .summary-link:hover { text-decoration: underline; }

        @media (max-width: 900px) {
            .profile-grid, .bottom-grid { grid-template-columns: 1fr; }
            .profile-card { flex-direction: column; text-align: center; }
            .profile-info-grid { grid-template-columns: 1fr; text-align: left; }
        }
    </style>
</head>
<body>

<!-- ========== SIDEBAR ========== -->
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
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="laporan.php" class="nav-link">Laporan Saya</a>

        <div class="nav-group">PENGELOLAAN</div>
        <a href="manajemen.php" class="nav-link">Manajemen Kasus</a>
        <a href="edukasi.php" class="nav-link">Edukasi &amp; Informasi</a>
        <a href="kenali.php" class="nav-link">Kenali Situasi Anda</a>

        <div class="nav-group">AKUN</div>
        <a href="profil.php" class="nav-link active">Profil</a>
        <a href="pengaturan.php" class="nav-link">Pengaturan</a>
        <a href="logout.php" class="nav-link logout" onclick="return confirm('Yakin ingin keluar?')">Keluar</a>
    </nav>
</aside>

<!-- ========== MAIN ========== -->
<main class="main-content">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="breadcrumb">
            <a href="dashboard.php">Beranda</a>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg>
            <span>Profil</span>
        </div>
        <div class="user-profile">
            <div class="notif-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
            </div>
            <div class="avatar"><?= htmlspecialchars($inisial) ?></div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($nama_mhs) ?></span>
                <span class="user-role">Mahasiswa</span>
            </div>
        </div>
    </header>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div>
            <h2>Profil Saya</h2>
            <p>Kelola informasi akun dan pantau aktivitas laporanmu</p>
        </div>
    </div>

    <!-- ===== PROFIL UTAMA + STATISTIK ===== -->
    <div class="profile-grid">

        <!-- KIRI: INFO PROFIL -->
        <div class="profile-card">
            <div class="profile-avatar"><?= htmlspecialchars($inisial) ?></div>
            <div style="flex:1">
                <div class="profile-tags">
                    <span class="profile-tag">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                        Mahasiswa
                    </span>
                    <span class="profile-tag">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Bergabung <?= $bergabung ?>
                    </span>
                </div>

                <div class="profile-info-grid">
                    <div>
                        <p class="profile-label">NAMA LENGKAP</p>
                        <p class="profile-value"><?= htmlspecialchars($nama_mhs) ?></p>
                    </div>
                    <div>
                        <p class="profile-label">NIM</p>
                        <p class="profile-value"><?= htmlspecialchars($mhs['nim']) ?></p>
                    </div>
                    <div>
                        <p class="profile-label">EMAIL</p>
                        <p class="profile-value"><?= htmlspecialchars($mhs['email']) ?></p>
                    </div>
                    <div>
                        <p class="profile-label">NO. HP</p>
                        <p class="profile-value"><?= htmlspecialchars($mhs['no_hp'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="profile-label">STATUS AKUN</p>
                        <p class="status-dot"><?= $mhs['status_akun'] === 'aktif' ? 'Aktif &amp; Terverifikasi' : 'Nonaktif' ?></p>
                    </div>
                </div>

                <a href="edit_profil.php" class="btn btn-outline">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit Profil
                </a>
            </div>
        </div>

        <!-- KANAN: STATISTIK -->
        <div class="profile-stats">
            <div class="pstat-card blue">
                <div class="pstat-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                </div>
                <div>
                    <div class="pstat-num"><?= $total ?></div>
                    <div class="pstat-lbl">Total Laporan</div>
                </div>
            </div>
            <div class="pstat-card orange">
                <div class="pstat-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                </div>
                <div>
                    <div class="pstat-num"><?= $diproses ?></div>
                    <div class="pstat-lbl">Sedang Diproses</div>
                </div>
            </div>
            <div class="pstat-card green">
                <div class="pstat-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/></svg>
                </div>
                <div>
                    <div class="pstat-num"><?= $selesai ?></div>
                    <div class="pstat-lbl">Selesai Ditangani</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== AKTIVITAS TERBARU + RINGKASAN LAPORAN ===== -->
    <div class="bottom-grid">

        <!-- AKTIVITAS TERBARU -->
        <div class="panel">
            <div class="panel-head">
                <h3>Aktivitas Terbaru</h3>
                <a href="laporan.php" class="panel-link">Lihat Semua &rarr;</a>
            </div>

            <?php if (empty($laporan_list)): ?>
            <div class="empty-state" style="border:none;padding:30px 10px">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
                </svg>
                <p>Belum ada aktivitas.</p>
            </div>
            <?php else: ?>
            <div class="activity-list">
                <?php foreach ($laporan_list as $l):
                    $status = strtolower($l['status_laporan']);
                    $dotClass = $status === 'selesai' ? 'green' : ($status === 'menunggu' ? 'orange' : 'blue');
                    $waktu = date('d M Y', strtotime($l['tanggal_laporan']));
                ?>
                <div class="activity-row">
                    <div class="activity-dot <?= $dotClass ?>"></div>
                    <div class="activity-text">
                        <strong><?= htmlspecialchars($l['judul_laporan']) ?></strong>
                        &mdash; Status: <?= htmlspecialchars(ucfirst($l['status_laporan'])) ?>
                    </div>
                    <div class="activity-time"><?= $waktu ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- RINGKASAN LAPORAN -->
        <div class="panel">
            <div class="panel-head">
                <h3>Ringkasan Laporan</h3>
                <a href="laporan.php" class="panel-link">Lihat Semua &rarr;</a>
            </div>

            <?php if (empty($laporan_list)): ?>
            <div class="empty-state" style="border:none;padding:30px 10px">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p>Belum ada laporan.</p>
            </div>
            <?php else: ?>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>ID Laporan</th>
                        <th>Jenis Kekerasan</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporan_list as $l):
                        $st   = getBadge($l['status_laporan'], $status_badge);
                        $kode = $l['kode_laporan'] ?? 'KS-'.$l['id_laporan'];
                        $tgl  = date('d/m/Y', strtotime($l['tanggal_laporan']));
                    ?>
                    <tr>
                        <td class="summary-id">#<?= htmlspecialchars($kode) ?></td>
                        <td><?= htmlspecialchars(ucfirst($l['jenis_kekerasan'])) ?></td>
                        <td><span class="badge <?= $st ?>"><?= htmlspecialchars(ucfirst($l['status_laporan'])) ?></span></td>
                        <td><?= $tgl ?></td>
                        <td><a href="laporan.php?detail=<?= $l['id_laporan'] ?>" class="summary-link">Lihat detail</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

</main>
</body>
</html>