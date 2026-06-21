<?php
session_start();
include 'conn.php';

if(!isset($_SESSION['admin_logged_in']) || $_SESSION['role'] !== 'admin'){
    header("Location: login_admin.php");
    exit;
}

// Query sesuai tabel users (bukan mahasiswa/tim_investigasi terpisah)
$count_mhs   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='mahasiswa'"))['total'];
$count_tim   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='investigasi'"))['total'];
$count_lap   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan"))['total'];
$count_verif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM laporan WHERE status_laporan='menunggu'"))['total'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Admin Panel</title>
    <link rel="stylesheet" href="dashboard_admin.css">
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
        <a href="dashboard_admin.php" class="nav-link active">
            <span class="nav-text">Dashboard</span>
        </a>

        <div class="nav-group">MANAJEMEN</div>
        <a href="verifikasi_laporan.php" class="nav-link">
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

    <section class="welcome-banner-admin">
        <div class="banner-text">
            <h2>Selamat Datang di Admin Pusat SIRAKELIKA</h2>
            <p>Hak akses penuh administrator. Anda bertanggung jawab melakukan validasi administrasi berkas sebelum diteruskan ke Tim Investigasi Kampus.</p>
        </div>
    </section>

    <div class="content-title">
        <h2>Ringkasan Statistik Sistem</h2>
        <p>Pantau data akun pengguna dan antrean validasi kasus aktif</p>
    </div>

    <section class="stats-grid">
        <div class="card" style="border-top:4px solid #ef4444;">
            <span class="card-num" style="color:#ef4444;"><?php echo $count_verif; ?></span>
            <span class="card-title">Menunggu Verifikasi</span>
        </div>
        <div class="card" style="border-top:4px solid #3b82f6;">
            <span class="card-num"><?php echo $count_lap; ?></span>
            <span class="card-title">Total Semua Laporan</span>
        </div>
        <div class="card" style="border-top:4px solid #10b981;">
            <span class="card-num"><?php echo $count_mhs; ?></span>
            <span class="card-title">Total User Mahasiswa</span>
        </div>
        <div class="card" style="border-top:4px solid #f59e0b;">
            <span class="card-num"><?php echo $count_tim; ?></span>
            <span class="card-title">Total Akun Investigasi</span>
        </div>
    </section>

    <div class="data-grid">
        <div class="table-container">
            <div class="table-header">
                <div>
                    <h3>Antrean Validasi Laporan Masuk</h3>
                    <p>Laporan berstatus "menunggu" yang perlu diverifikasi</p>
                </div>
                <a href="verifikasi_laporan.php" style="text-decoration:none;">
                    <button class="btn-view-all">Lihat Semua →</button>
                </a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>KODE</th>
                        <th>JUDUL LAPORAN</th>
                        <th>JENIS KEKERASAN</th>
                        <th>TANGGAL MASUK</th>
                        <th>AKSI</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $q = mysqli_query($conn, "SELECT * FROM laporan WHERE status_laporan='menunggu' ORDER BY tanggal_laporan DESC LIMIT 5");
                if(mysqli_num_rows($q) > 0){
                    while($row = mysqli_fetch_assoc($q)){
                        $kode = $row['kode_laporan'] ?? '#KS-'.$row['id_laporan'];
                        echo "<tr>
                            <td class='id-case'>".htmlspecialchars($kode)."</td>
                            <td><strong>".htmlspecialchars($row['judul_laporan'])."</strong></td>
                            <td>".htmlspecialchars($row['jenis_kekerasan'])."</td>
                            <td>".date('d M Y', strtotime($row['tanggal_laporan']))."</td>
                            <td><button class='btn-verif' onclick=\"location.href='verifikasi_laporan.php?id=".$row['id_laporan']."'\">Verifikasi</button></td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;padding:30px;color:#94a3b8;'>✓ Semua laporan telah diverifikasi.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

</body>
</html>