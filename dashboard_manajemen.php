<?php
session_start();
include 'conn.php';

if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'manajemen'){
    header("Location: login.php"); 
    exit;
}

$username_aktif = $_SESSION['username']; 


$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan");
$q_butuh_keputusan = mysqli_query($conn, "SELECT COUNT(*) as butuh FROM laporan WHERE status_laporan='Diproses'");
$q_kasus_putus = mysqli_query($conn, "SELECT COUNT(*) as selesai FROM laporan WHERE status_laporan='Selesai'");

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
        /* Mengembalikan flexbox layout agar kembali sejajar dan rapi seperti dashboard investigasi */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            display: flex;
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

        .badge-manajemen {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .bg-warning { background-color: #fef3c7; color: #d97706; }
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
            <div class="nav-group">MONITORING & PENGAWASAN</div>
            <a href="#" class="nav-link active">
                <span class="nav-text">Dashboard</span>
            </a>
            
            <div class="nav-group">PENGAMBILAN KEPUTUSAN</div>
            <a href="tinjau_investigasi.php" class="nav-link">
                <span class="nav-text">Tinjau Hasil Investigasi</span>
            </a>
            <a href="keputusan_sanksi.php" class="nav-link">
                <span class="nav-text">Surat Keputusan Sanksi</span>
            </a>

            <div class="nav-group">IMPLEMENTASI KEBIJAKAN</div>
            <a href="arahan_kebijakan.php" class="nav-link">
                <span class="nav-text">Arahan & Sosialisasi</span>
            </a>
            <a href="statistik_kampus.php" class="nav-link">
                <span class="nav-text">Laporan Tren Kasus</span>
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
                <p>Pantau hasil investigasi, tetapkan sanksi hukum kampus yang objektif, dan berikan arahan strategis guna menciptakan lingkungan Institut Teknologi B.J. Habibie yang aman, setara, dan terlindungi dari segala bentuk kekerasan.</p>
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
                        <h3>Berkas Hasil Investigasi (Menunggu Putusan)</h3>
                        <p>Daftar laporan yang telah rampung diperiksa Tim Investigasi dan menunggu keputusan kebijakan sanksi Anda</p>
                    </div>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID KASUS</th>
                            <th>KATEGORI</th>
                            <th>REKOMENDASI TIM INVESTIGASI</th>
                            <th>STATUS BERKAS</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_manajemen = mysqli_query($conn, "SELECT * FROM laporan WHERE status_laporan='Diproses' ORDER BY id_laporan DESC LIMIT 5");
                        
                        if(mysqli_num_rows($query_manajemen) > 0) {
                            while($row = mysqli_fetch_assoc($query_manajemen)) {
                                echo "<tr>";
                                echo "<td class='id-case'>#KS-" . $row['id_laporan'] . "</td>";
                                echo "<td><strong>" . htmlspecialchars($row['jenis_laporan']) . "</strong></td>";
                                echo "<td><em style='color: #475569;'>Menunggu peninjauan berkas BAP...</em></td>";
                                echo "<td><span class='badge-manajemen bg-warning'>Butuh Kebijakan</span></td>";
                                echo "<td><button class='btn-tinjau' onclick=\"location.href='tinjau_kasus.php?id=" . $row['id_laporan'] . "'\">Tinjau & Putuskan</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'><div class='empty-state'><p style='text-align:center; padding:20px; color:#64748b;'>Belum ada berkas hasil investigasi baru yang masuk ke meja manajemen.</p></div></td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="activity-container" style="flex: 1;">
                <h3>Arahan Implementasi Terkini</h3>
                <p class="activity-sub">Kebijakan & instruksi penanganan dari manajemen kampus</p>
                
                <div class="timeline">
                    <div class="timeline-item item-blue" style="border-left-color: #4338ca;">
                        <p class="timeline-text"><strong>SK Rektor No. 12/2026:</strong> Pemberhentian tidak hormat pelaku kasus kekerasan seksual #KS-004 resmi diimplementasikan.</p>
                        <span class="timeline-time">Kemarin</span>
                    </div>
                    <div class="timeline-item item-green" style="border-left-color: #16a34a;">
                        <p class="timeline-text">Instruksi pengetatan CCTV area koridor belakang gedung perkuliahan bersama disetujui.</p>
                        <span class="timeline-time">5 hari lalu</span>
                    </div>
                </div>
            </div>

        </div>

    </main>

</body>
</html>