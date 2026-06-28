<?php
session_start();
include '../config/conn.php';

// Proteksi Halaman: Pastikan hanya User Manajemen Kampus yang bisa masuk
if(!isset($_SESSION['username']) || $_SESSION['role'] !== 'manajemen'){
    header("Location: login.php"); 
    exit;
}

$username_aktif = !empty($_SESSION['nama']) ? $_SESSION['nama'] : $_SESSION['username'];
$pesan_sukses = "";
$pesan_error = "";

// ==========================================
// PROSES SUBMIT: KETIKA TOMBOL SAHKAN SK DIKLIK
// ==========================================
if (isset($_POST['terbitkan_sk'])) {
    $id_laporan = $_POST['id_laporan'];
    $nomor_sk = mysqli_real_escape_string($conn, $_POST['nomor_sk']);

    // Proses Upload File Dokumen SK
    $file_sk_nama = null;
    if (!empty($_FILES['file_sk']['name'])) {
        $upload_dir = 'uploads/sk_sanksi/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext_allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($_FILES['file_sk']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $ext_allowed)) {
            if ($_FILES['file_sk']['size'] <= 15 * 1024 * 1024) { // Maksimal 15MB
                $file_sk_nama = 'SK_' . $id_laporan . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($_FILES['file_sk']['tmp_name'], $upload_dir . $file_sk_nama)) {
                    $pesan_error = "Gagal mengunggah berkas dokumen ke server.";
                }
            } else {
                $pesan_error = "Ukuran file terlalu besar. Maksimal adalah 15MB.";
            }
        } else {
            $pesan_error = "Format file tidak didukung. Gunakan PDF, DOCX, atau Gambar.";
        }
    } else {
        $pesan_error = "Wajib memilih dan mengunggah berkas Dokumen SK Resmi.";
    }

    if (empty($pesan_error) && $file_sk_nama) {
        try {
            // 1. Update status laporan menjadi 'selesai' dan simpan file SK
            $update_laporan = mysqli_query($conn, "
                UPDATE laporan 
                SET status_laporan = 'selesai', 
                    file_sk = '$file_sk_nama'
                WHERE id_laporan = '$id_laporan'
            ");

            // 2. Catat riwayat perubahan ke tabel log status agar grafik tren ikut terupdate
            $insert_log = mysqli_query($conn, "
                INSERT INTO status_laporan_log (id_laporan, status_lama, status_baru, catatan, tanggal_update) 
                VALUES ('$id_laporan', 'ditindaklanjuti', 'selesai', 'Surat Keputusan Sanksi No $nomor_sk resmi diterbitkan lewat dokumen berkas.', NOW())
            ");

            if ($update_laporan && $insert_log) {
                $pesan_sukses = "🎉 Sukses! Surat Keputusan Resmi berhasil dipos dan dikirim ke Dashboard Investigasi.";
            } else {
                throw new Exception("Gagal memperbarui database.");
            }
        } catch (Exception $e) {
            $pesan_error = "Terjadi kendala sistem: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIRAKELIKA - Surat Keputusan Sanksi</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; margin: 0; display: flex; min-height: 100vh; }
        .form-sk-box { background: white; padding: 28px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); max-width: 700px; margin: 20px auto; width: 100%; }
        .form-group { margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 14px; font-weight: 600; color: #334155; }
        .form-control { padding: 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 14px; background-color: #fff; }
        .btn-submit { background-color: #10b981; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 14px; }
        .btn-submit:hover { background-color: #059669; }
        .alert { padding: 12px 16px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; }
        .alert-success { background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background-color: #fef2f2; color: #991b1b; border: 1px solid #fca5a5; }
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

        <div class="content-title" style="margin: 20px 0;">
            <h2>Penerbitan Surat Keputusan Sanksi</h2>
            <p>Pilih kasus berstatus ditindaklanjuti dan unggah berkas Surat Keputusan Rektorat resmi langsung ke sistem.</p>
        </div>

        <div class="form-sk-box">
            <?php if(!empty($pesan_sukses)) echo "<div class='alert alert-success'>$pesan_sukses</div>"; ?>
            <?php if(!empty($pesan_error)) echo "<div class='alert alert-danger'>$pesan_error</div>"; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                
                <div class="form-group">
                    <label>Pilih Berkas Laporan Kasus (Status: Ditindaklanjuti)</label>
                    <select name="id_laporan" class="form-control" required>
                        <?php
                        try {
                            $get_cases = mysqli_query($conn, "SELECT id_laporan, jenis_kekerasan FROM laporan WHERE status_laporan='ditindaklanjuti'");
                            if ($get_cases && mysqli_num_rows($get_cases) > 0) {
                                while ($k = mysqli_fetch_assoc($get_cases)) {
                                    $kategori_tampil = !empty($k['jenis_kekerasan']) ? $k['jenis_kekerasan'] : 'Umum';
                                    echo "<option value='".$k['id_laporan']."'>#KS-".$k['id_laporan']." - ".strtoupper($kategori_tampil)."</option>";
                                }
                            } else {
                                echo "<option value=''>-- Tidak ada aduan berstatus 'ditindaklanjuti' --</option>";
                            }
                        } catch (Exception $e) {
                            echo "<option value=''>Terjadi kesalahan sistem database</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nomor Surat Keputusan Resmi (SK Rektorat)</label>
                    <input type="text" name="nomor_sk" class="form-control" placeholder="Contoh: SK/REK/III/026/VI/2026" required>
                </div>

                <div class="form-group">
                    <label>Pilih File Dokumen SK Sanksi Resmi (.PDF / .DOCX / Hasil Scan)</label>
                    <input type="file" name="file_sk" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required style="padding: 8px 12px;">
                </div>

                <button type="submit" name="terbitkan_sk" class="btn-submit">✍️ Sahkan & Unggah Berkas SK</button>
            </form>
        </div>
    </main>

</body>
</html>

