<?php
session_start();

// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");
include '../config/conn.php';


// Hanya mahasiswa yang login boleh mengakses halaman ini
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
$id_user = (int) $_SESSION['id_user'];

// Ambil data nama untuk ditampilkan di topbar
$res_user = $conn->query("SELECT username FROM users WHERE id_user = $id_user LIMIT 1");
$user_row = $res_user ? $res_user->fetch_assoc() : null;
$nama_user = $user_row['username'] ?? 'Pengguna';
$inisial_user = '';
foreach (explode(' ', $nama_user) as $part) { $inisial_user .= strtoupper(substr($part, 0, 1)); }
$inisial_user = substr($inisial_user, 0, 2);

$progres_labels = ['Diterima', 'Diproses', 'Ditindaklanjuti', 'Mediasi', 'Selesai'];

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

function getProgres($status, $map) {
    return $map[strtolower(trim($status))] ?? 0;
}
function getBadge($status, $map) {
    return $map[strtolower(trim($status))] ?? 'badge-gray';
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
$where = "id_user = $id_user";

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
$total    = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_user = $id_user")->fetch_assoc()['c'] ?? 0);
$diproses = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_user = $id_user AND LOWER(status_laporan) IN ('diproses','ditindaklanjuti','mediasi')")->fetch_assoc()['c'] ?? 0);
$selesai  = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE id_user = $id_user AND LOWER(status_laporan)='selesai'")->fetch_assoc()['c'] ?? 0);

// Detail
$detail = null;
if ($detail_id) {
    $res = $conn->query("SELECT * FROM laporan WHERE id_laporan = $detail_id AND id_user = $id_user LIMIT 1");
    if ($res && $res->num_rows) $detail = $res->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Saya – SIRAKELIKA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="laporan.css">
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
        <a href="laporan_saya.php" class="nav-link active">Laporan Saya</a>

        <div class="nav-group">PENGELOLAAN</div>
        
        <a href="edukasi1.php" class="nav-link">Edukasi &amp; Informasi</a>
        <a href="kenali.php" class="nav-link">Kenali Situasi Anda</a>

        <div class="nav-group">AKUN</div>
        <a href="profil.php" class="nav-link">Profil</a>
  
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
            <span>Laporan Saya</span>
        </div>
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

    <!-- ====== ALERT ====== -->
    <?php if ($success_msg): ?>
    <div class="alert-success" id="successAlert">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
        <?= htmlspecialchars($success_msg) ?>
        <button onclick="document.getElementById('successAlert').remove()" class="alert-close">×</button>
    </div>
    <?php endif; ?>

    <?php if ($detail): ?>
    <!-- ============================================================
         HALAMAN DETAIL
         ============================================================ -->
    <?php
        $st     = getBadge($detail['status_laporan'], $status_badge);
        $pg     = getProgres($detail['status_laporan'], $status_progres);
        $tgl    = date('d M Y, H:i', strtotime($detail['waktu_kejadian'] ?? $detail['tanggal_laporan']));
        $dibuat = date('d M Y', strtotime($detail['tanggal_laporan']));
        $kode   = $detail['kode_laporan'] ?? 'KS-'.$detail['id_laporan'];
    ?>

    <div class="page-header">
        <div>
            <h2>Detail Laporan</h2>
            <p>#<?= htmlspecialchars($kode) ?> &nbsp;·&nbsp; Dibuat <?= $dibuat ?></p>
        </div>
        <a href="laporan.php" class="btn btn-outline">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15,18 9,12 15,6"/></svg>
            Kembali
        </a>
    </div>

    <div class="detail-grid">

        <!-- KIRI -->
        <div>
            <!-- Info utama -->
            <div class="card-box status-<?= strtolower(trim($detail['status_laporan'])) ?>" style="border-left:4px solid;">
                <div class="card-top">
                    <span class="lc-id">#<?= htmlspecialchars($kode) ?></span>
                    <div class="tag-group">
                        <span class="badge <?= $st ?>"><?= htmlspecialchars(ucfirst($detail['status_laporan'])) ?></span>
                        <?php if (!empty($detail['jenis_kekerasan'])): ?>
                        <span class="badge badge-gray"><?= htmlspecialchars($detail['jenis_kekerasan']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <h3 class="detail-title"><?= htmlspecialchars($detail['judul_laporan'] ?? '-') ?></h3>
                <p class="detail-desc"><?= nl2br(htmlspecialchars($detail['deskripsi'])) ?></p>
                <div class="lc-meta">
                    <?php if (!empty($detail['lokasi_kejadian'])): ?>
                    <span class="meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?= htmlspecialchars($detail['lokasi_kejadian']) ?>
                    </span>
                    <?php endif; ?>
                    <span class="meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/></svg>
                        <?= $tgl ?>
                    </span>
                    <?php if (!empty($detail['jenis_pelaporan'])): ?>
                    <span class="meta-item">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                        <?= htmlspecialchars($detail['jenis_pelaporan']) ?>
                    </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Progres -->
            <div class="card-box">
                <p class="section-label">PROGRES PENANGANAN</p>
                <div class="progress-bar">
                    <?php foreach ($progres_labels as $i => $lbl): ?>
                    <div class="ps <?= $i < $pg ? 'done' : ($i === $pg ? 'active' : '') ?>">
                        <div class="ps-circle">
                            <?php if ($i < $pg): ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20,6 9,17 4,12"/></svg>
                            <?php elseif ($i === $pg): ?>
                            <div class="ps-dot"></div>
                            <?php endif; ?>
                        </div>
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
        <div>
            <!-- Tindakan -->
            <div class="card-box">
                <p class="section-label">TINDAKAN</p>
                <div class="action-btns">
                    <?php if (strtolower($detail['status_laporan']) !== 'selesai'): ?>
                    <button class="btn btn-outline" onclick="openModal('modalEdit')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Laporan
                    </button>
                    <button class="btn btn-danger" onclick="openModal('modalHapus')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3,6 5,6 21,6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                        Tarik Laporan
                    </button>
                    <?php else: ?>
                    <p style="font-size:13px;color:#94a3b8;text-align:center;padding:8px 0">Laporan telah selesai diproses.</p>
                    <?php endif; ?>
                    <button class="btn btn-outline" onclick="window.print()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6,9 6,2 18,2 18,9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Cetak
                    </button>
                </div>
            </div>

            <!-- Info Laporan -->
            <div class="card-box">
                <p class="section-label">INFO LAPORAN</p>
                <div class="info-list">
                    <div class="info-row">
                        <span>ID Laporan</span>
                        <strong style="font-family:monospace">#<?= htmlspecialchars($kode) ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Tanggal Buat</span>
                        <strong><?= $dibuat ?></strong>
                    </div>
                    <div class="info-row">
                        <span>Status</span>
                        <span class="badge <?= $st ?>"><?= htmlspecialchars(ucfirst($detail['status_laporan'])) ?></span>
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

    <!-- Modal Tarik -->
    <div class="modal-overlay" id="modalHapus" onclick="if(event.target===this)closeModal('modalHapus')">
        <div class="modal">
            <div class="modal-head">
                <h3>Tarik Laporan</h3>
                <button onclick="closeModal('modalHapus')" class="modal-close">×</button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menarik laporan <strong>#<?= htmlspecialchars($kode) ?></strong>? Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-foot">
                <button class="btn btn-outline" onclick="closeModal('modalHapus')">Batal</button>
                <a href="laporan.php?success=Laporan+berhasil+ditarik" class="btn btn-danger">Ya, Tarik</a>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- ============================================================
         HALAMAN DAFTAR
         ============================================================ -->
    <div class="page-header">
        <div>
            <h2>Laporan Saya</h2>
            <p>Pantau status dan riwayat laporan yang telah kamu buat</p>
        </div>
        <a href="buat_laporan.php" class="btn btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Laporan Baru
        </a>
    </div>

    <!-- STATISTIK -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-num"><?= $total ?></div>
            <div class="stat-lbl">Total Laporan Saya</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-num"><?= $diproses ?></div>
            <div class="stat-lbl">Sedang Diproses</div>
        </div>
        <div class="stat-card green">
            <div class="stat-num"><?= $selesai ?></div>
            <div class="stat-lbl">Selesai Ditangani</div>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="tabs">
            <?php foreach (['semua' => 'Semua', 'baru' => 'Menunggu', 'diproses' => 'Diproses', 'selesai' => 'Selesai'] as $k => $v): ?>
            <a href="laporan.php?filter=<?= $k ?><?= $search ? '&search='.urlencode($search) : '' ?>"
               class="tab <?= $filter === $k ? 'active' : '' ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>
        <form method="GET" class="search-form" autocomplete="off">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
            <div class="search-box">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari laporan...">
                <?php if ($search): ?>
                <a href="laporan.php?filter=<?= $filter ?>" class="clear-btn" title="Hapus pencarian">×</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- DAFTAR -->
    <?php if (empty($laporan_list)): ?>
    <div class="empty-state">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3><?= ($search || $filter !== 'semua') ? 'Tidak ada laporan ditemukan' : 'Belum ada laporan' ?></h3>
        <p><?= ($search || $filter !== 'semua') ? 'Coba ubah filter atau kata kunci pencarian.' : 'Laporan yang kamu buat akan muncul di sini.' ?></p>
        <?php if ($search || $filter !== 'semua'): ?>
        <a href="laporan.php" class="btn btn-outline" style="margin-top:16px">Reset Filter</a>
        <?php endif; ?>
    </div>

    <?php else: ?>
    <div class="laporan-list">
    <?php foreach ($laporan_list as $l):
        $st  = getBadge($l['status_laporan'], $status_badge);
        $pg  = getProgres($l['status_laporan'], $status_progres);
        $tgl = !empty($l['waktu_kejadian']) ? date('d M Y, H:i', strtotime($l['waktu_kejadian'])) : '-';
        $tgl_laporan = date('d M Y', strtotime($l['tanggal_laporan']));
        $kode = $l['kode_laporan'] ?? 'KS-'.$l['id_laporan'];
    ?>
    <div class="laporan-card status-<?= strtolower(trim($l['status_laporan'])) ?>" onclick="window.location='laporan.php?detail=<?= $l['id_laporan'] ?>'">
        <div class="lc-head">
            <div class="lc-left">
                <span class="lc-id">#<?= htmlspecialchars($kode) ?></span>
                <span class="badge <?= $st ?>"><?= htmlspecialchars(ucfirst($l['status_laporan'])) ?></span>
                <?php if (!empty($l['jenis_kekerasan'])): ?>
                <span class="badge badge-gray"><?= htmlspecialchars($l['jenis_kekerasan']) ?></span>
                <?php endif; ?>
            </div>
            <div class="lc-right">
                <span class="lc-date"><?= $tgl_laporan ?></span>
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
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                <?= htmlspecialchars($l['jenis_pelaporan']) ?>
            </span>
            <?php endif; ?>
        </div>

        <!-- PROGRES -->
        <div class="progress-section">
            <p class="progress-label">PROGRES PENANGANAN</p>
            <div class="progress-bar">
                <?php foreach ($progres_labels as $i => $lbl): ?>
                <div class="ps <?= $i < $pg ? 'done' : ($i === $pg ? 'active' : '') ?>">
                    <div class="ps-circle">
                        <?php if ($i < $pg): ?>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5"><polyline points="20,6 9,17 4,12"/></svg>
                        <?php elseif ($i === $pg): ?>
                        <div class="ps-dot"></div>
                        <?php endif; ?>
                    </div>
                    <span class="ps-lbl"><?= $lbl ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (strtolower($l['status_laporan']) === 'selesai'): ?>
            <div style="margin-top:10px">
                <span class="selesai-chip">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg>
                    Diselesaikan
                </span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

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