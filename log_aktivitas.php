<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'investigasi') {
    header("Location: login.php");
    exit;
}
$username_aktif = $_SESSION['username'];
$inisial = '';
foreach (explode(' ', $username_aktif) as $part) { $inisial .= strtoupper(substr($part, 0, 1)); }
$inisial = substr($inisial, 0, 2) ?: 'TI';

// =============================================
//  FILTER
// =============================================
$id_laporan_filter = isset($_GET['laporan']) ? (int) $_GET['laporan'] : 0;
$search = trim($_GET['search'] ?? '');

// =============================================
//  AMBIL DATA LOG STATUS
// =============================================
$where_log = '1=1';
if ($id_laporan_filter > 0) $where_log .= " AND sl.id_laporan = $id_laporan_filter";

$sql_log = "SELECT 
                sl.id_log        AS id,
                sl.id_laporan,
                sl.status_lama,
                sl.status_baru,
                sl.catatan,
                sl.tanggal_update AS waktu,
                'status' AS tipe,
                l.kode_laporan,
                l.judul_laporan
            FROM status_laporan_log sl
            JOIN laporan l ON sl.id_laporan = l.id_laporan
            WHERE $where_log";

// =============================================
//  AMBIL DATA TINDAK LANJUT
// =============================================
$where_tl = '1=1';
if ($id_laporan_filter > 0) $where_tl .= " AND tl.id_laporan = $id_laporan_filter";

$sql_tl = "SELECT 
                tl.id_tindak_lanjut AS id,
                tl.id_laporan,
                tl.deskripsi_tindakan,
                tl.tanggal_tindakan AS waktu,
                'tindakan' AS tipe,
                l.kode_laporan,
                l.judul_laporan,
                u.username AS nama_tim
            FROM tindak_lanjut tl
            JOIN laporan l ON tl.id_laporan = l.id_laporan
            LEFT JOIN users u ON tl.id_tim = u.id_user
            WHERE $where_tl";

$timeline = [];

$res = $conn->query($sql_log);
if ($res) while ($row = $res->fetch_assoc()) $timeline[] = $row;

$res = $conn->query($sql_tl);
if ($res) while ($row = $res->fetch_assoc()) $timeline[] = $row;

// Urutkan gabungan berdasarkan waktu terbaru
usort($timeline, function($a, $b) {
    return strtotime($b['waktu']) <=> strtotime($a['waktu']);
});

// Filter pencarian (judul / kode)
if ($search !== '') {
    $timeline = array_filter($timeline, function($item) use ($search) {
        $s = strtolower($search);
        return str_contains(strtolower($item['judul_laporan']), $s)
            || str_contains(strtolower($item['kode_laporan']), $s);
    });
}

// =============================================
//  DAFTAR LAPORAN UNTUK DROPDOWN FILTER
// =============================================
$laporan_options = [];
$res = $conn->query("SELECT id_laporan, kode_laporan, judul_laporan FROM laporan ORDER BY tanggal_laporan DESC");
if ($res) while ($row = $res->fetch_assoc()) $laporan_options[] = $row;

// =============================================
//  STATISTIK RINGKAS
// =============================================
$total_log    = (int)($conn->query("SELECT COUNT(*) c FROM status_laporan_log")->fetch_assoc()['c'] ?? 0);
$total_tindak = (int)($conn->query("SELECT COUNT(*) c FROM tindak_lanjut")->fetch_assoc()['c'] ?? 0);
$hari_ini     = (int)($conn->query("SELECT COUNT(*) c FROM status_laporan_log WHERE DATE(tanggal_update) = CURDATE()")->fetch_assoc()['c'] ?? 0);

