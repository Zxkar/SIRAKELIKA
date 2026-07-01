<?php
session_start();


// Prevent browser caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

include '../config/conn.php';
if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'mahasiswa'){
    header("Location: login.php"); 
    exit;
}

$id_user = (int) $_SESSION['id_user'];

$query_total   = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan WHERE id_user=$id_user");
$query_baru    = mysqli_query($conn, "SELECT COUNT(*) as baru FROM laporan WHERE id_user=$id_user AND LOWER(status_laporan)='menunggu'");
$query_proses  = mysqli_query($conn, "SELECT COUNT(*) as proses FROM laporan WHERE id_user=$id_user AND LOWER(status_laporan) IN ('diproses','ditindaklanjuti','mediasi')");
$query_selesai = mysqli_query($conn, "SELECT COUNT(*) as selesai FROM laporan WHERE id_user=$id_user AND LOWER(status_laporan)='selesai'");

$data_total   = mysqli_fetch_assoc($query_total)['total'];
$data_baru    = mysqli_fetch_assoc($query_baru)['baru'];
$data_proses  = mysqli_fetch_assoc($query_proses)['proses'];
$data_selesai = mysqli_fetch_assoc($query_selesai)['selesai'];

// Laporan terbaru (maks 5)
$query_terbaru = mysqli_query($conn, "SELECT * FROM laporan WHERE id_user=$id_user ORDER BY tanggal_laporan DESC LIMIT 5");

function getStatusBadge($status){
    $status = strtolower(trim($status));
    if($status === 'menunggu') return ['Baru','status-new'];
    if(in_array($status, ['diproses','ditindaklanjuti','mediasi'])) return ['Diproses','status-process'];
    if($status === 'selesai') return ['Selesai','status-done'];
    if($status === 'ditolak') return ['Ditolak','status-new'];
    return [ucfirst($status),'status-new'];
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
    <script>
    window.addEventListener('pageshow', function(e) {
        if (e.persisted) window.location.reload();
    });
</script>

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
            <a href="#" class="nav-link active">
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="laporan.php" class="nav-link">
                <span class="nav-text">Laporan Saya</span>
            </a>

            <div class="nav-group">PENGELOLAAN</div>
            <a href="edukasi1.php" class="nav-link">
                <span class="nav-text">Edukasi & Informasi</span>
            </a>
            <a href="kenali.php" class="nav-link">
                <span class="nav-text">Kenali Situasi Anda</span>
            </a>

            <div class="nav-group">AKUN</div>
            <a href="profil.php" class="nav-link">
                <span class="nav-text">Profil</span>
            </a>
            <a href="../auth/logout.php" class="nav-link logout">
                <span class="nav-text">Keluar</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        
        <header class="topbar">
            <div></div> 
            <div class="user-profile">
                <div class="notif-btn">
                     <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?></div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <span class="user-role">Mahasiswa</span>
                </div>
            </div>
        </header>

        <section class="welcome-banner">
            <div class="banner-text">
                <h2>Selamat Datang di SIRAKELIKA</h2>
                <p>Sistem Pelaporan Kekerasan di Lingkungan Kampus. Laporkan dengan aman, anonim, dan terlindungi. Kami ada untuk kamu.</p>
            </div>
            <a href="buat_laporan.php">
            <button class="btn-report">+ Buat Laporan Baru</button>
            </a>
        </section>

        <div class="content-title">
            <h2>Dashboard</h2>
            <p>Ringkasan aktivitas dan status laporan kekerasan kampus</p>
        </div>

        <section class="stats-grid">
            <div class="card card-total">
                <span class="card-num"><?php echo $data_total; ?></span>
                <span class="card-title">Total Laporan</span>
            </div>
            <div class="card card-new">
                <span class="card-num"><?php echo $data_baru; ?></span>
                <span class="card-title">Laporan Baru</span>
            </div>
            <div class="card card-process">
                <span class="card-num"><?php echo $data_proses; ?></span>
                <span class="card-title">Dalam Proses</span>
            </div>
            <div class="card card-done">
                <span class="card-num"><?php echo $data_selesai; ?></span>
                <span class="card-title">Selesai Ditangani</span>
            </div>
        </section>

        <div class="data-grid">
            
            <div class="table-container">
                <div class="table-header">
                    <div>
                        <h3>Laporan Terbaru</h3>
                        <p>Daftar riwayat pelaporan aktif Anda</p>
                    </div>
                    <button class="btn-view-all">Lihat Semua →</button>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID LAPORAN</th>
                            <th>JENIS KEKERASAN</th>
                            <th>LOKASI</th>
                            <th>TANGGAL</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($query_terbaru) > 0): ?>
                            <?php while($l = mysqli_fetch_assoc($query_terbaru)):
                                $kode = $l['kode_laporan'] ?? ('KS-'.$l['id_laporan']);
                                $tgl  = !empty($l['tanggal_laporan']) ? date('d M Y', strtotime($l['tanggal_laporan'])) : '-';
                                list($label, $class) = getStatusBadge($l['status_laporan']);
                            ?>
                            <tr>
                                <td class="id-case">#<?php echo htmlspecialchars($kode); ?></td>
                                <td><strong><?php echo htmlspecialchars($l['jenis_kekerasan'] ?? '-'); ?></strong></td>
                                <td><?php echo htmlspecialchars($l['lokasi_kejadian'] ?? '-'); ?></td>
                                <td><?php echo $tgl; ?></td>
                                <td><span class="status-badge <?php echo $class; ?>"><?php echo $label; ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; color:#94a3b8; padding:32px 8px;">Belum ada laporan yang dibuat</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="activity-container">
                <h3>Aktivitas Terbaru</h3>
                <p class="activity-sub">Update status kasus</p>
                
                <div class="timeline">
                    <div class="timeline-item item-red">
                        <p class="timeline-text">Laporan baru <strong>#KS-2026-003</strong> berhasil dikirim ke sistem</p>
                        <span class="timeline-time">Baru saja</span>
                    </div>
                    <div class="timeline-item item-blue">
                        <p class="timeline-text">Kasus <strong>#KS-2026-002</strong> sedang ditinjau oleh Tim Investigasi</p>
                        <span class="timeline-time">4 hari lalu</span>
                    </div>
                    <div class="timeline-item item-green">
                        <p class="timeline-text">Pendampingan psikologis kasus <strong>#KS-2026-001</strong> dinyatakan selesai</p>
                        <span class="timeline-time">1 minggu lalu</span>
                    </div>
                </div>
            </div>

        </div>

    </main>

</body>
</html>
