<?php
session_start();
include '../config/conn.php';

// Hanya mahasiswa yang login boleh mengakses halaman ini
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

// Generate kode laporan unik
function generateKode($conn) {
    $prefix = 'KS-';
    $year   = date('Y');
    do {
        $rand = strtoupper(substr(md5(uniqid()), 0, 6));
        $kode = $prefix . $year . '-' . $rand;
        $cek  = $conn->query("SELECT id_laporan FROM laporan WHERE kode_laporan='$kode' LIMIT 1");
    } while ($cek && $cek->num_rows > 0);
    return $kode;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis_pelaporan = $_POST['jenis_pelaporan'] ?? '';
    $judul_laporan   = trim($_POST['judul_laporan'] ?? '');
    $deskripsi       = trim($_POST['deskripsi'] ?? '');
    $jenis_kekerasan = $_POST['jenis_kekerasan'] ?? '';
    $waktu_kejadian  = $_POST['waktu_kejadian'] ?? '';
    $lokasi_kejadian = trim($_POST['lokasi_kejadian'] ?? '');
    $id_user    = (int) $_SESSION['id_user'];

    if (!$judul_laporan || !$deskripsi || !$jenis_kekerasan || !$waktu_kejadian || !$lokasi_kejadian) {
        $error = 'Semua field wajib diisi.';
    } else {
        // Handle upload bukti
        $bukti_nama = null;
        if (!empty($_FILES['bukti']['name'])) {
            $upload_dir = 'uploads/bukti/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $ext_allowed = ['jpg','jpeg','png','pdf','mp4','mov','avi','mp3','wav','m4a','ogg','aac'];
            $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $ext_allowed)) {
                $error = 'Format file tidak didukung. Gunakan JPG, PNG, PDF, video, atau audio (MP3/WAV/M4A).';
            } elseif ($_FILES['bukti']['size'] > 20 * 1024 * 1024) {
                $error = 'Ukuran file maksimal 20MB.';
            } else {
                $bukti_nama = uniqid('bukti_') . '.' . $ext;
                move_uploaded_file($_FILES['bukti']['tmp_name'], $upload_dir . $bukti_nama);
            }
        }

        if (!$error) {
            $kode      = generateKode($conn);
            $jp        = $conn->real_escape_string($jenis_pelaporan);
            $jl        = $conn->real_escape_string($judul_laporan);
            $ds        = $conn->real_escape_string($deskripsi);
            $jk        = $conn->real_escape_string($jenis_kekerasan);
            $wk        = $conn->real_escape_string($waktu_kejadian);
            $lk        = $conn->real_escape_string($lokasi_kejadian);
            $bn        = $bukti_nama ? $conn->real_escape_string($bukti_nama) : null;
            $bn_sql    = $bn ? "'$bn'" : 'NULL';

            $sql = "INSERT INTO laporan 
                    (id_user, kode_laporan, judul_laporan, deskripsi, jenis_kekerasan, jenis_pelaporan, waktu_kejadian, lokasi_kejadian, status_laporan)
                    VALUES ($id_user, '$kode', '$jl', '$ds', '$jk', '$jp', '$wk', '$lk', 'menunggu')";

            if ($conn->query($sql)) {
                header("Location: laporan.php?success=Laporan+berhasil+dikirim+dengan+kode+$kode");
                exit;
            } else {
                $error = 'Gagal menyimpan laporan: ' . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan – SIRAKELIKA</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* ===== MULTI-STEP MODAL ===== */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.5);
            z-index: 999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-box {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 24px 64px rgba(0,0,0,0.18);
            animation: modalIn 0.25s ease;
        }

        @keyframes modalIn {
            from { opacity:0; transform: translateY(16px) scale(0.98); }
            to   { opacity:1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px 16px;
            border-bottom: 1px solid #f1f5f9;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 1;
            border-radius: 16px 16px 0 0;
        }

        .modal-header h2 {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
        }

        .modal-close {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .modal-close:hover { background: #e2e8f0; }

        /* STEP INDICATOR */
        .step-indicator {
            display: flex;
            align-items: center;
            padding: 16px 24px;
            gap: 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .step-num {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            background: #e2e8f0;
            color: #94a3b8;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .step-item.active .step-num  { background: #2563eb; color: #fff; }
        .step-item.done .step-num    { background: #10b981; color: #fff; }

        .step-text {
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            transition: color 0.3s;
        }

        .step-item.active .step-text { color: #2563eb; }
        .step-item.done .step-text   { color: #10b981; }

        .step-line {
            flex: 1;
            height: 2px;
            background: #e2e8f0;
            margin: 0 8px;
            transition: background 0.3s;
        }

        .step-line.done { background: #10b981; }

        /* MODAL BODY */
        .modal-body { padding: 24px; }

        /* PILIH JENIS */
        .jenis-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 20px;
        }

        .jenis-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
        }

        .jenis-card:hover { border-color: #93c5fd; background: #f8fafc; }

        .jenis-card.selected {
            border-color: #2563eb;
            background: #eff6ff;
        }

        .jenis-card .jenis-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
        }

        .jenis-card.umum .jenis-icon  { background: #eff6ff; color: #2563eb; }
        .jenis-card.khusus .jenis-icon { background: #faf5ff; color: #7c3aed; }

        .jenis-card h3 { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 6px; }
        .jenis-card p  { font-size: 12px; color: #64748b; line-height: 1.5; }

        .jenis-note {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 8px;
            padding: 12px 14px;
            font-size: 12px;
            color: #92400e;
            line-height: 1.5;
            margin-bottom: 4px;
        }

        /* FORM FIELDS */
        .form-group { margin-bottom: 16px; }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-label .req { color: #ef4444; margin-left: 2px; }

        .form-control {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
            color: #1e293b;
            background: #fff;
            outline: none;
            transition: border 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }

        textarea.form-control { resize: vertical; min-height: 90px; }

        select.form-control { cursor: pointer; }

        /* UPLOAD AREA */
        .upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 32px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            background: #f8fafc;
            position: relative;
        }

        .upload-area:hover, .upload-area.drag-over {
            border-color: #3b82f6;
            background: #eff6ff;
        }

        .upload-area input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .upload-icon {
            width: 48px;
            height: 48px;
            background: #e0f2fe;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: #0284c7;
        }

        .upload-area h4 { font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
        .upload-area p  { font-size: 12px; color: #64748b; }

        .file-types {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-top: 12px;
        }

        .file-tag {
            background: #f1f5f9;
            color: #475569;
            font-size: 10px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 4px;
        }

        .file-preview {
            display: none;
            align-items: center;
            gap: 10px;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-top: 10px;
            font-size: 13px;
            color: #166534;
            font-weight: 500;
        }

        .file-preview.show { display: flex; }

        .file-remove {
            margin-left: auto;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
        }

        /* ALERT */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        /* FOOTER */
        .modal-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
            text-decoration: none;
            font-family: 'Inter', sans-serif;
        }

        .btn-primary { background: #2563eb; color: #fff; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-primary:disabled { background: #93c5fd; cursor: not-allowed; }

        .btn-outline { background: #fff; color: #475569; border: 1px solid #e2e8f0; }
        .btn-outline:hover { background: #f8fafc; }

        .btn-ghost { background: transparent; color: #64748b; padding: 9px 12px; }
        .btn-ghost:hover { background: #f1f5f9; }

        /* KHUSUS: identitas pelapor */
        .identitas-section {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
        }

        .identitas-section h4 {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        /* STEP PANELS */
        .step-panel { display: none; }
        .step-panel.active { display: block; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
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
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="laporan.php" class="nav-link active">Laporan Saya</a>
        <div class="nav-group">PENGELOLAAN</div>
        <a href="manajemen.php" class="nav-link">Manajemen Kasus</a>
        <a href="edukasi.php" class="nav-link">Edukasi &amp; Informasi</a>
        <a href="kenali.php" class="nav-link">Kenali Situasi Anda</a>
        <div class="nav-group">AKUN</div>
        <a href="profil.php" class="nav-link">Profil</a>
        <a href="pengaturan.php" class="nav-link">Pengaturan</a>
        <a href="logout.php" class="nav-link logout" onclick="return confirm('Yakin keluar?')">Keluar</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="main-content">
    <header class="topbar">
        <div></div>
        <div class="user-profile">
            <div class="notif-btn">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
            </div>
            <div class="avatar">MA</div>
            <div class="user-info">
                <span class="user-name">M. Alif</span>
                <span class="user-role">Mahasiswa</span>
            </div>
        </div>
    </header>

    <!-- Konten latar (dashboard di belakang modal) -->
    <div style="filter: blur(2px); pointer-events:none; opacity:0.4;">
        <section class="welcome-banner">
            <div class="banner-text">
                <h2>Selamat Datang di SIRAKELIKA</h2>
                <p>Sistem Pelaporan Kekerasan di Lingkungan Kampus.</p>
            </div>
        </section>
        <div class="content-title"><h2>Dashboard</h2><p>Ringkasan aktivitas laporan</p></div>
        <section class="stats-grid">
            <div class="card card-total"><span class="card-num">0</span><span class="card-title">Total Laporan</span></div>
            <div class="card card-new"><span class="card-num">0</span><span class="card-title">Laporan Baru</span></div>
            <div class="card card-process"><span class="card-num">0</span><span class="card-title">Dalam Proses</span></div>
            <div class="card card-done"><span class="card-num">0</span><span class="card-title">Selesai</span></div>
        </section>
    </div>
</main>

<!-- ===================== MODAL MULTI-STEP ===================== -->
<div class="modal-backdrop" id="modalBackdrop">
    <div class="modal-box">

        <div class="modal-header">
            <h2 id="modalTitle">Buat Laporan Baru</h2>
            <button class="modal-close" onclick="tutupModal()" title="Tutup">×</button>
        </div>

        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="step-item active" id="si1">
                <div class="step-num">1</div>
                <span class="step-text">Jenis Laporan</span>
            </div>
            <div class="step-line" id="sl1"></div>
            <div class="step-item" id="si2">
                <div class="step-num">2</div>
                <span class="step-text">Detail Kejadian</span>
            </div>
            <div class="step-line" id="sl2"></div>
            <div class="step-item" id="si3">
                <div class="step-num">3</div>
                <span class="step-text">Upload Bukti</span>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" id="formLaporan">

            <?php if ($error): ?>
            <div class="modal-body">
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            </div>
            <?php endif; ?>

            <!-- ===== STEP 1: PILIH JENIS ===== -->
            <div class="step-panel active" id="step1">
                <div class="modal-body">
                    <p style="font-size:13px;color:#64748b;margin-bottom:16px">Pilih jenis pelaporan yang sesuai dengan situasimu.</p>

                    <div class="jenis-grid">
                        <div class="jenis-card umum" id="cardUmum" onclick="pilihJenis('UMUM')">
                            <div class="jenis-icon">
                                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                            </div>
                            <h3>Umum</h3>
                            <p>Laporkan secara anonim. Identitasmu tidak akan diketahui oleh siapapun, termasuk pihak kampus.</p>
                        </div>
                        <div class="jenis-card khusus" id="cardKhusus" onclick="pilihJenis('KHUSUS')">
                            <div class="jenis-icon">
                                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <h3>Khusus</h3>
                            <p>Laporkan dengan mencantumkan identitasmu. Identitas dapat diketahui pihak kampus untuk keperluan penanganan kasus.</p>
                        </div>
                    </div>

                    <div class="jenis-note" id="jenisNote" style="display:none">
                        <strong id="jenisNoteTitle"></strong><br>
                        <span id="jenisNoteDesc"></span>
                    </div>

                    <input type="hidden" name="jenis_pelaporan" id="jenis_pelaporan" value="">
                </div>
                <div class="modal-footer">
                    <a href="laporan.php" class="btn btn-outline">Batal</a>
                    <button type="button" class="btn btn-primary" id="btnStep1" onclick="goStep(2)" disabled>
                        Selanjutnya
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9,18 15,12 9,6"/></svg>
                    </button>
                </div>
            </div>

            <!-- ===== STEP 2: DETAIL KEJADIAN ===== -->
            <div class="step-panel" id="step2">
                <div class="modal-body">

                    <!-- Identitas Pelapor (Khusus saja - identitas dicantumkan) -->
                    <div class="identitas-section" id="identitasSection" style="display:none">
                        <h4>Identitas Pelapor (Wajib untuk Laporan Khusus)</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                                <input type="text" name="nama_pelapor" class="form-control" placeholder="Nama lengkap kamu">
                            </div>
                            <div class="form-group">
                                <label class="form-label">NIM <span class="req">*</span></label>
                                <input type="text" name="nim_pelapor" class="form-control" placeholder="NIM kamu">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Judul Laporan <span class="req">*</span></label>
                        <input type="text" name="judul_laporan" class="form-control" placeholder="Ringkasan singkat kejadian" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Jenis Kekerasan <span class="req">*</span></label>
                            <select name="jenis_kekerasan" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <option value="fisik">Fisik</option>
                                <option value="verbal">Verbal</option>
                                <option value="seksual">Seksual</option>
                                <option value="psikologis">Psikologis</option>
                                <option value="perundungan">Perundungan</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Waktu Kejadian <span class="req">*</span></label>
                            <input type="datetime-local" name="waktu_kejadian" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Lokasi Kejadian <span class="req">*</span></label>
                        <input type="text" name="lokasi_kejadian" class="form-control" placeholder="Contoh: Gedung A, Lantai 2, Kampus Utama" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Deskripsi Kejadian <span class="req">*</span></label>
                        <textarea name="deskripsi" class="form-control" placeholder="Ceritakan kronologi kejadian secara detail. Semakin lengkap, semakin mudah ditangani." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="goStep(1)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15,18 9,12 15,6"/></svg>
                        Kembali
                    </button>
                    <button type="button" class="btn btn-primary" onclick="goStep(3)">
                        Selanjutnya
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9,18 15,12 9,6"/></svg>
                    </button>
                </div>
            </div>

            <!-- ===== STEP 3: UPLOAD BUKTI ===== -->
            <div class="step-panel" id="step3">
                <div class="modal-body">
                    <p style="font-size:13px;color:#64748b;margin-bottom:16px">
                        Upload bukti pendukung laporan kamu. Bukti dapat berupa foto, video, rekaman suara, atau dokumen PDF.
                        <strong style="color:#0f172a"> (Opsional)</strong>
                    </p>

                    <div class="upload-area" id="uploadArea">
                        <input type="file" name="bukti" id="inputBukti" accept="image/*,video/*,audio/*,.pdf"
                               onchange="previewFile(this)">
                        <div class="upload-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                <polyline points="17,8 12,3 7,8"/>
                                <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
                        </div>
                        <h4>Klik atau seret file ke sini</h4>
                        <p>Ukuran maksimal 20MB</p>
                        <div class="file-types">
                            <span class="file-tag">JPG</span>
                            <span class="file-tag">PNG</span>
                            <span class="file-tag">PDF</span>
                            <span class="file-tag">MP4</span>
                            <span class="file-tag">MOV</span>
                            <span class="file-tag">MP3</span>
                            <span class="file-tag">WAV</span>
                            <span class="file-tag">M4A</span>
                        </div>
                    </div>

                    <div class="file-preview" id="filePreview">
                        <svg id="filePreviewIcon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                        </svg>
                        <span id="fileName">—</span>
                        <span id="fileSize" style="color:#94a3b8;font-size:12px"></span>
                        <button type="button" class="file-remove" onclick="hapusFile()" title="Hapus file">×</button>
                    </div>

                    <audio id="audioPreview" controls style="display:none;width:100%;margin-top:10px;height:36px"></audio>

                    <div style="margin-top:20px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px 14px;font-size:12px;color:#7f1d1d;line-height:1.5">
                        <strong>⚠ Privasi:</strong> Bukti yang kamu upload hanya dapat diakses oleh tim penanganan yang berwenang dan disimpan secara aman.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="goStep(2)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15,18 9,12 15,6"/></svg>
                        Kembali
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20,6 9,17 4,12"/></svg>
                        Kirim Laporan
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
let currentStep = 1;
let jenisSelected = '';

function pilihJenis(jenis) {
    jenisSelected = jenis;
    document.getElementById('jenis_pelaporan').value = jenis;

    document.getElementById('cardUmum').classList.toggle('selected', jenis === 'UMUM');
    document.getElementById('cardKhusus').classList.toggle('selected', jenis === 'KHUSUS');
    document.getElementById('btnStep1').disabled = false;

    const note    = document.getElementById('jenisNote');
    const title   = document.getElementById('jenisNoteTitle');
    const desc    = document.getElementById('jenisNoteDesc');
    note.style.display = 'block';

    if (jenis === 'UMUM') {
        title.textContent = 'Laporan Umum (Anonim) dipilih.';
        desc.textContent  = 'Identitasmu sepenuhnya dirahasiakan. Pihak kampus tidak akan mengetahui siapa yang membuat laporan ini.';
    } else {
        title.textContent = 'Laporan Khusus dipilih.';
        desc.textContent  = 'Identitasmu akan tercantum dalam laporan dan dapat diketahui oleh pihak kampus yang berwenang untuk keperluan penanganan kasus.';
    }
}

function goStep(n) {
    // Validasi step 2
    if (n === 3) {
        const required = ['judul_laporan','jenis_kekerasan','waktu_kejadian','lokasi_kejadian','deskripsi'];
        for (const f of required) {
            const el = document.querySelector(`[name="${f}"]`);
            if (!el || !el.value.trim()) {
                el && el.focus();
                el && (el.style.borderColor = '#ef4444');
                setTimeout(() => el && (el.style.borderColor = ''), 2000);
                return;
            }
        }
    }

    document.getElementById('step' + currentStep).classList.remove('active');

    // Step indicator
    const si = document.getElementById('si' + currentStep);
    si.classList.remove('active');
    si.classList.add('done');
    si.querySelector('.step-num').innerHTML =
        '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20,6 9,17 4,12"/></svg>';

    if (currentStep < 3) {
        document.getElementById('sl' + currentStep).classList.add('done');
    }

    currentStep = n;
    document.getElementById('step' + currentStep).classList.add('active');
    document.getElementById('si' + currentStep).classList.add('active');

    // Update title
    const titles = ['', 'Buat Laporan Baru', 'Detail Kejadian', 'Upload Bukti'];
    document.getElementById('modalTitle').textContent = titles[currentStep];

    // Tampilkan identitas jika KHUSUS (identitas dicantumkan)
    if (currentStep === 2) {
        document.getElementById('identitasSection').style.display =
            jenisSelected === 'KHUSUS' ? 'block' : 'none';
    }
}

function tutupModal() {
    if (confirm('Batalkan laporan ini? Data yang sudah diisi akan hilang.')) {
        window.location.href = 'laporan.php';
    }
}

function previewFile(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const size = (file.size / 1024 / 1024).toFixed(1) + ' MB';
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = size;
        document.getElementById('filePreview').classList.add('show');
        document.getElementById('uploadArea').style.borderColor = '#10b981';
        document.getElementById('uploadArea').style.background  = '#f0fdf4';

        const icon = document.getElementById('filePreviewIcon');
        const audioPreview = document.getElementById('audioPreview');
        const isAudio = file.type.startsWith('audio/') || /\.(mp3|wav|m4a|ogg|aac)$/i.test(file.name);

        if (isAudio) {
            // Ikon mikrofon
            icon.innerHTML = '<path d="M12 1a3 3 0 00-3 3v8a3 3 0 006 0V4a3 3 0 00-3-3z"/><path d="M19 10v2a7 7 0 01-14 0v-2"/><line x1="12" y1="19" x2="12" y2="23"/><line x1="8" y1="23" x2="16" y2="23"/>';
            audioPreview.src = URL.createObjectURL(file);
            audioPreview.style.display = 'block';
        } else {
            // Ikon dokumen (default)
            icon.innerHTML = '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/>';
            audioPreview.style.display = 'none';
            audioPreview.removeAttribute('src');
        }
    }
}

function hapusFile() {
    document.getElementById('inputBukti').value = '';
    document.getElementById('filePreview').classList.remove('show');
    document.getElementById('uploadArea').style.borderColor = '';
    document.getElementById('uploadArea').style.background  = '';

    const audioPreview = document.getElementById('audioPreview');
    audioPreview.pause();
    audioPreview.style.display = 'none';
    audioPreview.removeAttribute('src');
}

// Drag & drop
const ua = document.getElementById('uploadArea');
ua.addEventListener('dragover',  e => { e.preventDefault(); ua.classList.add('drag-over'); });
ua.addEventListener('dragleave', () => ua.classList.remove('drag-over'));
ua.addEventListener('drop', e => {
    e.preventDefault();
    ua.classList.remove('drag-over');
    const dt = e.dataTransfer;
    if (dt.files.length) {
        document.getElementById('inputBukti').files = dt.files;
        previewFile(document.getElementById('inputBukti'));
    }
});

// Submit loading state
document.getElementById('formLaporan').addEventListener('submit', () => {
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4"/></svg> Mengirim...';
});
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
</body>
</html>
