<?php

include "connection.php";

$progres_labels = ['Diterima', 'Diproses', 'Ditindaklanjuti', 'Mediasi', 'Selesai'];

$status_progres = [
    'menunggu'        => 0,
    'diproses'        => 1,
    'ditindaklanjuti' => 2,
    'mediasi'         => 3,
    'selesai'         => 4,
];

$status_color = [
    'menunggu'        => 'baru',
    'diproses'        => 'verifikasi',
    'ditindaklanjuti' => 'investigasi',
    'mediasi'         => 'mediasi',
    'selesai'         => 'selesai',
];

function getProgres($status, $map) {
    return $map[strtolower(trim($status))] ?? 0;
}
function getColor($status, $map) {
    return $map[strtolower(trim($status))] ?? 'baru';
}

// =============================================
//  PARAMETER URL
// =============================================
$filter      = $_GET['filter']  ?? 'semua';
$search      = trim($_GET['search'] ?? '');
$detail_id   = isset($_GET['detail']) ? (int)$_GET['detail'] : null;
$success_msg = $_GET['success'] ?? '';

// =============================================
//  QUERY DATABASE
// =============================================
$where = '1=1';

if ($filter === 'baru')         $where .= " AND LOWER(status_laporan) = 'menunggu'";
elseif ($filter === 'diproses') $where .= " AND LOWER(status_laporan) IN ('diproses','ditindaklanjuti','mediasi')";
elseif ($filter === 'selesai')  $where .= " AND LOWER(status_laporan) = 'selesai'";

if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where .= " AND (judul_laporan LIKE '%$s%' OR deskripsi LIKE '%$s%' OR kode_laporan LIKE '%$s%')";
}

$laporan_list = [];
$res = $conn->query("SELECT * FROM laporan WHERE $where ORDER BY tanggal_laporan DESC");
if ($res) while ($row = $res->fetch_assoc()) $laporan_list[] = $row;

// Statistik
$total    = (int)($conn->query("SELECT COUNT(*) c FROM laporan")->fetch_assoc()['c'] ?? 0);
$diproses = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE LOWER(status_laporan) IN ('diproses','ditindaklanjuti','mediasi')")->fetch_assoc()['c'] ?? 0);
$selesai  = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE LOWER(status_laporan)='selesai'")->fetch_assoc()['c'] ?? 0);