function statusBadgeClass($status) {
    $map = [
        'menunggu'        => 'status-new',
        'diproses'        => 'status-process',
        'ditindaklanjuti' => 'status-process',
        'mediasi'         => 'status-process',
        'selesai'         => 'status-done',
        'ditolak'         => 'status-rejected',
    ];
    return $map[strtolower(trim($status))] ?? 'status-new';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas Kasus – SIRAKELIKA</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .page-header-investigasi {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        .page-header-investigasi h2 { font-size: 24px; font-weight: 700; color: #0f172a; }
        .page-header-investigasi p  { font-size: 13px; color: #64748b; margin-top: 4px; }

        .status-rejected { background-color: #f1f5f9; color: #64748b; }

        /* ===================== TOOLBAR ===================== */
        .toolbar-log {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .select-laporan, .search-box-log input {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
        }

        .select-laporan {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 9px 12px;
            color: #475569;
            background: #fff;
            cursor: pointer;
            min-width: 240px;
        }

        .search-box-log {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 12px;
            color: #94a3b8;
            min-width: 220px;
        }
        .search-box-log input { border: none; outline: none; color: #1e293b; background: transparent; width: 100%; }

        .reset-link {
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            text-decoration: none;
            padding: 8px 4px;
        }
        .reset-link:hover { text-decoration: underline; }

        /* ===================== MINI STATS ===================== */
        .mini-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .mini-stat-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mini-stat-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .mini-stat-icon.blue   { background: #eff6ff; color: #2563eb; }
        .mini-stat-icon.purple { background: #faf5ff; color: #7c3aed; }
        .mini-stat-icon.orange { background: #fffbeb; color: #d97706; }

        .mini-stat-num { font-size: 20px; font-weight: 700; color: #0f172a; }
        .mini-stat-lbl { font-size: 12px; color: #64748b; }

        /* ===================== TIMELINE FULL ===================== */
        .timeline-container {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 24px;
        }

        .timeline-full {
            display: flex;
            flex-direction: column;
        }

        .tl-entry {
            display: flex;
            gap: 16px;
            position: relative;
            padding-bottom: 24px;
        }

        .tl-entry:last-child { padding-bottom: 0; }

        .tl-entry:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 17px;
            top: 36px;
            bottom: 0;
            width: 2px;
            background: #e2e8f0;
        }

        .tl-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            z-index: 1;
        }

        .tl-icon.status   { background: #eff6ff; color: #2563eb; }
        .tl-icon.tindakan { background: #f0fdf4; color: #16a34a; }

        .tl-content { flex: 1; padding-top: 4px; }

        .tl-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 6px;
        }

        .tl-title { font-size: 13.5px; font-weight: 600; color: #0f172a; line-height: 1.4; }
        .tl-title a { color: #2563eb; text-decoration: none; font-family: monospace; font-weight: 700; }
        .tl-title a:hover { text-decoration: underline; }

        .tl-time { font-size: 11px; color: #94a3b8; white-space: nowrap; }

        .tl-desc {
            font-size: 12.5px;
            color: #64748b;
            line-height: 1.6;
            background: #f8fafc;
            border-radius: 8px;
            padding: 10px 12px;
            margin-top: 6px;
        }

        .status-flow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            margin-top: 2px;
        }

        .status-flow .arrow { color: #cbd5e1; }

        .tl-meta {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .tl-meta strong { color: #475569; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo-area">
        <div class="logo-icon"></div>
        <div>
            <h1 class="logo-title">SIRAKELIKA</h1>
            <p class="logo-sub">PANEL INVESTIGASI</p>
        </div>
    </div>

    <nav class="nav-container">
        <div class="nav-group">NAVIGASI UTAMA</div>
        <a href="dashboard_investigasi.php" class="nav-link">
            <span class="nav-text">Dashboard Tim</span>
        </a>

        <div class="nav-group">PENGELOLAAN KASUS</div>
        <a href="manajemen_kasus.php" class="nav-link">
            <span class="nav-text">Manajemen Kasus Masuk</span>
        </a>
        <a href="log_aktivitas.php" class="nav-link active">
            <span class="nav-text">Log Aktivitas Kasus</span>
        </a>

        <div class="nav-group">AKUN SYSTEM</div>
        <a href="logout.php" class="nav-link logout" onclick="return confirm('Yakin ingin keluar?')">
            <span class="nav-text">Keluar</span>
        </a>
    </nav>
</aside>

<main class="main-content">

    <header class="topbar">
    </header>

    <div class="page-header-investigasi">
        <div>
            <h2>Log Aktivitas Kasus</h2>
            <p>Riwayat perubahan status dan catatan tindak lanjut seluruh kasus secara kronologis</p>
        </div>
    </div>

    <!-- MINI STATS -->
    <div class="mini-stats">
        <div class="mini-stat-card">
            <div class="mini-stat-icon blue">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
            </div>
            <div>
                <div class="mini-stat-num"><?= $total_log ?></div>
                <div class="mini-stat-lbl">Total Perubahan Status</div>
            </div>
        </div>
        <div class="mini-stat-card">
            <div class="mini-stat-icon purple">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
            </div>
            <div>
                <div class="mini-stat-num"><?= $total_tindak ?></div>
                <div class="mini-stat-lbl">Total Catatan Tindakan</div>
            </div>
        </div>
        <div class="mini-stat-card">
            <div class="mini-stat-icon orange">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
                <div class="mini-stat-num"><?= $hari_ini ?></div>
                <div class="mini-stat-lbl">Aktivitas Hari Ini</div>
            </div>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar-log">
        <select class="select-laporan" onchange="window.location='log_aktivitas.php?laporan='+this.value">
            <option value="0">Semua Kasus</option>
            <?php foreach ($laporan_options as $opt): ?>
            <option value="<?= $opt['id_laporan'] ?>" <?= $id_laporan_filter == $opt['id_laporan'] ? 'selected' : '' ?>>
                #<?= htmlspecialchars($opt['kode_laporan']) ?> — <?= htmlspecialchars($opt['judul_laporan']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <form method="GET" style="display:flex">
            <input type="hidden" name="laporan" value="<?= $id_laporan_filter ?>">
            <div class="search-box-log">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul atau kode kasus...">
            </div>
        </form>

        <?php if ($id_laporan_filter > 0 || $search !== ''): ?>
        <a href="log_aktivitas.php" class="reset-link">Reset Filter</a>
        <?php endif; ?>
    </div>

    <!-- TIMELINE -->
    <div class="timeline-container">
        <?php if (empty($timeline)): ?>
        <div class="empty-state">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>
            </svg>
            <p>Belum ada aktivitas yang tercatat untuk filter ini.</p>
        </div>
        <?php else: ?>
        <div class="timeline-full">
            <?php foreach ($timeline as $item): ?>
            <div class="tl-entry">
                <?php if ($item['tipe'] === 'status'): ?>
                <div class="tl-icon status">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                </div>
                <div class="tl-content">
                    <div class="tl-head">
                        <div class="tl-title">
                            Status diperbarui pada kasus
                            <a href="manajemen_kasus.php?search=<?= urlencode($item['kode_laporan']) ?>">#<?= htmlspecialchars($item['kode_laporan']) ?></a>
                        </div>
                        <span class="tl-time"><?= date('d M Y, H:i', strtotime($item['waktu'])) ?></span>
                    </div>
                    <div class="tl-meta"><strong><?= htmlspecialchars($item['judul_laporan']) ?></strong></div>
                    <div class="status-flow">
                        <span class="status-badge <?= statusBadgeClass($item['status_lama'] ?: 'menunggu') ?>"><?= htmlspecialchars(ucfirst($item['status_lama'] ?: '—')) ?></span>
                        <span class="arrow">→</span>
                        <span class="status-badge <?= statusBadgeClass($item['status_baru']) ?>"><?= htmlspecialchars(ucfirst($item['status_baru'])) ?></span>
                    </div>
                    <?php if (!empty($item['catatan'])): ?>
                    <div class="tl-desc"><?= nl2br(htmlspecialchars($item['catatan'])) ?></div>
                    <?php endif; ?>
                </div>

                <?php else: ?>
                <div class="tl-icon tindakan">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div class="tl-content">
                    <div class="tl-head">
                        <div class="tl-title">
                            Catatan tindak lanjut ditambahkan pada kasus
                            <a href="manajemen_kasus.php?search=<?= urlencode($item['kode_laporan']) ?>">#<?= htmlspecialchars($item['kode_laporan']) ?></a>
                        </div>
                        <span class="tl-time"><?= date('d M Y, H:i', strtotime($item['waktu'])) ?></span>
                    </div>
                    <div class="tl-meta">
                        <strong><?= htmlspecialchars($item['judul_laporan']) ?></strong>
                        <?php if (!empty($item['nama_tim'])): ?>
                        &middot; oleh <strong><?= htmlspecialchars($item['nama_tim']) ?></strong>
                        <?php endif; ?>
                    </div>
                    <div class="tl-desc"><?= nl2br(htmlspecialchars($item['deskripsi_tindakan'])) ?></div>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</main>
</body>
</html>