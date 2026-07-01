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
                VALUES ({$t['id_user']}, $id, 'Laporan Baru Perlu Ditindaklanjuti', 'Laporan [$kode] \"$judul\" diteruskan.')");
        }
    }

    if(!empty($lap_lama['id_user'])){
        $pesan_map = [
            'diproses'         => 'Laporan kamu sedang dalam proses review oleh admin.',
            'ditindaklanjuti'  => 'Laporan kamu sudah diteruskan ke Tim Investigasi Kampus.',
            'selesai'          => 'Laporan kamu telah selesai ditangani.',
            'ditolak'          => 'Laporan kamu tidak dapat diproses.',
            'menunggu'         => 'Status laporan kamu dikembalikan ke menunggu verifikasi.',
        ];
        $pesan_notif = mysqli_real_escape_string($conn, $pesan_map[$status] ?? 'Status laporan kamu telah diperbarui.');
        mysqli_query($conn, "INSERT INTO notifikasi (id_user, id_laporan, judul, pesan) VALUES ({$lap_lama['id_user']}, $id, 'Update Status Laporan [$kode]', '$pesan_notif')");
    }

    header("Location: verifikasi_laporan.php?success=1");
    exit;
}

$filter = isset($_GET['filter']) ? mysqli_real_escape_string($conn, $_GET['filter']) : 'menunggu';
if(!in_array($filter, $valid_status)) $filter = 'menunggu';

$query = mysqli_query($conn, "SELECT l.*, u.username FROM laporan l LEFT JOIN users u ON l.id_user = u.id_user WHERE l.status_laporan = '$filter' ORDER BY l.tanggal_laporan DESC");
$laporan_list = mysqli_fetch_all($query, MYSQLI_ASSOC);
$total = count($laporan_list);

$bukti_map = [];
if($total > 0){
    $ids = array_map(fn($r) => (int)$r['id_laporan'], $laporan_list);
    $bukti_q = mysqli_query($conn, "SELECT * FROM bukti WHERE id_laporan IN (" . implode(',', $ids) . ") ORDER BY tanggal_upload ASC");
    while($b = mysqli_fetch_assoc($bukti_q)){
        $bukti_map[$b['id_laporan']][] = $b;
    }
}

