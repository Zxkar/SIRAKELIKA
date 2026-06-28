<?php
session_start();
include 'conn.php';

if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'investigasi'){
    header("Location: login.php"); 
    exit;
}

$username_aktif = $_SESSION['username']; 


$query_total_kasus = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan");
$query_perlu_tindakan = mysqli_query($conn, "SELECT COUNT(*) as tindakan FROM laporan WHERE status_laporan='Baru'");
$query_sedang_selidik = mysqli_query($conn, "SELECT COUNT(*) as selidik FROM laporan WHERE status_laporan='Diproses'");
$query_kasus_selesai  = mysqli_query($conn, "SELECT COUNT(*) as selesai FROM laporan WHERE status_laporan='Selesai'");

$total_kasus    = mysqli_fetch_assoc($query_total_kasus)['total'];
$perlu_tindakan = mysqli_fetch_assoc($query_perlu_tindakan)['tindakan'];
$sedang_selidik = mysqli_fetch_assoc($query_sedang_selidik)['selidik'];
$kasus_selesai  = mysqli_fetch_assoc($query_kasus_selesai)['selesai'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Dashboard Investigasi</title>
    <link rel="stylesheet" href="dashboard_investigasi.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .welcome-banner-investigasi {
            background: linear-gradient(135deg, #1e293b, #334155);
            padding: 32px;
            border-radius: 16px;
            color: white;
            margin-bottom: 28px;
        }

        .welcome-banner-investigasi h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-banner-investigasi p {
            font-size: 14px;
            color: #cbd5e1;
            line-height: 1.6;
            max-width: 700px;
        }

        /* Action Button */
        .btn-action-investigasi {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 7px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .btn-action-investigasi:hover {
            background-color: #2563eb;
        }

        .btn-action-investigasi:active {
            transform: scale(0.97);
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 11px;
            display: inline-block;
        }

        .status-badge.status-done {
            background-color: #dcfce7;
            color: #15803d;
        }
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
            <a href="#" class="nav-link active">
                <span class="nav-text">Dashboard Tim</span>
            </a>
            
            <div class="nav-group">PENGELOLAAN KASUS</div>
            <a href="manajemen_kasus.php" class="nav-link">
                <span class="nav-text">Manajemen Kasus Masuk</span>
            </a>
            
            <a href="log_aktivitas.php" class="nav-link">
                <span class="nav-text">Log Aktivitas Kasus</span>
            </a>

            <div class="nav-group">AKUN SYSTEM</div>
            <a href="logout.php" class="nav-link logout">
                <span class="nav-text">Keluar</span>
            </a>
        </nav>
    </aside>

    <main class="main-content">
        
        <header class="topbar">
            <div></div> 
            <div class="user-profile">
                <div class="avatar">TI</div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($username_aktif); ?></span>
                    <span class="user-role">Tim Investigasi</span>
                </div>
            </div>
        </header>

        <section class="welcome-banner-investigasi">
            <div class="banner-text">
                <h2>Pusat Kendali Investigasi SIRAKELIKA</h2>
                <p>Selamat bekerja. Sesuai dengan deskripsi tugas kerja sistem, lakukan validasi, pemeriksaan saksi, dan penyusunan rekomendasi kasus kekerasan secara objektif, transparan, dan sesuai prosedur demi menjaga keadilan di kampus.</p>
            </div>
        </section>

        <div class="content-title">
            <h2>Ringkasan Kerja Investigasi</h2>
            <p>Pantau beban penanganan kasus kekerasan yang sedang aktif saat ini</p>
        </div>

        <section class="stats-grid">
            <div class="card card-total">
                <span class="card-num"><?php echo $total_kasus; ?></span>
                <span class="card-title">Total Semua Kasus</span>
            </div>
            <div class="card card-new">
                <span class="card-num"><?php echo $perlu_tindakan; ?></span>
                <span class="card-title">Perlu Tindakan (Baru)</span>
            </div>
            <div class="card card-process">
                <span class="card-num"><?php echo $sedang_selidik; ?></span>
                <span class="card-title">Sedang Diselidiki</span>
            </div>
            <div class="card card-done">
                <span class="card-num"><?php echo $kasus_selesai; ?></span>
                <span class="card-title">Kasus Selesai Arsip</span>
            </div>
        </section>

        <div class="data-grid">
            
            <div class="table-container">
                <div class="table-header">
                    <div>
                        <h3>Daftar Antrean Kasus Kampus</h3>
                        <p>Daftar laporan teratas yang memerlukan penanganan objektif & transparan</p>
                    </div>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID KASUS</th>
                            <th>JENIS KEKERASAN</th>
                            <th>LOKASI KEJADIAN</th>
                            <th>TANGGAL MASUK</th>
                            <th>STATUS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_laporan = mysqli_query($conn, "SELECT * FROM laporan ORDER BY id_laporan DESC LIMIT 5");
                        
                        if(mysqli_num_rows($query_laporan) > 0) {
                            while($row = mysqli_fetch_assoc($query_laporan)) {
                                $badge_class = 'status-new';
                                
                                if(strtolower($row['status_laporan']) == 'diproses') { 
                                    $badge_class = 'status-process'; 
                                } elseif(strtolower($row['status_laporan']) == 'selesai') { 
                                    $badge_class = 'status-done'; 
                                }
                                
                                echo "<tr>";
                                echo "<td class='id-case'>#KS-" . $row['id_laporan'] . "</td>";
                                echo "<td><strong>" . ucwords(htmlspecialchars($row['jenis_kekerasan'])) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($row['lokasi_kejadian']) . "</td>";
                                echo "<td>" . date('d M Y', strtotime($row['tanggal_laporan'])) . "</td>";
                                echo "<td><span class='status-badge {$badge_class}'>" . htmlspecialchars($row['status_laporan']) . "</span></td>";
                                echo "<td><button class='btn-action-investigasi' onclick=\"location.href='manajemen_kasus.php?id=" . $row['id_laporan'] . "'\">Kelola Kasus</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'><div class='empty-state'><p style='text-align:center; padding:20px; color:#64748b;'>Tidak ada laporan kekerasan masuk.</p></div></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="activity-container">
                <h3>Log Investigasi Terkini</h3>
                <p class="activity-sub">Histori perubahan status berkas</p>
                
                <div class="timeline">
                    <div class="timeline-item item-blue">
                        <p class="timeline-text">Berita Acara Pemeriksaan (BAP) untuk kasus <strong>#KS-2026-002</strong> berhasil dibuat.</p>
                        <span class="timeline-time">Hari ini</span>
                    </div>
                    <div class="timeline-item item-green">
                        <p class="timeline-text">Rekomendasi sanksi kasus <strong>#KS-2026-001</strong> dikirim ke Manajemen Kampus.</p>
                        <span class="timeline-time">3 hari lalu</span>
                    </div>
                    <div class="timeline-item item-red">
                        <p class="timeline-text">Notifikasi masuk: Kasus baru <strong>#KS-2026-003</strong> dialihkan oleh Admin.</p>
                        <span class="timeline-time">1 minggu lalu</span>
                    </div>
                </div>
            </div>

        </div>

    </main>

</body>
</html>
