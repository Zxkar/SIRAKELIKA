<?php
session_start();
include 'connection.php';

// PROTEKSI KETAT: Jika bukan admin yang login, tendang ke halaman login khusus admin
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin'){
    header("Location: login_admin.php");
    exit;
}

// 1. QUERY METRIK KHUSUS MANAGEMENT ADMIN (SESUAI SRS)
// Menghitung data statistik secara real-time dari database
$query_total_mhs  = mysqli_query($conn, "SELECT COUNT(*) as total FROM mahasiswa");
$query_total_tim  = mysqli_query($conn, "SELECT COUNT(*) as total FROM tim_investigasi");
$query_laporan_in = mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan");
$query_belum_verif= mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan WHERE status_laporan='Baru'");

$count_mhs    = mysqli_fetch_assoc($query_total_mhs)['total'];
$count_tim    = mysqli_fetch_assoc($query_total_tim)['total'];
$count_lap    = mysqli_fetch_assoc($query_laporan_in)['total'];
$count_verif  = mysqli_fetch_assoc($query_belum_verif)['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Admin Panel</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        
        /* Mengubah Sidebar menjadi cerah/putih bersih */
        .sidebar { 
            background-color: #ffffff !important; 
            border-right: 1px solid #e2e8f0 !important;
        }

        /* Memastikan warna teks judul aplikasi terlihat sangat jelas */
        .logo-title {
            color: #0f172a !important;
        }
        .logo-sub {
            color: #dc2626 !important;
            font-weight: 700;
        }

        .nav-group {
            color: #64748b !important;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 24px;
        }

        .nav-link {
            color: #334155 !important;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            background-color: #f1f5f9 !important; 
            color: #dc2626 !important; 
        }

        .nav-link.active {
            background-color: #fef2f2 !important; 
            color: #dc2626 !important; 
            font-weight: 600;
        }

        .nav-link.logout {
            color: #ef4444 !important;
        }
        .nav-link.logout:hover {
            background-color: #fef2f2 !important;
        }

        .welcome-banner-admin {
            background: linear-gradient(135deg, #dc2626, #991b1b) !important;
            padding: 32px;
            border-radius: 16px;
            color: white;
            margin-bottom: 28px;
        }
        .welcome-banner-admin h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #ffffff;
        }
        .welcome-banner-admin p {
            font-size: 14px;
            color: #fecaca;
            line-height: 1.6;
            max-width: 700px;
        }

        .btn-verif { 
            background-color: #10b981; 
            color: white; 
            border: none; 
            padding: 6px 12px; 
            border-radius: 6px; 
            font-size: 11px; 
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.2s;
        }
        .btn-verif:hover { 
            background-color: #059669; 
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="logo-area">
            <div class="logo-icon" style="background-color: #dc2626;"></div>
            <div>
                <h1 class="logo-title">SIRAKELIKA</h1>
                <p class="logo-sub">ADMINISTRATOR</p>
            </div>
        </div>

        <nav class="nav-container">
            <div class="nav-group">SYSTEM CONTROL</div>
            <a href="#" class="nav-link active">
                <span class="nav-text">Dashboard</span>
            </a>
            
            <div class="nav-group">MANAJEMEN</div>
            <a href="#" class="nav-link">
                <span class="nav-text">Verifikasi Laporan Masuk</span>
            </a>
            <a href="#" class="nav-link">
                <span class="nav-text">kelola Akun Mahasiswa</span>
            </a>
            <a href="#" class="nav-link">
                <span class="nav-text">kelola Akun Pihak Internal</span>
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
                <div class="avatar" style="background-color: #dc2626; color: white;">ADM</div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <span class="user-role">Sistem Administrator</span>
                </div>
            </div>
        </header>

        <section class="welcome-banner-admin">
            <div class="banner-text">
                <h2>Selamat Datang di Admin Pusat SIRAKELIKA</h2>
                <p>Hak akses penuh administrator. Sesuai mandat SRS, Anda bertanggung jawab penuh melakukan validasi administrasi berkas di awal masuk sistem sebelum diteruskan ke Tim Investigasi Kampus.</p>
            </div>
        </section>

        <div class="content-title">
            <h2>Ringkasan Statistik Sistem</h2>
            <p>Pantau data akun pengguna dan antrean validasi kasus aktif</p>
        </div>

        <section class="stats-grid">
            <div class="card" style="border-top: 4px solid #ef4444;">
                <span class="card-num" style="color: #ef4444;"><?php echo $count_verif; ?></span>
                <span class="card-title" style="font-weight: 600;">Belum Diverifikasi</span>
            </div>
            <div class="card" style="border-top: 4px solid #3b82f6;">
                <span class="card-num"><?php echo $count_lap; ?></span>
                <span class="card-title">Total Semua Laporan</span>
            </div>
            <div class="card" style="border-top: 4px solid #10b981;">
                <span class="card-num"><?php echo $count_mhs; ?></span>
                <span class="card-title">Total User Mahasiswa</span>
            </div>
            <div class="card" style="border-top: 4px solid #f59e0b;">
                <span class="card-num"><?php echo $count_tim; ?></span>
                <span class="card-title">Total Akun Investigasi</span>
            </div>
        </section>

        <div class="data-grid">
            <div class="table-container" style="flex: 2;">
                <div class="table-header">
                    <div>
                        <h3>Antrean Validasi Laporan Masuk</h3>
                        <p>Daftar laporan baru yang memerlukan verifikasi administrasi awal</p>
                    </div>
                </div>
                
                <table class="data-table" style="width: 100%; margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>ID LAPORAN</th>
                            <th>JENIS KASUS KEKERASAN</th>
                            <th>TANGGAL MASUK</th>
                            <th>TINDAKAN ADMIN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Menampilkan 5 laporan berstatus 'Baru' yang masuk ke sistem
                        $query_antrean = mysqli_query($conn, "SELECT * FROM laporan WHERE status_laporan='Baru' ORDER BY id_laporan DESC LIMIT 5");
                        
                        if(mysqli_num_rows($query_antrean) > 0){
                            while($row = mysqli_fetch_assoc($query_antrean)){
                                echo "<tr>";
                                echo "<td class='id-case'>#KS-".$row['id_laporan']."</td>";
                                echo "<td><strong>".htmlspecialchars($row['jenis_laporan'])."</strong></td>";
                                echo "<td>".date('d M Y', strtotime($row['tanggal_laporan']))."</td>";
                                echo "<td><button class='btn-verif' onclick=\"location.href='verifikasi_kasus.php?id=".$row['id_laporan']."'\">Validasi & Teruskan</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; padding: 30px; color:#94a3b8;'> Semua laporan masuk telah bersih diverifikasi awal.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>