<?php
session_start();
include 'conn.php';

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

// Menambahkan l.bukti_nama ke query select agar data file bukti ikut terbaca
$query = mysqli_query($conn, "SELECT l.*, u.username FROM laporan l
                               LEFT JOIN users u ON l.id_user = u.id_user
                               WHERE l.status_laporan = '$filter'
                               ORDER BY l.tanggal_laporan DESC");
$laporan_list = mysqli_fetch_all($query, MYSQLI_ASSOC);
$total = count($laporan_list);

// MENYESUAIKAN PATH BERKAS: Mengarah ke folder uploads/bukti/ milik halaman buat laporan
define('BUKTI_BASE_PATH', '../laporan/uploads/bukti/');
define('BUKTI_SERVER_PATH', dirname(__DIR__) . '/laporan/uploads/bukti/');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Laporan - SIRAKELIKA</title>
    <link rel="stylesheet" href="dashboard_admin.css">
    <link rel="stylesheet" href="verifikasi_laporan.css">
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
        <a href="dashboard_admin.php" class="nav-link"><span class="nav-text">Dashboard</span></a>
        <div class="nav-group">MANAJEMEN</div>
        <a href="verifikasi_laporan.php" class="nav-link active"><span class="nav-text">Verifikasi Laporan Masuk</span></a>
        <a href="kelola_mahasiswa.php" class="nav-link"><span class="nav-text">Kelola Akun Mahasiswa</span></a>
        <a href="kelola_internal.php" class="nav-link"><span class="nav-text">Kelola Akun Pihak Internal</span></a>
        <div class="nav-group">AKUN UTAMA</div>
        <a href="logout.php" class="nav-link logout"><span class="nav-text">Keluar</span></a>
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

    <div class="filter-bar">
        <?php foreach($valid_status as $s): ?>
        <a href="?filter=<?= $s ?>" class="filter-btn <?= $filter===$s ? 'active' : '' ?>"><?= ucfirst($s) ?></a>
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
            <?php if($total > 0): foreach($laporan_list as $row): 
                $id_lap  = $row['id_laporan'];
                $kode    = htmlspecialchars($row['kode_laporan'] ?? '#KS-'.$id_lap);
                $pelapor = $row['id_user'] ? htmlspecialchars($row['username']) : '<em style="color:#94a3b8">Anonim</em>';
                $waktu_kejadian_fmt = (!empty($row['waktu_kejadian']) && $row['waktu_kejadian'] !== '0000-00-00 00:00:00')
                    ? date('d M Y, H:i', strtotime($row['waktu_kejadian'])) : '-';
            ?>
            <tr>
                <td class="id-case"><?= $kode ?></td>
                <td><strong><?= htmlspecialchars($row['judul_laporan'] ?: '(tanpa judul)') ?></strong></td>
                <td><?= $pelapor ?></td>
                <td><?= htmlspecialchars($row['jenis_kekerasan'] ?: '-') ?></td>
                <td><?= date('d M Y', strtotime($row['tanggal_laporan'])) ?></td>
                <td><span class="badge-status s-<?= $row['status_laporan'] ?>"><?= ucfirst($row['status_laporan']) ?></span></td>
                <td>
                    <button type="button" class="btn-verif" id="btnToggle<?= $id_lap ?>" onclick="toggleDetail(<?= $id_lap ?>)">Lihat & Verifikasi</button>
                </td>
            </tr>
            <tr class="detail-row" id="detailRow<?= $id_lap ?>">
                <td colspan="7">
                    <div class="detail-panel">

                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Jenis Laporan</span>
                                <span class="detail-value">
                                    <?php if(($row['jenis_pelaporan'] ?? '') === 'UMUM'): ?>
                                        <span class="badge-pelaporan badge-umum">Umum (Anonim)</span>
                                    <?php else: ?>
                                        <span class="badge-pelaporan badge-khusus">Khusus</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Jenis Kekerasan</span>
                                <span class="detail-value"><?= htmlspecialchars(ucfirst($row['jenis_kekerasan'] ?: '-')) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Waktu Kejadian</span>
                                <span class="detail-value"><?= $waktu_kejadian_fmt ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Lokasi Kejadian</span>
                                <span class="detail-value"><?= htmlspecialchars($row['lokasi_kejadian'] ?: '-') ?></span>
                            </div>
                            <?php if(($row['jenis_pelaporan'] ?? '') === 'KHUSUS' && (!empty($row['nama_pelapor']) || !empty($row['nim_pelapor']))): ?>
                            <div class="detail-item detail-span2">
                                <span class="detail-label">Identitas Pelapor</span>
                                <span class="detail-value"><?= htmlspecialchars($row['nama_pelapor'] ?: '-') ?> — NIM <?= htmlspecialchars($row['nim_pelapor'] ?: '-') ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item detail-span2">
                                <span class="detail-label">Akun Pengirim</span>
                                <span class="detail-value"><?= $row['id_user'] ? htmlspecialchars($row['username']) : 'Anonim (tanpa akun tercatat)' ?></span>
                            </div>
                        </div>

                        <div class="detail-block">
                            <span class="detail-label">Judul Laporan</span>
                            <p class="detail-text"><?= htmlspecialchars($row['judul_laporan'] ?: '-') ?></p>
                        </div>

                        <div class="detail-block">
                            <span class="detail-label">Kronologi / Deskripsi Kejadian</span>
                            <p class="detail-text"><?= nl2br(htmlspecialchars($row['deskripsi'] ?: '-')) ?></p>
                        </div>

                        <!-- MODIFIKASI BLOK BUKTI: Membaca langsung bukti_nama dari tabel laporan -->
                        <div class="detail-block">
                            <span class="detail-label">Bukti Pendukung</span>
                            <?php 
                            $nama_file_bukti = $row['bukti_nama'] ?? ''; 
                            if(empty($nama_file_bukti)): 
                            ?>
                                <p class="no-bukti">Tidak ada bukti yang diupload pelapor.</p>
                            <?php 
                            else: 
                                $url      = BUKTI_BASE_PATH . $nama_file_bukti;
                                $srv_path = BUKTI_SERVER_PATH . $nama_file_bukti;
                                $ext      = strtolower(pathinfo($nama_file_bukti, PATHINFO_EXTENSION));
                                
                                if (file_exists($srv_path)):
                            ?>
                                <div class="bukti-grid">
                                    <div class="bukti-item">
                                    <?php if(in_array($ext, ['jpg','jpeg','png'])): ?>
                                        <a href="<?= htmlspecialchars($url) ?>" target="_blank"><img src="<?= htmlspecialchars($url) ?>" class="bukti-img" alt="Bukti laporan"></a>
                                    <?php elseif(in_array($ext, ['mp4','mov','avi'])): ?>
                                        <video src="<?= htmlspecialchars($url) ?>" controls class="bukti-video"></video>
                                    <?php elseif($ext === 'pdf'): ?>
                                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="bukti-file-link">📄 <?= htmlspecialchars($nama_file_bukti) ?></a>
                                    <?php else: ?>
                                        <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="bukti-file-link">📎 <?= htmlspecialchars($nama_file_bukti) ?></a>
                                    <?php endif; ?>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <p class="no-bukti" style="color:#ef4444;">⚠ Berkas bukti tercatat (<?= htmlspecialchars($nama_file_bukti) ?>) tetapi file fisik tidak ditemukan di folder server.</p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>

                        <hr class="modal-divider">

                        <form method="POST" class="status-form">
                            <input type="hidden" name="update_status" value="1">
                            <input type="hidden" name="id_laporan" value="<?= $id_lap ?>">
                            <div class="form-row-inline">
                                <div class="form-group">
                                    <label>Status Baru</label>
                                    <select name="status_baru">
                                        <?php foreach($valid_status as $s): ?>
                                        <option value="<?= $s ?>" <?= $row['status_laporan']===$s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group" style="flex:1;">
                                    <label>Catatan (opsional)</label>
                                    <textarea name="catatan" placeholder="Tambahkan catatan tindakan..."></textarea>
                                </div>
                            </div>
                            <div class="modal-actions">
                                <button type="button" class="btn-cancel" onclick="toggleDetail(<?= $id_lap ?>)">Tutup</button>
                                <button type="submit" class="btn-submit">Simpan Status</button>
                            </div>
                        </form>

                    </div>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7" style="text-align:center;padding:30px;color:#94a3b8;">Tidak ada laporan dengan status ini.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
function toggleDetail(id){
    const row = document.getElementById('detailRow' + id);
    const btn = document.getElementById('btnToggle' + id);
    const isOpen = row.classList.contains('show');

    document.querySelectorAll('.detail-row.show').forEach(function(r){
        r.classList.remove('show');
        const otherBtn = document.getElementById('btnToggle' + r.id.replace('detailRow',''));
        if(otherBtn) otherBtn.textContent = 'Lihat & Verifikasi';
    });

    if(!isOpen){
        row.classList.add('show');
        btn.textContent = 'Tutup Detail';
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
</body>
</html>