// Detail
$detail = null;
if ($detail_id) {
    $res = $conn->query("SELECT * FROM laporan WHERE id_laporan = $detail_id LIMIT 1");
    if ($res && $res->num_rows) $detail = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya – SIRAKELIKA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="laporan.css">
</head>
<body>

<!-- ========== SIDEBAR ========== -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
        </div>
        <div class="logo-text">
            <span class="logo-name">SIRAKELIKA</span>
            <span class="logo-sub">Pelaporan Kekerasan Kampus</span>
        </div>
    </div>

    <nav class="sidebar-nav">
        <p class="nav-label">MENU UTAMA</p>
        <a href="dashboard.php" class="nav-item">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            <span>Dashboard</span>
        </a>
        <a href="laporan.php" class="nav-item active">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
            <span>Laporan Saya</span>
        </a>

        <p class="nav-label">PENGELOLAAN</p>
        <a href="manajemen.php" class="nav-item">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                <path d="M16 3.13a4 4 0 010 7.75"/>
            </svg>
            <span>Manajemen Kasus</span>
        </a>
        <a href="edukasi.php" class="nav-item">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <span>Edukasi &amp; Informasi</span>
        </a>

        <p class="nav-label">AKUN</p>
        <a href="profil.php" class="nav-item">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Profil</span>
        </a>
        <a href="pengaturan.php" class="nav-item">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.07 4.93l-1.41 1.41M6.34 17.66l-1.41 1.41M2 12h2M20 12h2M17.66 6.34l1.41-1.41M4.93 19.07l1.41-1.41M12 2v2M12 20v2"/>
            </svg>
            <span>Pengaturan</span>
        </a>
        <a href="logout.php" class="nav-item nav-danger" onclick="return confirm('Yakin ingin keluar?')">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                <polyline points="16,17 21,12 16,7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Keluar</span>
        </a>
    </nav>
</aside>

<!-- ========== MAIN ========== -->
<main class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="breadcrumb">
            <a href="dashboard.php">Beranda</a>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg>
            <span>Laporan Saya</span>
        </div>
        <div class="topbar-right">
            <button class="icon-btn" title="Notifikasi">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
            </button>
            <div class="user-chip" onclick="this.classList.toggle('open')">
                <div class="avatar">MA</div>
                <div class="user-info">
                    <span class="user-name">M. Alif</span>
                    <span class="user-role">Mahasiswa</span>
                </div>
                <svg class="chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6,9 12,15 18,9"/></svg>
                <div class="user-dropdown">
                    <a href="profil.php">Profil Saya</a>
                    <a href="pengaturan.php">Pengaturan</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-danger" onclick="return confirm('Keluar?')">Keluar</a>
                </div>
            </div>
        </div>
    </header>

    <!-- CONTENT -->
    <div class="content">

        <?php if ($success_msg): ?>
        <div class="alert-success" id="successAlert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
            <?= htmlspecialchars($success_msg) ?>
            <button onclick="document.getElementById('successAlert').remove()" class="alert-close">×</button>
        </div>
        <?php endif; ?>

        <?php if ($detail): ?>
        <!-- ========================================
             HALAMAN DETAIL
             ======================================== -->
        <?php
            $st     = getColor($detail['status_laporan'], $status_color);
            $pg     = getProgres($detail['status_laporan'], $status_progres);
            $tgl    = date('d M Y, H:i', strtotime($detail['waktu_kejadian'] ?? $detail['tanggal_laporan']));
            $dibuat = date('d M Y', strtotime($detail['tanggal_laporan']));
        ?>
        <div class="page-head">
            <div>
                <h1 class="page-title">Detail Laporan</h1>
                <p class="page-sub">
                    <span class="mono">#<?= htmlspecialchars($detail['kode_laporan'] ?? 'KS-'.$detail['id_laporan']) ?></span>
                    &nbsp;·&nbsp; Dibuat <?= $dibuat ?>
                </p>
            </div>
            <a href="laporan.php" class="btn btn-outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15,18 9,12 15,6"/></svg>
                Kembali
            </a>
        </div>

        <div class="detail-layout">
            <!-- KIRI -->
            <div class="detail-main">
                <div class="card">
                    <div class="card-top">
                        <span class="mono text-muted">#<?= htmlspecialchars($detail['kode_laporan'] ?? 'KS-'.$detail['id_laporan']) ?></span>
                        <div class="tag-group">
                            <span class="tag tag-<?= $st ?>"><?= htmlspecialchars(ucfirst($detail['status_laporan'])) ?></span>
                            <?php if (!empty($detail['jenis_kekerasan'])): ?>
                            <span class="tag tag-gray"><?= htmlspecialchars($detail['jenis_kekerasan']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <h2 class="detail-title"><?= htmlspecialchars($detail['judul_laporan'] ?? '-') ?></h2>
                    <p class="detail-desc"><?= nl2br(htmlspecialchars($detail['deskripsi'])) ?></p>
                    <div class="meta-row">
                        <?php if (!empty($detail['lokasi_kejadian'])): ?>
                        <span class="meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($detail['lokasi_kejadian']) ?>
                        </span>
                        <?php endif; ?>
                        <span class="meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                            <?= $tgl ?>
                        </span>
                        <?php if (!empty($detail['jenis_pelaporan'])): ?>
                        <span class="meta-item">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                            <?= htmlspecialchars($detail['jenis_pelaporan']) ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Progres -->
                <div class="card">
                    <p class="section-label">PROGRES PENANGANAN</p>
                    <div class="progres-bar">
                        <?php foreach ($progres_labels as $i => $lbl): ?>
                        <div class="ps <?= $i < $pg ? 'done' : ($i === $pg ? 'active' : '') ?>">
                            <div class="ps-circle">
                                <?php if ($i < $pg): ?>
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20,6 9,17 4,12"/></svg>
                                <?php elseif ($i === $pg): ?>
                                <div class="ps-dot"></div>
                                <?php endif; ?>
                            </div>
                            <?php if ($i < count($progres_labels) - 1): ?>
                            <div class="ps-line <?= $i < $pg ? 'done' : '' ?>"></div>
                            <?php endif; ?>
                            <span class="ps-lbl"><?= $lbl ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (strtolower($detail['status_laporan']) === 'selesai'): ?>
                    <div class="selesai-note">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
                        Laporan ini telah selesai ditangani
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- KANAN -->
            <div class="detail-side">
                <div class="card">
                    <p class="section-label">TINDAKAN</p>
                    <?php if (strtolower($detail['status_laporan']) !== 'selesai'): ?>
                    <button class="btn btn-outline w-full" onclick="openModal('modalEdit')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Laporan
                    </button>
                    <button class="btn btn-danger w-full" onclick="openModal('modalHapus')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                        Tarik Laporan
                    </button>
                    <?php else: ?>
                    <p class="text-muted" style="font-size:13px;text-align:center;padding:8px 0">Laporan telah selesai diproses.</p>
                    <?php endif; ?>
                    <button class="btn btn-outline w-full" onclick="window.print()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 6,2 18,2 18,9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Cetak
                    </button>
                </div>

                <div class="card">
                    <p class="section-label">INFO LAPORAN</p>
                    <div class="info-list">
                        <div class="info-row">
                            <span>ID</span>
                            <strong class="mono">#<?= htmlspecialchars($detail['kode_laporan'] ?? $detail['id_laporan']) ?></strong>
                        </div>
                        <div class="info-row">
                            <span>Dibuat</span>
                            <strong><?= $dibuat ?></strong>
                        </div>
                        <div class="info-row">
                            <span>Status</span>
                            <span class="tag tag-<?= $st ?> tag-sm"><?= htmlspecialchars(ucfirst($detail['status_laporan'])) ?></span>
                        </div>
                        <?php if (!empty($detail['jenis_kekerasan'])): ?>
                        <div class="info-row">
                            <span>Jenis</span>
                            <strong><?= htmlspecialchars($detail['jenis_kekerasan']) ?></strong>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($detail['jenis_pelaporan'])): ?>
                        <div class="info-row">
                            <span>Pelaporan</span>
                            <strong><?= htmlspecialchars($detail['jenis_pelaporan']) ?></strong>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Edit -->
        <div class="modal-overlay" id="modalEdit" onclick="if(event.target===this)closeModal('modalEdit')">
            <div class="modal">
                <div class="modal-head">
                    <h3>Edit Laporan</h3>
                    <button onclick="closeModal('modalEdit')" class="modal-close">×</button>
                </div>
                <div class="modal-body">
                    <p>Fitur edit laporan akan segera tersedia. Hubungi tim kami untuk perubahan mendesak.</p>
                </div>
                <div class="modal-foot">
                    <button class="btn btn-outline" onclick="closeModal('modalEdit')">Tutup</button>
                </div>
            </div>
        </div>

        <!-- Modal Hapus -->
        <div class="modal-overlay" id="modalHapus" onclick="if(event.target===this)closeModal('modalHapus')">
            <div class="modal">
                <div class="modal-head">
                    <h3>Tarik Laporan</h3>
                    <button onclick="closeModal('modalHapus')" class="modal-close">×</button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menarik laporan <strong>#<?= htmlspecialchars($detail['kode_laporan'] ?? $detail['id_laporan']) ?></strong>? Tindakan ini tidak dapat dibatalkan.</p>
                </div>
                <div class="modal-foot">
                    <button class="btn btn-outline" onclick="closeModal('modalHapus')">Batal</button>
                    <a href="laporan.php?success=Laporan+berhasil+ditarik" class="btn btn-danger">Ya, Tarik</a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- ========================================
             HALAMAN DAFTAR LAPORAN
             ======================================== -->
        <div class="page-head">
            <div>
                <h1 class="page-title">Laporan Saya</h1>
                <p class="page-sub">Pantau status dan riwayat laporan yang telah kamu buat</p>
            </div>
            <a href="buat_laporan.php" class="btn btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Buat Laporan Baru
            </a>
        </div>

        <!-- STATISTIK -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
                </div>
                <div>
                    <div class="stat-num"><?= $total ?></div>
                    <div class="stat-lbl">Total Laporan Saya</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                </div>
                <div>
                    <div class="stat-num"><?= $diproses ?></div>
                    <div class="stat-lbl">Sedang Diproses</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22,4 12,14.01 9,11.01"/></svg>
                </div>
                <div>
                    <div class="stat-num"><?= $selesai ?></div>
                    <div class="stat-lbl">Selesai Ditangani</div>
                </div>
            </div>
        </div>

        <!-- FILTER & SEARCH -->
        <div class="toolbar">
            <div class="tabs">
                <?php foreach (['semua'=>'Semua','baru'=>'Menunggu','diproses'=>'Diproses','selesai'=>'Selesai'] as $k=>$v): ?>
                <a href="laporan.php?filter=<?= $k ?><?= $search ? '&search='.urlencode($search) : '' ?>"
                   class="tab <?= $filter===$k ? 'active':'' ?>"><?= $v ?></a>
                <?php endforeach; ?>
            </div>
            <form method="GET" class="search-form" autocomplete="off">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                <div class="search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari laporan...">
                    <?php if ($search): ?><a href="laporan.php?filter=<?= $filter ?>" class="clear-btn" title="Hapus pencarian">×</a><?php endif; ?>
                </div>
            </form>
        </div>

        <!-- DAFTAR LAPORAN -->
        <?php if (empty($laporan_list)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14,2 14,8 20,8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
            </div>
            <h3><?= $search || $filter !== 'semua' ? 'Tidak ada laporan ditemukan' : 'Belum ada laporan' ?></h3>
            <p><?= $search || $filter !== 'semua' ? 'Coba ubah filter atau kata kunci pencarian.' : 'Laporan yang dibuat akan muncul di sini.' ?></p>
            <?php if ($search || $filter !== 'semua'): ?>
            <a href="laporan.php" class="btn btn-outline" style="margin-top:4px">Reset Filter</a>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div class="laporan-list">
        <?php foreach ($laporan_list as $l):
            $st  = getColor($l['status_laporan'], $status_color);
            $pg  = getProgres($l['status_laporan'], $status_progres);
            $tgl = !empty($l['waktu_kejadian']) ? date('d M Y, H:i', strtotime($l['waktu_kejadian'])) : '-';
            $tgl_laporan = date('d M Y', strtotime($l['tanggal_laporan']));
            $id_display  = $l['kode_laporan'] ?? 'KS-'.$l['id_laporan'];
        ?>
        <div class="laporan-card" onclick="window.location='laporan.php?detail=<?= $l['id_laporan'] ?>'">
            <div class="lc-head">
                <div class="lc-left">
                    <span class="mono text-muted">#<?= htmlspecialchars($id_display) ?></span>
                    <span class="tag tag-<?= $st ?>"><?= htmlspecialchars(ucfirst($l['status_laporan'])) ?></span>
                    <?php if (!empty($l['jenis_kekerasan'])): ?>
                    <span class="tag tag-gray"><?= htmlspecialchars($l['jenis_kekerasan']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="lc-right">
                    <span class="text-muted" style="font-size:12px"><?= $tgl_laporan ?></span>
                    <a href="laporan.php?detail=<?= $l['id_laporan'] ?>" class="btn-detail" onclick="event.stopPropagation()">
                        Lihat Detail
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9,18 15,12 9,6"/></svg>
                    </a>
                </div>
            </div>

            <h3 class="lc-title"><?= htmlspecialchars($l['judul_laporan'] ?? '-') ?></h3>
            <p class="lc-desc"><?= htmlspecialchars($l['deskripsi']) ?></p>

            <div class="lc-meta">
                <?php if (!empty($l['lokasi_kejadian'])): ?>
                <span class="meta-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <?= htmlspecialchars($l['lokasi_kejadian']) ?>
                </span>
                <?php endif; ?>
                <span class="meta-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                    <?= $tgl ?>
                </span>
                <?php if (!empty($l['jenis_pelaporan'])): ?>
                <span class="meta-item">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                    <?= htmlspecialchars($l['jenis_pelaporan']) ?>
                </span>
                <?php endif; ?>
            </div>

            <!-- PROGRES -->
            <div class="lc-progres">
                <p class="progres-title">PROGRES PENANGANAN</p>
                <div class="progres-bar">
                    <?php foreach ($progres_labels as $i => $lbl): ?>
                    <div class="ps <?= $i < $pg ? 'done' : ($i === $pg ? 'active' : '') ?>">
                        <div class="ps-circle">
                            <?php if ($i < $pg): ?>
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20,6 9,17 4,12"/></svg>
                            <?php elseif ($i === $pg): ?>
                            <div class="ps-dot"></div>
                            <?php endif; ?>
                        </div>
                        <?php if ($i < count($progres_labels) - 1): ?>
                        <div class="ps-line <?= $i < $pg ? 'done' : '' ?>"></div>
                        <?php endif; ?>
                        <span class="ps-lbl"><?= $lbl ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if (strtolower($l['status_laporan']) === 'selesai'): ?>
                <p class="selesai-chip">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
                    Diselesaikan / Resolusi
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div><!-- /content -->
</main>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(m => {
            m.classList.remove('show');
            document.body.style.overflow = '';
        });
    }
});
// Auto-search on typing
const searchInput = document.querySelector('.search-box input');
if (searchInput) {
    let timer;
    searchInput.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => searchInput.closest('form').submit(), 500);
    });
}
</script>
</body>
</html>