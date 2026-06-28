<?php
session_start();
include 'conn.php';

// Proteksi Halaman: Pastikan hanya User Manajemen Kampus yang bisa masuk
if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'manajemen'){
    header("Location: login.php"); 
    exit;
}

// Mengambil session nama lengkap / username aktif
$username_aktif = !empty($_SESSION['nama']) ? $_SESSION['nama'] : $_SESSION['username']; 
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Tinjau Hasil Investigasi</title>
    <link rel="stylesheet" href="dashboard_manajemen.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        .badge-manajemen {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .bg-warning { background-color: #fef3c7; color: #d97706; }
        .bg-info { background-color: #e0f2fe; color: #0369a1; }

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

        .card-info-investigasi {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #1e3a8a;
            line-height: 1.5;
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
            <a href="laporan_tren.php" class="nav-link">
                <span class="nav-text">Laporan Tren Kasus</span>
            </a>
            
            <div class="nav-group">EKSEKUTIF & KEBIJAKAN</div>
            <a href="tinjau_hasil_investigasi.php" class="nav-link active">
                <span class="nav-text">Tinjau Hasil Investigasi</span>
            </a>
            <a href="surat_keputusan_sanksi.php" class="nav-link">
                <span class="nav-text">Surat Keputusan Sanksi</span>
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
            <h2>Peninjauan Berkas Hasil Investigasi</h2>
            <p>Daftar laporan kasus kekerasan yang telah selesai diproses oleh tim investigator dan siap divalidasi kebijakannya.</p>
        </div>

        <div class="card-info-investigasi">
            <strong>Petunjuk Manajemen:</strong> Berkas di bawah ini merupakan aduan riil yang memerlukan analisis kebijakan hukum/sanksi drop-out, skorsing, atau sanksi administratif lainnya sesuai regulasi Permendikbudristek pencegahan kekerasan di lingkungan kampus.
        </div>

        <div class="table-container">
            <div class="table-header">
                <div>
                    <h3>Berkas Kasus Masuk (Menunggu Putusan Rektorat)</h3>
                    <p>Silakan klik tombol "Tinjau & Putuskan" untuk membaca kronologi lengkap, bukti, dan rekomendasi tim pemeriksaan.</p>
                </div>
            </div>
            
            <table class="data-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th>ID KASUS</th>
                        <th>KATEGORI KASUS</th>
                        <th>REKOMENDASI TIM INVESTIGASI</th>
                        <th>STATUS BERKAS</th>
                        <th>AKSI EVALUASI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // 1. CEK STATUS KONEKSI KE DATABASE UTAMA
                        if (!isset($conn) || !$conn) {
                            throw new Exception("Variabel koneksi '\$conn' tidak terdefinisi atau bernilai salah. Periksa file conn.php.");
                        }

                        // Kirim sinyal sukses koneksi ke sistem log konsol browser
                        echo "<script>console.log('DATABASE STATUS: Berhasil terhubung ke database sirakelika.');</script>";

                        // 2. QUERY MENARIK DATA ASLI
                        $query_investigasi = mysqli_query($conn, "SELECT * FROM laporan WHERE status_laporan='ditindaklanjuti' ORDER BY id_laporan DESC");
                        
                        if($query_investigasi && mysqli_num_rows($query_investigasi) > 0) {
                            // JIKA KONEKSI SEHAT & DATA ADA DI DATABASE
                            while($row = mysqli_fetch_assoc($query_investigasi)) {
                                $kategori = !empty($row['jenis_kekerasan']) ? $row['jenis_kekerasan'] : 'Kasus Umum';
                                $rekomendasi = !empty($row['rekomendasi_tim']) ? $row['rekomendasi_tim'] : 'Belum ada rekomendasi tertulis';
                                
                                echo "<tr>";
                                echo "<td class='id-case'>#KS-" . htmlspecialchars($row['id_laporan']) . "</td>";
                                echo "<td><strong>" . htmlspecialchars($kategori) . "</strong></td>";
                                echo "<td>" . htmlspecialchars($rekomendasi) . "</td>";
                                echo "<td><span class='badge-manajemen bg-warning'>Menunggu Keputusan</span></td>";
                                echo "<td><button class='btn-tinjau' onclick=\"location.href='surat_keputusan_sanksi.php'\">Tinjau & Putuskan</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            // TAMPILAN TERBARU: KETIKA SELURUH BERKAS SELESAI DISAHKAN / ANTREAN KOSONG
                            echo "<tr>";
                            echo "<td colspan='5'>";
                            echo "    <div style='background-color: #f0fdf4; border: 1px dashed #bbf7d0; padding: 30px; text-align: center; border-radius: 8px; margin: 10px 0;'>";
                            echo "        <span style='font-size: 24px;'>🎉</span>";
                            echo "        <h3 style='margin: 10px 0 5px 0; color: #166534; font-weight: 600;'>Semua Antrean Berkas Kosong</h3>";
                            echo "        <p style='margin: 0; color: #15803d; font-size: 14px;'>Seluruh berkas hasil investigasi telah berhasil ditinjau dan diterbitkan Surat Keputusannya.</p>";
                            echo "    </div>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } catch (Exception $e) {
                        // JIKA TERJADI ERROR KONEKSI / TABEL TIDAK DITEMUKAN
                        echo "<tr><td colspan='5' style='text-align:center; padding:15px; background-color:#fef2f2; color:#991b1b; font-weight:600; font-size:13px;'>";
                        echo "🔴 Gagal Memuat Data: " . htmlspecialchars($e->getMessage());
                        echo "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>