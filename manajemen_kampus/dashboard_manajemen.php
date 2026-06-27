<?php
session_start();
include 'conn.php';

// Proteksi Halaman: Pastikan hanya User Manajemen Kampus yang bisa masuk
if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'manajemen'){
    header("Location: login.php"); 
    exit;
}

// Mengambil session nama lengkap / username aktif yang dilempar dari login.php Anda
$username_aktif = !empty($_SESSION['nama']) ? $_SESSION['nama'] : $_SESSION['username']; 

// =========================================================================
// Metrik Pengawasan Kasus (SINKRON DENGAN ENUM DATABASE: ditindaklanjuti & selesai)
// =========================================================================
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan");
$q_butuh_keputusan = mysqli_query($conn, "SELECT COUNT(*) as butuh FROM laporan WHERE status_laporan='ditindaklanjuti'");
$q_kasus_putus = mysqli_query($conn, "SELECT COUNT(*) as selesai FROM laporan WHERE status_laporan='selesai'");

$total_kasus     = mysqli_fetch_assoc($q_total)['total'];
$butuh_keputusan = mysqli_fetch_assoc($q_butuh_keputusan)['butuh'];
$kasus_putus     = mysqli_fetch_assoc($q_kasus_putus)['selesai']; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Dashboard Manajemen Kampus</title>
    <link rel="stylesheet" href="dashboard.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        .welcome-banner-manajemen {
            background: linear-gradient(135deg, #312e81, #4338ca);
            padding: 32px;
            border-radius: 16px;
            color: white;
            margin-bottom: 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .welcome-banner-manajemen h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-banner-manajemen p {
            font-size: 14px;
            color: #e0e7ff;
            line-height: 1.6;
            max-width: 750px;
        }

        .btn-tinjau {
            background-color: #4338ca;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-tinjau:hover {
            background-color: #312e81;
        }

        .btn-unduh {
            background-color: #10b981;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-unduh:hover {
            background-color: #059669;
        }

        .badge-manajemen {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
        }
        .bg-warning { background-color: #fef3c7; color: #d97706; }
        .bg-success { background-color: #dcfce7; color: #15803d; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="logo-area">
            <div class="logo-icon" style="background-color: #4338ca;"></div>
            <div>
                <h1 class="logo-title">SIRAKELIKA</h1>
                <p class="logo-sub">MANAJEMEN KAMPUS</p>
            </div>
        </div>

        <nav class="nav-container">
            <div class="nav-group">MONITORING UTAMA</div>
            <a href="dashboard_manajemen.php" class="nav-link active">
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="laporan_tren.php" class="nav-link">
                <span class="nav-text"> Laporan Tren Kasus</span>
            </a>
            
            <div class="nav-group">EKSEKUTIF & KEBIJAKAN</div>
            <a href="tinjau_hasil_investigasi.php" class="nav-link">
                <span class="nav-text"> Tinjau Hasil Investigasi</span>
            </a>
            <a href="surat_keputusan_sanksi.php" class="nav-link">
                <span class="nav-text"> Surat Keputusan Sanksi</span>
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
                <div class="avatar" style="background-color: #4338ca;">MK</div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($username_aktif); ?></span>
                    <span class="user-role">Pihak Manajemen Kampus</span>
                </div>
            </div>
        </header>

        <section class="welcome-banner-manajemen">
            <div class="banner-text">
                <h2>Pusat Evaluasi & Pengambil Kebijakan Kampus</h2>
                <p>Pantau hasil investigasi, tetapkan sanksi hukum kampus yang objektif, dan berikan arahan strategis guna menciptakan lingkungan Kampus yang aman, setara, dan terlindungi dari segala bentuk kekerasan.</p>
            </div>
        </section>

        <div class="content-title">
            <h2>Metrik Pengawasan Kasus</h2>
            <p>Data ringkas untuk dasar pertimbangan pengambilan keputusan strategis rektorat</p>
        </div>

        <section class="stats-grid">
            <div class="card card-total">
                <span class="card-num"><?php echo $total_kasus; ?></span>
                <span class="card-title">Total Aduan Masuk</span>
            </div>
            <div class="card card-process" style="border-left: 4px solid #d97706;">
                <span class="card-num" style="color: #d97706;"><?php echo $butuh_keputusan; ?></span>
                <span class="card-title">Butuh Rekomendasi Sanksi</span>
            </div>
            <div class="card card-done" style="border-left: 4px solid #16a34a;">
                <span class="card-num" style="color: #16a34a;"><?php echo $kasus_putus; ?></span>
                <span class="card-title">Kasus Selesai (SK Terbit)</span>
            </div>
        </section>

        <div class="data-grid">
            
            <div class="table-container" style="flex: 2;">
                <div class="table-header">
                    <div>
                        <h3>Berkas Hasil Investigasi (Semua Status)</h3>
                        <p>Daftar laporan baik yang membutuhkan kebijakan sanksi maupun yang sudah resmi diterbitkan Surat Keputusannya.</p>
                    </div>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID KASUS</th>
                            <th>KATEGORI</th>
                            <th>REKOMENDASI TIM / SK FILE</th>
                            <th>STATUS BERKAS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // DIUBAH: Mengambil data baik status 'ditindaklanjuti' maupun 'selesai'
                        $query_manajemen = mysqli_query($conn, "SELECT * FROM laporan WHERE status_laporan IN ('ditindaklanjuti', 'selesai') ORDER BY status_laporan ASC, id_laporan DESC LIMIT 10");
                        
                        if(mysqli_num_rows($query_manajemen) > 0) {
                            while($row = mysqli_fetch_assoc($query_manajemen)) {
                                $kategori = !empty($row['jenis_kekerasan']) ? $row['jenis_kekerasan'] : 'Umum / Fisik';
                                
                                echo "<tr>";
                                echo "<td class='id-case'>#KS-" . htmlspecialchars($row['id_laporan']) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($kategori) . "</strong></td>";
                                
                                // Jika status selesai, tampilkan info download SK, jika belum tampilkan rekomendasi tim
                                if ($row['status_laporan'] == 'selesai') {
                                    echo "<td><span style='color:#16a34a; font-weight:500;'>Dokumen SK Siap di Server</span></td>";
                                    echo "<td><span class='badge-manajemen bg-success'>Selesai (SK Terbit)</span></td>";
                                    echo "<td><a href='uploads/sk_sanksi/" . htmlspecialchars($row['file_sk']) . "' target='_blank' class='btn-unduh'>👁️ Buka SK</a></td>";
                                } else {
                                    $rekomendasi = !empty($row['rekomendasi_tim']) ? $row['rekomendasi_tim'] : 'Belum ada rekomendasi tertulis';
                                    echo "<td>" . htmlspecialchars($rekomendasi) . "</td>";
                                    echo "<td><span class='badge-manajemen bg-warning'>Butuh Kebijakan</span></td>";
                                    echo "<td><button class='btn-tinjau' onclick=\"location.href='surat_keputusan_sanksi.php'\">Tinjau & Putuskan</button></td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'><div class='empty-state'><p style='text-align:center; padding:25px; color:#64748b; background-color:#f1f5f9; border-radius:6px; margin:10px; font-weight:500;'>Belum ada berkas kasus investigasi pada tahap ini.</p></div></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="activity-container" style="flex: 1;">
                <h3>Arahan & Riwayat SK Terkini</h3>
                <p class="activity-sub">Kebijakan & penomoran SK sanksi otomatis dari sistem rektorat</p>
                
                <div class="timeline" style="max-height: 450px; overflow-y: auto; padding-right: 5px;">
                    <?php
                    // Query mengambil catatan log ketika status berubah menjadi 'selesai'
                    $query_log = mysqli_query($conn, "SELECT id_laporan, catatan, tanggal_update FROM status_laporan_log WHERE status_baru = 'selesai' ORDER BY id_log DESC LIMIT 5");

                    if(mysqli_num_rows($query_log) > 0) {
                        while($log = mysqli_fetch_assoc($query_log)) {
                            // Format Tanggal ringkas
                            $waktu = date('d M Y - H:i', strtotime($log['tanggal_update']));
                            ?>
                            <div class="timeline-item item-blue" style="border-left: 4px solid #4338ca; padding-left: 12px; margin-bottom: 15px; position: relative;">
                                <p class="timeline-text" style="font-size: 13px; margin: 0 0 4px 0; color: #334155;">
                                    <strong>Kasus #KS-<?php echo $log['id_laporan']; ?>:</strong> <br>
                                    <?php echo htmlspecialchars($log['catatan']); ?>
                                </p>
                                <span class="timeline-time" style="font-size: 11px; color: #94a3b8;"><?php echo $waktu; ?> WIB</span>
                            </div>
                            <?php
                        }
                    } else {
                        echo "<p style='color: #94a3b8; font-size: 13px; font-style: italic; text-align: center; padding-top: 20px;'>Belum ada riwayat penerbitan SK Surat Keputusan sanksi.</p>";
                    }
                    ?>
                </div>
            </div>

        </div>

    </main>

</body>
</html>