<?php
session_start();
include 'conn.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin, superadmin'){
    header("Location: login_admin.php");
    exit;
}

if(isset($_POST['update_status'])){
    $id      = (int)$_POST['id_laporan'];
    $status  = mysqli_real_escape_string($conn, $_POST['status_baru']);
    $catatan = mysqli_real_escape_string($conn, $_POST['catatan']);

    $lap_lama    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status_laporan, judul_laporan, kode_laporan FROM laporan WHERE id_laporan=$id"));
    $status_lama = $lap_lama['status_laporan'];
    $judul       = mysqli_real_escape_string($conn, $lap_lama['judul_laporan']);
    $kode        = $lap_lama['kode_laporan'] ?? 'KS-'.$id;

    mysqli_query($conn, "UPDATE laporan SET status_laporan='$status' WHERE id_laporan=$id");

    $admin_id = $_SESSION['admin_id'] ?? 0;
    mysqli_query($conn, "INSERT INTO status_laporan_log (id_laporan, id_user_petugas, status_lama, status_baru, catatan)
                         VALUES ($id, $admin_id, '$status_lama', '$status', '$catatan')");

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

    $pelapor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id_user FROM laporan WHERE id_laporan=$id"));
    if($pelapor['id_user']){
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
                {$pelapor['id_user']},
                $id,
                'Update Status Laporan [$kode]',
                '$pesan_notif'
            )");
    }

    header("Location: verifikasi_laporan.php?success=1");
    exit;
}

$filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : 'menunggu';
$valid_status = ['menunggu','diproses','ditindaklanjuti','selesai','ditolak'];
if(!in_array($filter, $valid_status)) $filter = 'menunggu';

$query = mysqli_query($conn, "SELECT l.*, u.username FROM laporan l
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
    <style>
        .filter-bar { display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap; }
        .filter-btn { padding:7px 16px; border-radius:20px; border:1px solid #e2e8f0; background:#fff;
                      font-size:12px; font-weight:600; cursor:pointer; color:#64748b; text-decoration:none; transition:all 0.2s; }
        .filter-btn:hover, .filter-btn.active { background:#dc2626; color:#fff; border-color:#dc2626; }
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:100; align-items:center; justify-content:center; }
        .modal-overlay.show { display:flex; }
        .modal { background:#fff; border-radius:16px; padding:32px; width:460px; max-width:95vw; }
        .modal h3 { font-size:18px; font-weight:700; margin-bottom:4px; }
        .modal p.sub { font-size:13px; color:#64748b; margin-bottom:20px; }
        .form-group { margin-bottom:16px; }
        .form-group label { font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px; }
        .form-group select, .form-group textarea {
            width:100%; padding:10px 12px; border:1px solid #e2e8f0; border-radius:8px;
            font-size:13px; color:#1e293b; font-family:inherit; outline:none; }
        .form-group select:focus, .form-group textarea:focus { border-color:#dc2626; }
        .form-group textarea { resize:vertical; min-height:80px; }
        .modal-actions { display:flex; gap:10px; justify-content:flex-end; margin-top:20px; }
        .btn-cancel { padding:9px 20px; border-radius:8px; border:1px solid #e2e8f0; background:#fff;
                      font-size:13px; font-weight:600; cursor:pointer; color:#64748b; }
        .btn-submit { padding:9px 20px; border-radius:8px; border:none; background:#dc2626;
                      font-size:13px; font-weight:600; cursor:pointer; color:#fff; }
        .btn-submit:hover { background:#b91c1c; }
        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a;
                         padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:13px; font-weight:600; }
        .badge-status { font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px; display:inline-block; }
        .s-menunggu       { background:#fef9c3; color:#ca8a04; }
        .s-diproses       { background:#eff6ff; color:#2563eb; }
        .s-ditindaklanjuti{ background:#fdf4ff; color:#9333ea; }
        .s-selesai        { background:#f0fdf4; color:#16a34a; }
        .s-ditolak        { background:#fef2f2; color:#dc2626; }
        .btn-view-all { background:#f0f5ff; color:#2563eb; border:none; padding:6px 12px;
                        border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; }
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
            <div class="avatar">ADM</div>
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

<script>
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