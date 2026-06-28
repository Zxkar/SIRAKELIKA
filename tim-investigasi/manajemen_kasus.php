<?php
session_start();
include '../config/conn.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'investigasi') {
    header("Location: login.php");
    exit;
}
$username_aktif = $_SESSION['username'];
$id_user = (int) ($_SESSION['id_user'] ?? 0);
$inisial = '';
foreach (explode(' ', $username_aktif) as $part) { $inisial .= strtoupper(substr($part, 0, 1)); }
$inisial = substr($inisial, 0, 2) ?: 'TI';

// =============================================
//  AKSI: SIMPAN PERUBAHAN (update status + catatan + file hasil investigasi)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id_laporan  = (int) $_POST['id_laporan'];
    $status_baru = $conn->real_escape_string($_POST['status_baru']);
    $catatan     = trim($_POST['catatan'] ?? '');

    // Ambil status lama
    $res_lama = $conn->query("SELECT status_laporan FROM laporan WHERE id_laporan = $id_laporan LIMIT 1");
    $status_lama = $res_lama ? $res_lama->fetch_assoc()['status_laporan'] : '';

    // Upload file hasil investigasi (opsional)
    $file_hasil = null;
    if (!empty($_FILES['file_hasil']['name'])) {
        $upload_dir = 'uploads/hasil_investigasi/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $ext_allowed = ['pdf','doc','docx','jpg','jpeg','png'];
        $ext = strtolower(pathinfo($_FILES['file_hasil']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $ext_allowed) && $_FILES['file_hasil']['size'] <= 10 * 1024 * 1024) {
            $file_hasil = 'hasil_' . $id_laporan . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['file_hasil']['tmp_name'], $upload_dir . $file_hasil);
        }
    }

    // Update status laporan
    $conn->query("UPDATE laporan SET status_laporan = '$status_baru' WHERE id_laporan = $id_laporan");

    // Catat ke status_laporan_log
    $catatan_sql = $catatan !== '' ? "'" . $conn->real_escape_string($catatan) . "'" : 'NULL';
    @$conn->query("INSERT INTO status_laporan_log (id_laporan, status_lama, status_baru, catatan, tanggal_update) 
                    VALUES ($id_laporan, '$status_lama', '$status_baru', $catatan_sql, NOW())");

    // Catat ke tindak_lanjut
    if ($catatan !== '' || $file_hasil) {
        $cat_tl  = $conn->real_escape_string($catatan ?: 'Perubahan status oleh tim investigasi');
        $file_sql = $file_hasil ? "'" . $conn->real_escape_string($file_hasil) . "'" : 'NULL';
        $id_tim_sql = $id_user > 0 ? $id_user : 'NULL';
        @$conn->query("INSERT INTO tindak_lanjut (id_laporan, id_tim, deskripsi_tindakan, tanggal_tindakan) 
                        VALUES ($id_laporan, $id_tim_sql, '$cat_tl', NOW())");
    }

    header("Location: manajemen_kasus.php?success=Perubahan+berhasil+disimpan");
    exit;
}

// =============================================
//  AKSI: KIRIM BERKAS KE MANAJEMEN KAMPUS (Sederhana tanpa Rekomendasi)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_manajemen'])) {
    $id_laporan = (int) $_POST['id_laporan'];

    // 1. Ambil status lama
    $res_lama = $conn->query("SELECT status_laporan FROM laporan WHERE id_laporan = $id_laporan LIMIT 1");
    $status_lama = $res_lama ? $res_lama->fetch_assoc()['status_laporan'] : '';
    
    $status_baru = 'ditindaklanjuti'; 

    // 2. Update status laporan saja
    $conn->query("UPDATE laporan SET status_laporan = '$status_baru' WHERE id_laporan = $id_laporan");

    // 3. Catat perubahan status ke status_laporan_log
    $conn->query("INSERT INTO status_laporan_log (id_laporan, status_lama, status_baru, catatan, tanggal_update) 
                  VALUES ($id_laporan, '$status_lama', '$status_baru', 'Berkas kasus resmi dikirim ke Manajemen Kampus untuk penerbitan SK.', NOW())");

    // 4. Catat jejak aksi ke tabel tindak_lanjut
    $id_tim_sql = $id_user > 0 ? $id_user : 'NULL';
    $conn->query("INSERT INTO tindak_lanjut (id_laporan, id_tim, deskripsi_tindakan, tanggal_tindakan) 
                  VALUES ($id_laporan, $id_tim_sql, 'Mengirimkan berkas laporan ke pihak Manajemen Kampus.', NOW())");

    header("Location: manajemen_kasus.php?success=Berkas+berhasil+dikirim+ke+Manajemen+Kampus");
    exit;
}

// =============================================
//  STATISTIK
// =============================================
$status_badge = [
    'menunggu'        => 'status-new',
    'diproses'        => 'status-process',
    'ditindaklanjuti' => 'status-process',
    'mediasi'         => 'status-mediasi',
    'selesai'         => 'status-done',
    'ditolak'         => 'status-rejected',
];
function getBadge($status, $map) {
    return $map[strtolower(trim($status))] ?? 'status-new';
}

$total_kasus    = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE status_laporan != 'menunggu'")->fetch_assoc()['c'] ?? 0);
$perlu_tindakan = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE LOWER(status_laporan)='diproses'")->fetch_assoc()['c'] ?? 0);
$sedang_selidik = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE LOWER(status_laporan)='ditindaklanjuti'")->fetch_assoc()['c'] ?? 0);
$kasus_selesai  = (int)($conn->query("SELECT COUNT(*) c FROM laporan WHERE LOWER(status_laporan)='selesai'")->fetch_assoc()['c'] ?? 0);

// =============================================
//  FILTER & PENCARIAN
// =============================================
$filter       = $_GET['filter'] ?? 'semua';
$search       = trim($_GET['search'] ?? '');
$jenis_filter = $_GET['jenis'] ?? 'semua';

$where = "LOWER(l.status_laporan) != 'menunggu'";

if ($filter === 'baru')         $where .= " AND LOWER(l.status_laporan) = 'diproses'";
elseif ($filter === 'proses')   $where .= " AND LOWER(l.status_laporan) = 'ditindaklanjuti'";
elseif ($filter === 'mediasi')  $where .= " AND LOWER(l.status_laporan) = 'mediasi'";
elseif ($filter === 'selesai')  $where .= " AND LOWER(l.status_laporan) = 'selesai'";

if ($jenis_filter === 'umum')   $where .= " AND l.jenis_pelaporan = 'UMUM'";
elseif ($jenis_filter === 'khusus') $where .= " AND l.jenis_pelaporan = 'KHUSUS'";

if ($search !== '') {
    $s = $conn->real_escape_string($search);
    $where .= " AND (l.judul_laporan LIKE '%$s%' OR l.kode_laporan LIKE '%$s%' OR l.lokasi_kejadian LIKE '%$s%')";
}

$laporan_list = [];
$res = $conn->query("SELECT l.*, u.username AS nama_mahasiswa 
                      FROM laporan l 
                      LEFT JOIN users u ON l.id_user = u.id_user
                      WHERE $where 
                      ORDER BY l.tanggal_laporan DESC");
if ($res) while ($row = $res->fetch_assoc()) $laporan_list[] = $row;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kasus Masuk – SIRAKELIKA</title>
    <link rel="stylesheet" href="dashboard_investigasi.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .page-header-investigasi { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; }
        .page-header-investigasi h2 { font-size: 24px; font-weight: 700; color: #0f172a; }
        .page-header-investigasi p  { font-size: 13px; color: #64748b; margin-top: 4px; }
        .status-mediasi  { background: #faf5ff; color: #7c3aed; font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px; }
        .status-rejected { background-color: #f1f5f9; color: #64748b; }
        .toolbar-investigasi { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap; }
        .tabs-investigasi { display: flex; gap: 4px; background: #f1f5f9; padding: 4px; border-radius: 10px; }
        .tab-inv { padding: 7px 16px; border-radius: 7px; font-size: 13px; font-weight: 500; color: #64748b; text-decoration: none; transition: all 0.2s; }
        .tab-inv:hover { color: #1e293b; }
        .tab-inv.active { background: #fff; color: #1e293b; font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .filter-right { display: flex; gap: 8px; align-items: center; }
        .search-box-inv { display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; color: #94a3b8; min-width: 220px; }
        .search-box-inv input { border: none; outline: none; font-size: 13px; color: #1e293b; background: transparent; width: 100%; }
        .select-jenis { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; font-size: 13px; color: #475569; background: #fff; font-family: 'Inter', sans-serif; cursor: pointer; }
        .table-container-full { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
        .table-container-full .data-table { margin: 0; }
        .table-container-full .data-table th { padding: 14px 16px; background: #f8fafc; }
        .table-container-full .data-table td { padding: 14px 16px; }
        .table-container-full .data-table tr:hover td { background: #f8fafc; }
        .jenis-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 20px; }
        .jenis-pill.umum   { background: #eff6ff; color: #2563eb; }
        .jenis-pill.khusus { background: #faf5ff; color: #7c3aed; }
        .pelapor-anon { font-size: 12px; color: #94a3b8; font-style: italic; }
        .btn-kelola { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; border-radius: 7px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s; text-decoration: none; }
        .btn-kelola:hover { background: #dbeafe; border-color: #93c5fd; }
        .btn-kirim-mgmt { display: inline-flex; align-items: center; gap: 5px; padding: 6px 12px; background: #faf5ff; color: #7c3aed; border: 1px solid #ddd6fe; border-radius: 7px; font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s; text-decoration: none; }
        .btn-kirim-mgmt:hover { background: #ede9fe; border-color: #c4b5fd; }
        .btn-group { display: flex; gap: 6px; flex-wrap: wrap; }
        .alert-success-inv { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .modal-overlay-inv { display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
        .modal-overlay-inv.show { display: flex; }
        .modal-inv { background: #fff; border-radius: 14px; width: 100%; max-width: 540px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.18); }
        .modal-inv-head { display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid #f1f5f9; position: sticky; top: 0; background: #fff; z-index: 1; }
        .modal-inv-head h3 { font-size: 15px; font-weight: 700; color: #0f172a; }
        .modal-inv-close { background: #f1f5f9; border: none; width: 28px; height: 28px; border-radius: 50%; color: #64748b; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
        .modal-inv-body { padding: 20px 24px; }
        .modal-inv-foot { display: flex; gap: 8px; justify-content: flex-end; padding: 16px 24px; border-top: 1px solid #f1f5f9; }
        .detail-row { display: flex; justify-content: space-between; padding: 9px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .detail-row span:first-child { color: #64748b; }
        .detail-row strong { color: #0f172a; font-weight: 600; text-align:right; max-width: 60%; }
        .detail-desc-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; font-size: 13px; color: #334155; line-height: 1.6; margin: 12px 0; }
        .form-label-inv { display: block; font-size: 12px; font-weight: 600; color: #374151; margin: 14px 0 6px; }
        .select-status, .textarea-catatan, .input-file-inv { width: 100%; padding: 9px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; color: #1e293b; font-family: 'Inter', sans-serif; outline: none; background: #fff; }
        .textarea-catatan { resize: vertical; min-height: 80px; }
        .btn-modal { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; font-family: 'Inter', sans-serif; transition: all 0.2s; }
        .btn-modal.cancel  { background: #fff; color: #475569; border: 1px solid #e2e8f0; }
        .btn-modal.save    { background: #2563eb; color: #fff; }
        .btn-modal.save:disabled { background: #93c5fd; cursor: not-allowed; }
        .btn-modal.purple  { background: #4338ca; color: #fff; }
        .btn-modal.purple:hover { background: #3730a3; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo-area"><div class="logo-icon"></div><div><h1 class="logo-title">SIRAKELIKA</h1><p class="logo-sub">PANEL INVESTIGASI</p></div></div>
    <nav class="nav-container">
        <div class="nav-group">NAVIGASI UTAMA</div>
        <a href="dashboard_investigasi.php" class="nav-link"><span class="nav-text">Dashboard Tim</span></a>
        <div class="nav-group">PENGELOLAAN KASUS</div>
        <a href="manajemen_kasus.php" class="nav-link active"><span class="nav-text">Manajemen Kasus Masuk</span></a>
        <a href="log_aktivitas.php" class="nav-link"><span class="nav-text">Log Aktivitas Kasus</span></a>
        <div class="nav-group">AKUN SYSTEM</div>
        <a href="../auth/logout.php" class="nav-link logout" onclick="return confirm('Yakin ingin keluar?')"><span class="nav-text">Keluar</span></a>
    </nav>
</aside>

<main class="main-content">
    <header class="topbar"><div></div><div class="user-profile"><div class="avatar"><?= htmlspecialchars($inisial) ?></div><div class="user-info"><span class="user-name"><?= htmlspecialchars($username_aktif) ?></span><span class="user-role">Tim Investigasi</span></div></div></header>

    <?php if (!empty($_GET['success'])): ?>
    <div class="alert-success-inv"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20,6 9,17 4,12"/></svg><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <div class="page-header-investigasi">
        <div><h2>Manajemen Kasus Masuk</h2><p>Tinjau dan kelola laporan yang telah diverifikasi admin</p></div>
    </div>

    <section class="stats-grid">
        <div class="card card-total"><span class="card-num"><?= $total_kasus ?></span><span class="card-title">Total Kasus Diterima</span></div>
        <div class="card card-new"><span class="card-num"><?= $perlu_tindakan ?></span><span class="card-title">Perlu Tindakan</span></div>
        <div class="card card-process"><span class="card-num"><?= $sedang_selidik ?></span><span class="card-title">Sedang Diselidiki</span></div>
        <div class="card card-done"><span class="card-num"><?= $kasus_selesai ?></span><span class="card-title">Kasus Selesai</span></div>
    </section>

    <div class="toolbar-investigasi">
        <div class="tabs-investigasi">
            <?php foreach (['semua'=>'Semua','baru'=>'Diproses','proses'=>'Ditindaklanjuti','mediasi'=>'Mediasi','selesai'=>'Selesai'] as $k => $v): ?>
            <a href="manajemen_kasus.php?filter=<?= $k ?>&jenis=<?= $jenis_filter ?><?= $search ? '&search='.urlencode($search) : '' ?>" class="tab-inv <?= $filter === $k ? 'active' : '' ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="table-container-full">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID KASUS</th>
                    <th>JUDUL & JENIS</th>
                    <th>PELAPOR</th>
                    <th>LOKASI</th>
                    <th>TANGGAL MASUK</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($laporan_list)): ?>
                <tr><td colspan="7"><div class="empty-state"><p>Tidak ada laporan yang sesuai filter ini.</p></div></td></tr>
                <?php else: foreach ($laporan_list as $l):
                    $badge   = getBadge($l['status_laporan'], $status_badge);
                    $kode    = $l['kode_laporan'] ?? 'KS-'.$l['id_laporan'];
                    $tgl     = date('d M Y', strtotime($l['tanggal_laporan']));
                    $isUmum  = strtoupper($l['jenis_pelaporan']) === 'UMUM';
                    $status  = strtolower($l['status_laporan']);
                    
                    $canKirim = in_array($status, ['diproses']);
                    $canKelola = !in_array($status, ['ditindaklanjuti', 'selesai']);
                    $hasSK     = ($status === 'selesai' && !empty($l['file_sk']));

                    $detailJson = htmlspecialchars(json_encode([
                        'id' => $l['id_laporan'], 'kode' => $kode, 'judul' => $l['judul_laporan'], 'deskripsi' => $l['deskripsi'], 'jenis_kekerasan' => ucfirst($l['jenis_kekerasan']), 'jenis_pelaporan' => $l['jenis_pelaporan'], 'lokasi' => $l['lokasi_kejadian'], 'waktu' => date('d M Y, H:i', strtotime($l['waktu_kejadian'])), 'tanggal' => $tgl, 'status' => $l['status_laporan'], 'pelapor' => $isUmum ? ($l['nama_mahasiswa'] ?? '-') : 'Dirahasiakan',
                    ]), ENT_QUOTES);
                ?>
                <tr>
                    <td class="id-case">#<?= htmlspecialchars($kode) ?></td>
                    <td>
                        <strong style="display:block;color:#1e293b;font-size:13px;margin-bottom:3px"><?= htmlspecialchars($l['judul_laporan']) ?></strong>
                        <span class="jenis-pill <?= $isUmum ? 'umum' : 'khusus' ?>"><?= $isUmum ? 'Umum' : 'Khusus' ?></span>
                    </td>
                    <td><?= $isUmum ? htmlspecialchars($l['nama_mahasiswa'] ?? '-') : '<span class="pelapor-anon">🔒 Dirahasiakan</span>' ?></td>
                    <td><?= htmlspecialchars($l['lokasi_kejadian']) ?></td>
                    <td><?= $tgl ?></td>
                    <td><span class="status-badge <?= $badge ?>"><?= htmlspecialchars(ucfirst($l['status_laporan'])) ?></span></td>
                    <td>
                        <div class="btn-group">
                            <?php if ($canKelola): ?>
                            <button class="btn-kelola" onclick='bukaModal(<?= $detailJson ?>)'>Kelola Kasus</button>
                            <?php endif; ?>

                            <?php if ($canKirim): ?>
                            <button class="btn-kirim-mgmt" onclick="bukaModalKirim(<?= $l['id_laporan'] ?>, '<?= htmlspecialchars($kode, ENT_QUOTES) ?>')">Kirim ke Manajemen</button>
                            <?php endif; ?>

                            <?php if ($hasSK): ?>
                            <a href="uploads/sk_sanksi/<?= htmlspecialchars($l['file_sk']) ?>" class="btn-kirim-mgmt" style="background:#f0fdf4; color:#16a34a; border-color:#bbf7d0; text-decoration: none;" download>📥 Download SK Sanksi</a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- MODAL KELOLA KASUS -->
<div class="modal-overlay-inv" id="modalKelola" onclick="if(event.target===this) tutupModal('modalKelola')">
    <div class="modal-inv">
        <form method="POST" enctype="multipart/form-data">
            <div class="modal-inv-head"><h3>Kelola Kasus <span id="mKode" style="color:#2563eb"></span></h3><button type="button" class="modal-inv-close" onclick="tutupModal('modalKelola')">×</button></div>
            <div class="modal-inv-body">
                <div class="detail-row"><span>Judul</span><strong id="mJudul"></strong></div>
                <div class="detail-row"><span>Jenis Kekerasan</span><strong id="mJenisKekerasan"></strong></div>
                <div class="detail-desc-box" id="mDeskripsi"></div>
                <hr class="section-divider">
                <label class="form-label-inv">Ubah Status Laporan</label>
                <select name="status_baru" id="mStatusSelect" class="select-status" required onchange="cekPerubahan()">
                    <option value="diproses">Diproses</option>
                    <option value="ditindaklanjuti">Ditindaklanjuti</option>
                    <option value="mediasi">Mediasi</option>
                    <option value="selesai">Selesai</option>
                </select>
                <label class="form-label-inv">Catatan Tindak Lanjut</label>
                <textarea name="catatan" id="mCatatan" class="textarea-catatan" oninput="cekPerubahan()"></textarea>
                <input type="hidden" name="id_laporan" id="mIdLaporan"><input type="hidden" name="update_status" value="1"><input type="hidden" id="mStatusAwal">
            </div>
            <div class="modal-inv-foot"><button type="button" class="btn-modal cancel" onclick="tutupModal('modalKelola')">Batal</button><button type="submit" class="btn-modal save" id="btnSimpan" disabled>Simpan Perubahan</button></div>
        </form>
    </div>
</div>

<!-- MODAL KIRIM KE MANAJEMEN -->
<div class="modal-overlay-inv" id="modalKirim" onclick="if(event.target===this) tutupModalKirim()">
    <div class="modal-inv">
        <form method="POST">
            <div class="modal-inv-head"><h3>Kirim Berkas Kasus <span id="kKode"></span></h3><button type="button" class="modal-inv-close" onclick="tutupModalKirim()">×</button></div>
            <div class="modal-inv-body">
                <p style="font-size: 13px; color: #334155; line-height:1.5;">Apakah Anda yakin ingin menyerahkan berkas perkara ini langsung ke <strong>Manajemen Kampus (Rektorat)</strong> agar dapat diproses pembuatan Surat Keputusan (SK) Sanksi Final?</p>
                <input type="hidden" name="id_laporan" id="kIdLaporan"><input type="hidden" name="kirim_manajemen" value="1">
            </div>
            <div class="modal-inv-foot"><button type="button" class="btn-modal cancel" onclick="tutupModalKirim()">Batal</button><button type="submit" class="btn-modal purple">🚀 Ya, Kirim Sekarang</button></div>
        </form>
    </div>
</div>

<script>
let statusAwal = '';
function bukaModal(data) {
    document.getElementById('mKode').textContent= '#' + data.kode; document.getElementById('mJudul').textContent= data.judul; document.getElementById('mJenisKekerasan').textContent = data.jenis_kekerasan; document.getElementById('mDeskripsi').textContent= data.deskripsi; document.getElementById('mIdLaporan').value= data.id;
    const statusVal = data.status.toLowerCase(); document.getElementById('mStatusSelect').value = statusVal; document.getElementById('mStatusAwal').value= statusVal; document.getElementById('mCatatan').value= ''; statusAwal = statusVal;
    document.getElementById('btnSimpan').disabled = true; document.getElementById('modalKelola').classList.add('show');
}
function cekPerubahan() {
    const statusSekarang = document.getElementById('mStatusSelect').value; const catatan = document.getElementById('mCatatan').value.trim();
    document.getElementById('btnSimpan').disabled = !(statusSekarang !== statusAwal || catatan !== '');
}
function bukaModalKirim(id, kode) { document.getElementById('kKode').textContent = '#' + kode; document.getElementById('kIdLaporan').value = id; document.getElementById('modalKirim').classList.add('show'); }
function tutupModalKirim() { document.getElementById('modalKirim').remove('show'); }
function tutupModal(id) { document.getElementById(id).classList.remove('show'); }
</script>
</body>
</html>

