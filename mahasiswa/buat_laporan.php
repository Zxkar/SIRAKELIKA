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

            $ext_allowed = ['jpg','jpeg','png','pdf','mp4','mov','avi'];
            $ext = strtolower(pathinfo($_FILES['bukti']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $ext_allowed)) {
                $error = 'Format file tidak didukung. Gunakan JPG, PNG, PDF, atau video.';
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
                header("Location: laporan_saya.php?success=Laporan+berhasil+dikirim+dengan+kode+$kode");
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
    <link rel="stylesheet" href="buat_laporan.css">
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
        <a href="laporan_saya.php" class="nav-link active">Laporan Saya</a>
        <div class="nav-group">PENGELOLAAN</div>
     
        <a href="edukasi1.php" class="nav-link">Edukasi &amp; Informasi</a>
        <a href="kenali.php" class="nav-link">Kenali Situasi Anda</a>
        <div class="nav-group">AKUN</div>
        <a href="profil.php" class="nav-link">Profil</a>
       
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
                                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                                </svg>
                            </div>
                            <h3>Umum</h3>
                            <p>Laporkan kejadian secara terbuka dengan identitas yang dapat diketahui pihak kampus.</p>
                        </div>
                        <div class="jenis-card khusus" id="cardKhusus" onclick="pilihJenis('KHUSUS')">
                            <div class="jenis-icon">
                                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                            </div>
                            <h3>Khusus</h3>
                            <p>Laporkan secara anonim atau rahasia. Identitasmu terlindungi sepenuhnya.</p>
                        </div>
                    </div>

                    <div class="jenis-note" id="jenisNote" style="display:none">
                        <strong id="jenisNoteTitle"></strong><br>
                        <span id="jenisNoteDesc"></span>
                    </div>

                    <input type="hidden" name="jenis_pelaporan" id="jenis_pelaporan" value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="tutupModal()">Batal</button>
                    <button type="button" class="btn btn-primary" id="btnStep1" onclick="goStep(2)" disabled>
                        Selanjutnya
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9,18 15,12 9,6"/></svg>
                    </button>
                </div>
            </div>

            <!-- ===== STEP 2: DETAIL KEJADIAN ===== -->
            <div class="step-panel" id="step2">
                <div class="modal-body">

                    <!-- Identitas Pelapor (Khusus saja) -->
                    <div class="identitas-section" id="identitasSection" style="display:none">
                        <h4>Identitas Pelapor (Opsional — akan dirahasiakan)</h4>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama_pelapor" class="form-control" placeholder="Nama atau inisial">
                            </div>
                            <div class="form-group">
                                <label class="form-label">NIM</label>
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
                        Upload bukti pendukung laporan kamu. Bukti dapat berupa foto, video, atau dokumen PDF.
                        <strong style="color:#0f172a"> (Opsional)</strong>
                    </p>

                    <div class="upload-area" id="uploadArea">
                        <input type="file" name="bukti" id="inputBukti" accept="image/*,video/*,.pdf"
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
                        </div>
                    </div>

                    <div class="file-preview" id="filePreview">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14,2 14,8 20,8"/>
                        </svg>
                        <span id="fileName">—</span>
                        <span id="fileSize" style="color:#94a3b8;font-size:12px"></span>
                        <button type="button" class="file-remove" onclick="hapusFile()" title="Hapus file">×</button>
                    </div>

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
        title.textContent = 'Laporan Umum dipilih.';
        desc.textContent  = 'Identitas kamu dapat diketahui oleh pihak kampus yang berwenang untuk keperluan penanganan kasus.';
    } else {
        title.textContent = 'Laporan Khusus dipilih.';
        desc.textContent  = 'Identitasmu sepenuhnya dirahasiakan. Kamu tetap bisa memantau status laporan lewat kode yang diberikan.';
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

    // Tampilkan identitas jika Khusus
    if (currentStep === 2) {
        document.getElementById('identitasSection').style.display =
            jenisSelected === 'KHUSUS' ? 'block' : 'none';
    }
}

function tutupModal() {
    history.back();
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
    }
}

function hapusFile() {
    document.getElementById('inputBukti').value = '';
    document.getElementById('filePreview').classList.remove('show');
    document.getElementById('uploadArea').style.borderColor = '';
    document.getElementById('uploadArea').style.background  = '';
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

</body>
</html>