define('BUKTI_BASE_PATH', '../uploads/bukti/');
define('BUKTI_SERVER_PATH', dirname(__DIR__) . '/uploads/bukti/');
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
        .badge-kategori {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .kat-khusus { background-color: #ffe4e6; color: #e11d48; border: 1px solid #fda4af; }
        .kat-umum { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
    </style>
</head>
<body>

<!-- SIDEBAR FIX: Dikembalikan lengkap sesuai dashboard_admin.php -->
<aside class="sidebar">
    <div class="logo-area">
        <div class="logo-icon"></div>
        <div><h1 class="logo-title">SIRAKELIKA</h1><p class="logo-sub">ADMINISTRATOR</p></div>
    </div>
    <nav class="nav-container">
        <div class="nav-group">SYSTEM CONTROL</div>
        <a href="dashboard_admin.php" class="nav-link"><span class="nav-text">Dashboard</span></a>
        
        <div class="nav-group">MANAJEMEN</div>
        <a href="verifikasi_laporan.php" class="nav-link active"><span class="nav-text">Verifikasi Laporan Masuk</span></a>
        <!-- Silakan sesuaikan nama file href di bawah jika nama file aslimu berbeda -->
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
            <div class="avatar"><?= strtoupper(substr($_SESSION['admin_name'], 0, 2)); ?></div>
            <div class="user-info">
                <span class="user-name"><?= htmlspecialchars($_SESSION['admin_name']); ?></span>
                <span class="user-role">Sistem Administrator</span>
            </div>
        </div>
    </header>

    <div class="content-title">
        <h2>Verifikasi Laporan Masuk</h2>
        <p>Kelola dan ubah status laporan kekerasan kampus</p>
    </div>

    <div class="filter-bar">
        <?php foreach($valid_status as $s): ?>
        <a href="?filter=<?= $s ?>" class="filter-btn <?= $filter===$s ? 'active' : '' ?>"><?= ucfirst($s) ?></a>
        <?php endforeach; ?>
    </div>

    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>KODE</th>
                    <th>JUDUL LAPORAN</th>
                    <th>KATEGORI</th>
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

                // FIX: Deklarasi $kategori DULU sebelum dipakai
                $kategori = isset($row['jenis_pelaporan']) ? strtolower($row['jenis_pelaporan']) : 'umum';

                // Sembunyikan identitas jika laporan UMUM
                if($kategori === 'umum'){
                    $pelapor = '<em style="color:#94a3b8">Tersembunyi</em>';
                } else {
                    $pelapor = $row['id_user'] ? htmlspecialchars($row['username']) : '<em style="color:#94a3b8">Anonim</em>';
                }

                // Untuk KHUSUS: ekstrak nama/NIM dari deskripsi, pisahkan kronologi
                $identitas_nama = ''; $identitas_nim = ''; $kronologi_bersih = $row['deskripsi'] ?? '';
                if($kategori === 'khusus' && strpos($row['deskripsi'] ?? '', '--- IDENTITAS PELAPOR (KHUSUS) ---') !== false){
                    preg_match('/Nama:\s*(.+)/u', $row['deskripsi'], $mNama);
                    preg_match('/NIM:\s*(.+)/u', $row['deskripsi'], $mNim);
                    $identitas_nama = trim($mNama[1] ?? '');
                    $identitas_nim  = trim($mNim[1] ?? '');
                    $posCronologi = strpos($row['deskripsi'], '--- KRONOLOGI KEJADIAN ---');
                    $kronologi_bersih = $posCronologi !== false ? trim(substr($row['deskripsi'], $posCronologi + strlen('--- KRONOLOGI KEJADIAN ---'))) : $row['deskripsi'];
                }
                
            ?>
            <tr>
                <td class="id-case"><?= $kode ?></td>
                <td><strong><?= htmlspecialchars($row['judul_laporan'] ?: '(tanpa judul)') ?></strong></td>
                <td>
                    <?php if($kategori === 'khusus'): ?>
                        <span class="badge-kategori kat-khusus">Khusus</span>
                    <?php else: ?>
                        <span class="badge-kategori kat-umum">Umum</span>
                    <?php endif; ?>
                </td>
                <td><?= $pelapor ?></td>
                <td><?= htmlspecialchars($row['jenis_kekerasan'] ?: '-') ?></td>
                <td><?= date('d M Y', strtotime($row['tanggal_laporan'])) ?></td>
                <td><span class="badge-status s-<?= $row['status_laporan'] ?>"><?= ucfirst($row['status_laporan']) ?></span></td>
                <td><button type="button" class="btn-verif" id="btnToggle<?= $id_lap ?>" onclick="toggleDetail(<?= $id_lap ?>)">Lihat & Verifikasi</button></td>
            </tr>
            <tr class="detail-row" id="detailRow<?= $id_lap ?>">
                <td colspan="8">
                    <div class="detail-panel">
                        <!-- IDENTITAS PELAPOR -->
                        <?php if($kategori === 'khusus'): ?>
                        <div class="detail-block" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
                            <span class="detail-label" style="color:#1d4ed8;">Identitas Pelapor (Laporan Khusus)</span>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:8px;">
                                <div>
                                    <span class="detail-label">Nama</span>
                                    <span class="detail-value"><?= htmlspecialchars($identitas_nama ?: '-') ?></span>
                                </div>
                                <div>
                                    <span class="detail-label">NIM</span>
                                    <span class="detail-value"><?= htmlspecialchars($identitas_nim ?: '-') ?></span>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="detail-block" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;margin-bottom:16px;">
                            <span class="detail-label">Identitas Pelapor</span>
                            <p style="font-size:13px;color:#94a3b8;margin-top:6px;font-style:italic;">Identitas disembunyikan — pelapor memilih laporan umum/anonim.</p>
                        </div>
                        <?php endif; ?>

                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Waktu & Lokasi Kejadian</span>
                                <span class="detail-value"><?= htmlspecialchars($row['lokasi_kejadian']) ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Sifat Laporan</span>
                                <span class="detail-value"><strong><?= ucfirst($kategori) ?></strong></span>
                            </div>
                        </div>

                        <div class="detail-block">
                            <span class="detail-label">Kronologi / Deskripsi Kejadian</span>
                            <p class="detail-text"><?= nl2br(htmlspecialchars($kronologi_bersih ?: '-')) ?></p>
                        </div>

                        <div class="detail-block">
                            <span class="detail-label">Bukti Pendukung</span>
                            <?php 
                            $list_bukti = $bukti_map[$id_lap] ?? [];
                            if(empty($list_bukti)): 
                            ?>
                                <p class="no-bukti">Tidak ada bukti yang diupload pelapor.</p>
                            <?php 
                            else: 
                            ?>
                                <div class="bukti-grid">
                                    <?php foreach($list_bukti as $b): 
                                        $nama_file = $b['file_bukti']; 
                                        $url       = BUKTI_BASE_PATH . $nama_file;
                                        $srv_path  = BUKTI_SERVER_PATH . $nama_file;
                                        $ext       = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
                                        
                                        if (file_exists($srv_path)):
                                    ?>
                                        <div class="bukti-item" style="margin-bottom:10px;">
                                        <?php if(in_array($ext, ['jpg','jpeg','png'])): ?>
                                            <a href="<?= htmlspecialchars($url) ?>" target="_blank"><img src="<?= htmlspecialchars($url) ?>" class="bukti-img" style="max-width:200px; border-radius:6px;" alt="Bukti"></a>
                                        <?php elseif(in_array($ext, ['mp4','mov','avi'])): ?>
                                            <video src="<?= htmlspecialchars($url) ?>" controls class="bukti-video" style="max-width:300px;"></video>
                                        <?php else: ?>
                                            <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="bukti-file-link">📄 <?= htmlspecialchars($b['nama_asli'] ?: $nama_file) ?></a>
                                        <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="no-bukti" style="color:#ef4444;">⚠ File berkas (<?= htmlspecialchars($nama_file) ?>) tidak ditemukan fisik di server.</p>
                                    <?php endif; endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr class="modal-divider">

                        <form method="POST" class="status-form">
                            <input type="hidden" name="update_status" value="1"><input type="hidden" name="id_laporan" value="<?= $id_lap ?>">
                            <div class="form-row-inline">
                                <div class="form-group">
                                    <label>Status Baru</label>
                                    <select name="status_baru">
                                        <?php foreach($valid_status as $s): ?>
                                        <option value="<?= $s ?>" <?= $row['status_laporan']===$s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group" style="flex:1;"><label>Catatan</label><textarea name="catatan"></textarea></div>
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
            <tr><td colspan="8" style="text-align:center;padding:30px;color:#94a3b8;">Tidak ada laporan.</td></tr>
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

    if(!isOpen){ row.classList.add('show'); btn.textContent = 'Tutup Detail'; }
}
</script>
</body>
</html>