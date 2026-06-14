<?php
session_start();
include 'conn.php';

// Proteksi Ketat: Jika bukan admin, tendang langsung ke login khusus admin
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin'){
    header("Location: admin_login.php");
    exit;
}

// 1. QUERY METRIK KHUSUS MANAGEMENT ADMIN
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
    <title>SIRAKELIKA - Admin Panel Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Identitas visual khusus Admin Panel */
        .sidebar { background-color: #0f172a !important; } /* Hitam Pekat */
        .welcome-banner { background: linear-gradient(135deg, #dc2626, #991b1b) !important; } /* Merah Maroon berani */
        .btn-verif { background-color: #10b981; color: white; border: none; padding: 5px 10px; border-radius: 4px; font-size: 11px; cursor: pointer;}
        .btn-verif:hover { background-color: #059669; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="logo-area">
            <div class="logo-icon" style="background-color: #ef4444;"></div>
            <div>
                <h1 class="logo-title">SIRAKELIKA</h1>
                <p class="logo-sub" style="color: #ef4444;">ROOT ADMINISTRATOR</p>
            </div>
        </div>

        <nav class="nav-container">
            <div class="nav-group">SYSTEM PANEL</div>
            <a href="#" class="nav-link active"><span class="nav-text">Main Dashboard</span></a>
            
            <div class="nav-group">MANAJEMEN DATA (SRS BAB 4)</div>
            <a href="#" class="nav-link"><span class="nav-text">Verifikasi Laporan Masuk</span></a>
            <a href="#" class="nav-link"><span class="nav-text">Data Akun Mahasiswa</span></a>
            <a href="#" class="nav-link"><span class="nav-text">Data Akun Pihak Internal</span></a>
            
            <div class="nav-group">KONTROL UTAMA</div>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Keluar Panel</span></a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="topbar">
            <div></div>
            <div class="user-profile">
                <div class="avatar" style="background-color: #ef4444;">ADM</div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <span class="user-role">Sistem Administrator</span>
                </div>
            </div>
        </header>

        <section class="welcome-banner">
            <div class="banner-text">
                <h2>Selamat Datang di Admin Pusat SIRAKELIKA</h2>
                <p>Mulai kelola sistem, lakukan verifikasi keaslian dokumen atau bukti laporan masuk awal sebelum diteruskan ke Tim Investigasi kampus.</p>
            </div>
        </section>

        <section class="stats-grid">
            <div class="card">
                <span class="card-num"><?php echo $count_verif; ?></span>
                <span class="card-title" style="color: #ef4444; font-weight: bold;">Belum Diverifikasi</span>
            </div>
            <div class="card">
                <span class="card-num"><?php echo $count_lap; ?></span>
                <span class="card-title">Total Semua Laporan</span>
            </div>
            <div class="card">
                <span class="card-num"><?php echo $count_mhs; ?></span>
                <span class="card-title">Total User Mahasiswa</span>
            </div>
            <div class="card">
                <span class="card-num"><?php echo $count_tim; ?></span>
                <span class="card-title">Total Akun Investigasi</span>
            </div>
        </section>

        <div class="data-grid">
            <div class="table-container" style="flex: 2;">
                <h3>Antrean Validasi Laporan Masuk</h3>
                <table class="data-table" style="width: 100%; margin-top: 15px;">
                    <thead>
                        <tr>
                            <th>ID LAPORAN</th>
                            <th>JENIS KASUS</th>
                            <th>TANGGAL MASUK</th>
                            <th>TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Tampilkan laporan yang butuh validasi awal admin
                        $query_antrean = mysqli_query($conn, "SELECT * FROM laporan WHERE status_laporan='Baru' ORDER BY id_laporan DESC LIMIT 5");
                        if(mysqli_num_rows($query_antrean) > 0){
                            while($row = mysqli_fetch_assoc($query_antrean)){
                                echo "<tr>";
                                echo "<td>#KS-".$row['id_laporan']."</td>";
                                echo "<td><strong>".htmlspecialchars($row['jenis_laporan'])."</strong></td>";
                                echo "<td>".date('d M Y', strtotime($row['tanggal_laporan']))."</td>";
                                echo "<td><button class='btn-verif' onclick=\"location.href='verifikasi_kasus.php?id=".$row['id_laporan']."'\">Validasi & Teruskan</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center; padding: 20px; color:#94a3b8;'>Semua laporan masuk telah bersih diverifikasi.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>