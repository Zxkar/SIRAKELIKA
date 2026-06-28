<?php
session_start();
include '../config/conn.php';

if(!isset($_SESSION['admin_logged_in']) || !in_array($_SESSION['role'], ['admin', 'superadmin'])){
    header("Location: login_admin.php");
    exit;
}

$valid_status = ['menunggu','diproses','ditindaklanjuti','selesai','ditolak'];

if(isset($_POST['update_status'])){
    $id      = (int)$_POST['id_laporan'];
    $status  = $_POST['status_baru'];
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

    if(!in_array($status, $valid_status)){
        header("Location: verifikasi_laporan.php?error=status_invalid");
        exit;
    }

    $lap_lama = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status_laporan, judul_laporan, kode_laporan, id_user FROM laporan WHERE id_laporan=$id"));
    if(!$lap_lama){
        header("Location: verifikasi_laporan.php?error=laporan_invalid");
        exit;
    }

    $status_lama = $lap_lama['status_laporan'];
    $judul       = mysqli_real_escape_string($conn, $lap_lama['judul_laporan']);
    $kode        = $lap_lama['kode_laporan'] ?? 'KS-'.$id;
    $status_esc  = mysqli_real_escape_string($conn, $status);

    mysqli_query($conn, "UPDATE laporan SET status_laporan='$status_esc' WHERE id_laporan=$id");

    $admin_id     = (isset($_SESSION['admin_id']) && (int)$_SESSION['admin_id'] > 0) ? (int)$_SESSION['admin_id'] : null;
    $admin_id_sql = $admin_id !== null ? $admin_id : 'NULL';
    mysqli_query($conn, "INSERT INTO status_laporan_log (id_laporan, id_user_petugas, status_lama, status_baru, catatan)
                         VALUES ($id, $admin_id_sql, '$status_lama', '$status_esc', '$catatan')");

    if($status === 'ditindaklanjuti'){
        $tim = mysqli_query($conn, "SELECT id_user FROM users WHERE role='investigasi' AND status_akun='aktif'");
        while($t = mysqli_fetch_assoc($tim)){
            mysqli_query($conn, "INSERT INTO notifikasi (id_user, id_laporan, judul, pesan)
                VALUES (
                    {$t['id_user']},
                    $id,
                    'Laporan Baru Perlu Ditindaklanjuti',
                    'Laporan [$kode] \"$judul\" telah diteruskan oleh admin dan menunggu tindak lanjut tim investigasi.'
                )");
        }
    }

    if(!empty($lap_lama['id_user'])){
        $pesan_map = [
            'diproses'         => 'Laporan kamu sedang dalam proses review oleh admin.',
            'ditindaklanjuti'  => 'Laporan kamu sudah diteruskan ke Tim Investigasi Kampus.',
            'selesai'          => 'Laporan kamu telah selesai ditangani.',
            'ditolak'          => 'Laporan kamu tidak dapat diproses. Silakan hubungi admin untuk informasi lebih lanjut.',
            'menunggu'         => 'Status laporan kamu dikembalikan ke menunggu verifikasi.',
        ];
        $pesan_notif = mysqli_real_escape_string($conn, $pesan_map[$status] ?? 'Status laporan kamu telah diperbarui.');
        mysqli_query($conn, "INSERT INTO notifikasi (id_user, id_laporan, judul, pesan)
            VALUES (
                {$lap_lama['id_user']},
                $id,
                'Update Status Laporan [$kode]',
                '$pesan_notif'
            )");
    }

    header("Location: verifikasi_laporan.php?success=1");
    exit;
}

$filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : 'menunggu';
if(!in_array($filter, $valid_status)) $filter = 'menunggu';

$query = mysqli_query($conn, "SELECT l.*, u.username, l.bukti_file FROM laporan l
                               LEFT JOIN users u ON l.id_user = u.id_user
                               WHERE l.status_laporan = '$filter'
                               ORDER BY l.tanggal_laporan DESC");
$total = mysqli_num_rows($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Laporan - SIRAKELIKA</title>
    <link rel="stylesheet" href="dashboard_admin.css">
    <link rel="stylesheet" href="verifikasi_laporan.css">
    <style>
        .btn-detail { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; padding:6px 14px; border-radius:6px; font-size:11px; font-weight:600; cursor:pointer; transition:all 0.2s; }
        .btn-detail:hover { background:#dbeafe; }
        .modal-detail { background:#fff; border-radius:16px; width:560px; max-width:95vw; max-height:88vh; overflow-y:auto; }
        .modal-detail-head { padding:20px 24px 16px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; background:#fff; z-index:1; border-radius:16px 16px 0 0; }
        .modal-detail-head h3 { font-size:16px; font-weight:700; color:#0f172a; }
        .modal-detail-body { padding:20px 24px 24px; }
        .detail-section-title { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px; }
        .detail-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f8fafc; font-size:13px; gap:12px; }
        .detail-row span:first-child { color:#64748b; flex-shrink:0; }
        .detail-row strong { color:#0f172a; font-weight:600; text-align:right; }
        .detail-deskripsi { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px 14px; font-size:13px; color:#334155; line-height:1.6; white-space:pre-wrap; margin-top:8px; }
        .bukti-img { max-width:100%; border-radius:8px; border:1px solid #e2e8f0; margin-top:8px; display:block; }
        .bukti-link { display:inline-flex; align-items:center; gap:6px; padding:8px 14px; background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a; border-radius:8px; font-size:13px; font-weight:600; text-decoration:none; margin-top:8px; }
        .bukti-link:hover { background:#dcfce7; }
        .no-bukti { font-size:13px; color:#94a3b8; font-style:italic; margin-top:6px; }
        .modal-close-btn { background:#f1f5f9; border:none; width:28px; height:28px; border-radius:50%; color:#64748b; font-size:16px; cursor:pointer; display:flex; align-items:center; justify-content:center; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo-area">
        <div class="logo-icon"></div>
        <div>
            <h1 class="logo-title">SIRAKELIKA</h1>
            <p class="logo-sub">ADMINISTRATOR</p>
        </div>
    </div>
    <nav class="nav-container">
        <div class="nav-group">SYSTEM CONTROL</div>
        <a href="dashboard_admin.php" class="nav-link">
            <span class="nav-text">Dashboard</span>
        </a>
        <div class="nav-group">MANAJEMEN</div>
        <a href="verifikasi_laporan.php" class="nav-link active">
            <span class="nav-text">Verifikasi Laporan Masuk</span>
        </a>
        <a href="kelola_mahasiswa.php" class="nav-link">
            <span class="nav-text">Kelola Akun Mahasiswa</span>
        </a>
        <a href="kelola_internal.php" class="nav-link">
            <span class="nav-text">Kelola Akun Pihak Internal</span>
        </a>
        <div class="nav-group">AKUN UTAMA</div>
        <a href="logout.php" class="nav-link logout">
            <span class="nav-text">Keluar</span>
        </a>
    </nav>
</aside>

<main class="main-content">
    <header class="topbar">
        <div></div>
        <div class="user-profile">
            <div class="avatar"><?php echo strtoupper(substr($_SESSION['admin_name'], 0, 2)); ?></div>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <span class="user-role">Sistem Administrator</span>
            </div>
        </div>
    </header>

    <div class="content-title">
        <h2>Verifikasi Laporan Masuk</h2>
        <p>Kelola dan ubah status laporan kekerasan kampus</p>
    </div>

    <?php if(isset($_GET['success'])): ?>
    <div class="alert-success">✓ Status laporan berhasil diperbarui.</div>
    <?php endif; ?>
    <?php if(isset($_GET['error']) && $_GET['error']==='status_invalid'): ?>
    <div class="alert-error">⚠ Status yang dikirim tidak valid.</div>
    <?php endif; ?>
    <?php if(isset($_GET['error']) && $_GET['error']==='laporan_invalid'): ?>
    <div class="alert-error">⚠ Laporan tidak ditemukan.</div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <?php foreach($valid_status as $s): ?>
        <a href="?filter=<?= $s ?>" class="filter-btn <?= $filter===$s ? 'active' : '' ?>">
            <?= ucfirst($s) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <div class="table-container">
        <div class="table-header">
            <div>
                <h3>Laporan — <?= ucfirst($filter) ?></h3>
                <p><?= $total ?> laporan ditemukan</p>
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>KODE</th>
                    <th>JUDUL LAPORAN</th>
                    <th>PELAPOR</th>
                    <th>JENIS KEKERASAN</th>
                    <th>TANGGAL</th>
                    <th>STATUS</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
            <?php if($total > 0): while($row = mysqli_fetch_assoc($query)): 
                $kode = htmlspecialchars($row['kode_laporan'] ?? '#KS-'.$row['id_laporan']);
                $pelapor = $row['id_user'] ? htmlspecialchars($row['username']) : '<em style="color:#94a3b8">Anonim</em>';
            ?>
            <tr>
                <td class="id-case"><?= $kode ?></td>
                <td><strong><?= htmlspecialchars($row['judul_laporan']) ?></strong></td>
                <td><?= $pelapor ?></td>
                <td><?= htmlspecialchars($row['jenis_kekerasan']) ?></td>
                <td><?= date('d M Y', strtotime($row['tanggal_laporan'])) ?></td>
                <td><span class="badge-status s-<?= $row['status_laporan'] ?>"><?= ucfirst($row['status_laporan']) ?></span></td>
                <td>
                    <button class="btn-detail" onclick="openDetail(<?= htmlspecialchars(json_encode([
                        'kode'            => $kode,
                        'judul'           => $row['judul_laporan'],
                        'pelapor'         => $row['username'] ?? 'Anonim',
                        'jenis_pelaporan' => $row['jenis_pelaporan'] ?? '-',
                        'jenis_kekerasan' => $row['jenis_kekerasan'],
                        'waktu_kejadian'  => $row['waktu_kejadian'] ?? '-',
                        'lokasi'          => $row['lokasi_kejadian'],
                        'deskripsi'       => $row['deskripsi'],
                        'bukti'           => $row['bukti_file'] ?? '',
                        'status'          => $row['status_laporan'],
                        'tanggal'         => date('d M Y', strtotime($row['tanggal_laporan'])),
                    ]), ENT_QUOTES) ?>)">Lihat Detail</button>
                    <button class="btn-verif" onclick="openModal(
                        <?= $row['id_laporan'] ?>,
                        '<?= addslashes($kode) ?>',
                        '<?= $row['status_laporan'] ?>'
                    )">Update Status</button>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">Tidak ada laporan dengan status ini.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Modal Update Status -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <h3>Update Status Laporan</h3>
        <p class="sub" id="modalKode"></p>
        <form method="POST">
            <input type="hidden" name="update_status" value="1">
            <input type="hidden" name="id_laporan" id="inputId">
            <div class="form-group">
                <label>Status Baru</label>
                <select name="status_baru" id="selectStatus">
                    <option value="menunggu">Menunggu</option>
                    <option value="diproses">Diproses</option>
                    <option value="ditindaklanjuti">Ditindaklanjuti</option>
                    <option value="selesai">Selesai</option>
                    <option value="ditolak">Ditolak</option>
                </select>
            </div>
            <div class="form-group">
                <label>Catatan (opsional)</label>
                <textarea name="catatan" placeholder="Tambahkan catatan tindakan..."></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Batal</button>
                <button type="submit" class="btn-submit">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Detail Laporan -->
<div class="modal-overlay" id="modalDetail">
    <div class="modal-detail">
        <div class="modal-detail-head">
            <h3>Detail Laporan — <span id="d_kode" style="color:#dc2626"></span></h3>
            <button class="modal-close-btn" onclick="closeDetail()">×</button>
        </div>
        <div class="modal-detail-body">
            <div class="detail-section-title">Informasi Laporan</div>
            <div class="detail-row"><span>Pelapor</span><strong id="d_pelapor"></strong></div>
            <div class="detail-row"><span>Jenis Pelaporan</span><strong id="d_jenis_pelaporan"></strong></div>
            <div class="detail-row"><span>Jenis Kekerasan</span><strong id="d_jenis_kekerasan"></strong></div>
            <div class="detail-row"><span>Waktu Kejadian</span><strong id="d_waktu"></strong></div>
            <div class="detail-row"><span>Lokasi Kejadian</span><strong id="d_lokasi"></strong></div>
            <div class="detail-row"><span>Tanggal Laporan</span><strong id="d_tanggal"></strong></div>
            <div class="detail-row"><span>Status</span><strong id="d_status"></strong></div>

            <div class="detail-section-title" style="margin-top:16px;">Deskripsi Kejadian</div>
            <div class="detail-deskripsi" id="d_deskripsi"></div>

            <div class="detail-section-title" style="margin-top:16px;">Bukti Lampiran</div>
            <div id="d_bukti_wrap"></div>
        </div>
    </div>
</div>

<script>
function openDetail(data) {
    document.getElementById('d_kode').textContent            = data.kode;
    document.getElementById('d_pelapor').textContent         = data.pelapor || 'Anonim';
    document.getElementById('d_jenis_pelaporan').textContent = data.jenis_pelaporan || '-';
    document.getElementById('d_jenis_kekerasan').textContent = data.jenis_kekerasan || '-';
    document.getElementById('d_waktu').textContent           = data.waktu_kejadian || '-';
    document.getElementById('d_lokasi').textContent          = data.lokasi || '-';
    document.getElementById('d_tanggal').textContent         = data.tanggal || '-';
    document.getElementById('d_status').textContent          = data.status || '-';
    document.getElementById('d_deskripsi').textContent       = data.deskripsi || '-';

    const wrap = document.getElementById('d_bukti_wrap');
    if (data.bukti) {
        const ext = data.bukti.split('.').pop().toLowerCase();
        const url = '../mahasiswa/uploads/bukti/' + data.bukti;
        if (['jpg','jpeg','png'].includes(ext)) {
            wrap.innerHTML = '<img src="' + url + '" class="bukti-img">';
        } else {
            wrap.innerHTML = '<a href="' + url + '" target="_blank" class="bukti-link">📎 Lihat / Download Bukti (' + ext.toUpperCase() + ')</a>';
        }
    } else {
        wrap.innerHTML = '<p class="no-bukti">Tidak ada bukti yang dilampirkan.</p>';
    }

    document.getElementById('modalDetail').classList.add('show');
}
function closeDetail() {
    document.getElementById('modalDetail').classList.remove('show');
}
document.getElementById('modalDetail').addEventListener('click', function(e){
    if(e.target === this) closeDetail();
});

function openModal(id, kode, statusSaat){
    document.getElementById('inputId').value = id;
    document.getElementById('modalKode').textContent = 'Kode: ' + kode;
    document.getElementById('selectStatus').value = statusSaat;
    document.getElementById('modalOverlay').classList.add('show');
}
function closeModal(){
    document.getElementById('modalOverlay').classList.remove('show');
}
document.getElementById('modalOverlay').addEventListener('click', function(e){
    if(e.target === this) closeModal();
});
</script>
</body>
</html>
