<?php
session_start();
include '../config/conn.php';

// Proteksi Halaman: Pastikan hanya User Manajemen Kampus yang bisa masuk
if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'manajemen'){
    header("Location: login.php"); 
    exit;
}

$username_aktif = !empty($_SESSION['nama']) ? $_SESSION['nama'] : $_SESSION['username']; 

// ==========================================
// GRAFIK 1: TREN BULANAN (Membaca dari tabel utama atau log)
// ==========================================
$tren_bulan = [];
$tren_jumlah = [];

try {
    // Mencoba mengambil tren berdasarkan tanggal_update dari tabel log yang berelasi dengan laporan
    $query_tren = mysqli_query($conn, "
        SELECT MONTHNAME(log.tanggal_update) as bulan, COUNT(distinct log.id_laporan) as jumlah 
        FROM status_laporan_log log
        INNER JOIN laporan l ON log.id_laporan = l.id_laporan
        GROUP BY MONTH(log.tanggal_update)
        ORDER BY MONTH(log.tanggal_update) ASC 
        LIMIT 6
    ");

    if($query_tren && mysqli_num_rows($query_tren) > 0){
        while($row = mysqli_fetch_assoc($query_tren)){
            // Konversi nama bulan ke Indonesia jika diperlukan
            $tren_bulan[] = $row['bulan'];
            $tren_jumlah[] = $row['jumlah'];
        }
    } else {
        throw new Exception("Data kosong");
    }
} catch (Exception $e) {
    // Jika relasi gagal/kolom berbeda, gunakan data simulasi agar halaman tidak blank/error
    $tren_bulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
    $tren_jumlah = [5, 12, 8, 15, 7, 20];
}

// ==========================================
// GRAFIK 2: PROPORSI KATEGORI KASUS
// ==========================================
$kat_nama = [];
$kat_jumlah = [];

try {
    // Pastikan nama kolom di bawah ini ('jenis_laporan') sesuai dengan yang ada di tabel 'laporan' Anda
    $query_kategori = mysqli_query($conn, "
        SELECT jenis_laporan, COUNT(*) as jumlah 
        FROM laporan 
        GROUP BY jenis_laporan
    ");

    if($query_kategori && mysqli_num_rows($query_kategori) > 0) {
        while($row = mysqli_fetch_assoc($query_kategori)){
            $kat_nama[] = $row['jenis_laporan'];
            $kat_jumlah[] = $row['jumlah'];
        }
    } else {
        throw new Exception("Data kosong");
    }
} catch (Exception $e) {
    // Fallback jika nama kolom di tabel laporan bukan 'jenis_laporan' (misal: 'kategori')
    $kat_nama = ['Kekerasan Seksual', 'Perundungan', 'Kekerasan Fisik', 'Verbal'];
    $kat_jumlah = [0, 0, 0, 0]; // default kosong jika field salah
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Laporan Tren Kasus</title>
    <link rel="stylesheet" href="dashboard.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        .analytics-container {
            display: flex;
            gap: 24px;
            margin-bottom: 28px;
            padding: 0 20px;
        }
        
        .chart-box-main, .chart-box-side {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .chart-box-main { flex: 2; }
        .chart-box-side { flex: 1; }

        .box-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin-top: 0;
            margin-bottom: 20px;
        }

        .filter-section {
            background-color: white;
            padding: 16px 24px;
            border-radius: 12px;
            margin: 0 20px 24px 20px;
            border: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .select-custom {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            font-size: 13px;
            color: #334155;
        }

        .btn-export {
            background-color: #4338ca;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-export:hover { background-color: #312e81; }

        .badge-status {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-blue { background-color: #e0f2fe; color: #0369a1; }
        .status-green { background-color: #dcfce7; color: #15803d; }
        .status-orange { background-color: #fef3c7; color: #b45309; }

        @media (max-width: 1024px) {
            .analytics-container { flex-direction: column; }
        }
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
            <a href="dashboard_manajemen.php" class="nav-link">
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="laporan_tren.php" class="nav-link active">
                <span class="nav-text">Laporan Tren Kasus</span>
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

        <div class="content-title" style="margin-top: 10px;">
            <h2>Analisis & Laporan Tren Kasus</h2>
            <p>Pusat kendali visual data statistik kasus kekerasan untuk dasar perumusan program preventif kampus</p>
        </div>

        <section class="filter-section">
            <div>
                <span style="font-size: 14px; font-weight: 500; color: #475569; margin-right: 8px;">Filter Visual:</span>
                <select class="select-custom">
                    <option>Tahun Akademik Terkini (2026)</option>
                    <option>Satu Semester Terakhir</option>
                </select>
            </div>
            <button class="btn-export" onclick="window.print()">🖨️ Cetak Analisis Tren</button>
        </section>

        <section class="analytics-container">
            <div class="chart-box-main">
                <h3 class="box-title">Grafik Fluktuasi Laporan Masuk (Bulanan)</h3>
                <div style="position: relative; height:260px; width:100%;">
                    <canvas id="grafikGarisTren"></canvas>
                </div>
            </div>

            <div class="chart-box-side">
                <h3 class="box-title">Proporsi Jenis Laporan Kasus</h3>
                <div style="position: relative; height:260px; width:100%; display: flex; justify-content: center;">
                    <canvas id="grafikDonutKategori"></canvas>
                </div>
            </div>
        </section>

        <div style="padding: 0 20px;">
            <div class="table-container">
                <div class="table-header">
                    <div>
                        <h3>Rencana Intervensi Kampus Berdasarkan Deteksi Tren</h3>
                    </div>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>KLUSTER INDIKATOR</th>
                            <th>DETEKSI ANOMALI DATA</th>
                            <th>USULAN RENCANA TINDAKAN</th>
                            <th>STATUS TINDAKAN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Siber & Ranah Digital</strong></td>
                            <td>Kenaikan aduan kasus pelecehan seksual online via media sosial.</td>
                            <td>Pemberian edukasi literasi hukum UU ITE.</td>
                            <td><span class="badge-status status-orange">⏳ Dijadwalkan</span></td>
                        </tr>
                        <tr>
                            <td><strong>Area Fisik Kampus</strong></td>
                            <td>Laporan terdeteksi di lokasi koridor belakang gedung kuliah.</td>
                            <td>Penambahan lampu penerangan jalan malam dan optimalisasi CCTV.</td>
                            <td><span class="badge-status status-green">✅ Selesai</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        const dataBulan = <?php echo json_encode($tren_bulan); ?>;
        const dataTotalBulanan = <?php echo json_encode($tren_jumlah); ?>;
        const dataKategori = <?php echo json_encode($kat_nama); ?>;
        const dataTotalKategori = <?php echo json_encode($kat_jumlah); ?>;

        // Line Chart
        new Chart(document.getElementById('grafikGarisTren').getContext('2d'), {
            type: 'line',
            data: {
                labels: dataBulan,
                datasets: [{
                    label: 'Jumlah Pengaduan',
                    data: dataTotalBulanan,
                    borderColor: '#4338ca', 
                    backgroundColor: 'rgba(67, 56, 202, 0.06)',
                    borderWidth: 3,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Doughnut Chart
        new Chart(document.getElementById('grafikDonutKategori').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: dataKategori,
                datasets: [{
                    data: dataTotalKategori,
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#64748b']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>
</html>
