<?php
// mahasiswa/buat-laporan.php
// Template starter — Fitur Pelaporan Kekerasan
// Branch: feature/pelaporan

session_start();
require_once '../config/database.php';

$pesan_sukses = '';
$pesan_error  = '';

// ── Proses form saat submit ──────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jenis_pelaporan  = $_POST['jenis_pelaporan']  ?? '';
    $judul            = trim($_POST['judul']        ?? '');
    $jenis_kekerasan  = $_POST['jenis_kekerasan']  ?? '';
    $waktu_kejadian   = $_POST['waktu_kejadian']   ?? '';
    $lokasi_kejadian  = trim($_POST['lokasi_kejadian'] ?? '');
    $deskripsi        = trim($_POST['deskripsi']   ?? '');

    // Validasi field wajib
    if (empty($judul) || empty($jenis_kekerasan) || empty($waktu_kejadian)
        || empty($lokasi_kejadian) || empty($deskripsi)) {
        $pesan_error = "Semua field wajib diisi.";
    } else {

        // Tentukan id_mahasiswa — NULL jika anonim
        $id_mahasiswa = ($jenis_pelaporan === 'KHUSUS' && isset($_SESSION['id_mahasiswa']))
            ? $_SESSION['id_mahasiswa']
            : null;

        // Generate kode unik untuk laporan anonim
        $kode_laporan = strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));

        // Simpan laporan ke database
        $sql = "INSERT INTO laporan
                    (id_mahasiswa, judul_laporan, deskripsi, jenis_kekerasan,
                     jenis_pelaporan, waktu_kejadian, lokasi_kejadian, kode_laporan)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isssssss",
            $id_mahasiswa, $judul, $deskripsi, $jenis_kekerasan,
            $jenis_pelaporan, $waktu_kejadian, $lokasi_kejadian, $kode_laporan
        );

        if ($stmt->execute()) {
            $id_laporan_baru = $conn->insert_id;

            // Upload bukti jika ada
            if (!empty($_FILES['bukti']['name'])) {
                $nama_asli  = $_FILES['bukti']['name'];
                $tipe_file  = $_FILES['bukti']['type'];
                $ukuran     = round($_FILES['bukti']['size'] / 1024); // KB
                $ekstensi   = pathinfo($nama_asli, PATHINFO_EXTENSION);
                $nama_simpan = 'bukti_' . $id_laporan_baru . '_' . time() . '.' . $ekstensi;
                $tujuan      = '../uploads/' . $nama_simpan;

                $ekstensi_boleh = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
                if (in_array(strtolower($ekstensi), $ekstensi_boleh)) {
                    if (move_uploaded_file($_FILES['bukti']['tmp_name'], $tujuan)) {
                        $sql_bukti = "INSERT INTO bukti
                                        (id_laporan, file_bukti, nama_asli, tipe_file, ukuran_file)
                                      VALUES (?, ?, ?, ?, ?)";
                        $stmt2 = $conn->prepare($sql_bukti);
                        $stmt2->bind_param("isssi",
                            $id_laporan_baru, $nama_simpan, $nama_asli, $tipe_file, $ukuran);
                        $stmt2->execute();
                    }
                }
            }

            $pesan_sukses = "Laporan berhasil dikirim! Kode laporan kamu: <strong>$kode_laporan</strong> — simpan kode ini untuk cek status.";
        } else {
            $pesan_error = "Gagal menyimpan laporan. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Laporan — SIRAKELIKA</title>
    <style>
        /* ── Reset & Base ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f9;
            color: #333;
        }

        /* ── Layout ── */
        .container {
            max-width: 700px;
            margin: 40px auto;
            padding: 0 16px 60px;
        }

        /* ── Header ── */
        .page-header {
            background: #c0392b;
            color: white;
            padding: 24px 32px;
            border-radius: 10px 10px 0 0;
        }
        .page-header h1 { font-size: 1.4rem; }
        .page-header p  { font-size: 0.9rem; margin-top: 4px; opacity: 0.85; }

        /* ── Card ── */
        .card {
            background: white;
            padding: 32px;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }

        /* ── Alert ── */
        .alert {
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 24px;
            font-size: 0.95rem;
        }
        .alert-success { background: #eafaf1; border-left: 4px solid #27ae60; color: #1e8449; }
        .alert-error   { background: #fdf0ef; border-left: 4px solid #c0392b; color: #922b21; }

        /* ── Toggle Pelaporan ── */
        .toggle-group {
            display: flex;
            gap: 12px;
            margin-bottom: 28px;
        }
        .toggle-btn {
            flex: 1;
            padding: 14px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        .toggle-btn.active {
            border-color: #c0392b;
            background: #fdf0ef;
            color: #c0392b;
            font-weight: 600;
        }
        .toggle-btn span { display: block; font-size: 0.8rem; margin-top: 4px; color: #777; }
        .toggle-btn.active span { color: #c0392b; }

        /* ── Form ── */
        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 6px;
            color: #444;
        }
        label .required { color: #c0392b; margin-left: 3px; }

        input[type="text"],
        input[type="datetime-local"],
        select,
        textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #ddd;
            border-radius: 6px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: border-color 0.2s;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #c0392b;
        }
        textarea { resize: vertical; min-height: 120px; }

        /* ── Upload ── */
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            color: #999;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .upload-area:hover { border-color: #c0392b; }
        .upload-area input { display: none; }

        /* ── Submit ── */
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: #c0392b;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.2s;
        }
        .btn-submit:hover { background: #a93226; }

        /* ── Info box ── */
        .info-box {
            background: #eaf4fb;
            border-left: 4px solid #2980b9;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 0.88rem;
            color: #1a5276;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="page-header">
        <h1>📋 Buat Laporan Kekerasan</h1>
        <p>Semua laporan akan ditangani secara rahasia dan profesional</p>
    </div>

    <div class="card">

        <?php if ($pesan_sukses): ?>
            <div class="alert alert-success"><?= $pesan_sukses ?></div>
        <?php endif; ?>

        <?php if ($pesan_error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($pesan_error) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="formLaporan">

            <!-- Pilih Jenis Pelaporan -->
            <div class="form-group">
                <label>Jenis Pelaporan <span class="required">*</span></label>
                <div class="toggle-group">
                    <div class="toggle-btn active" onclick="pilihJenis('UMUM', this)">
                        🔒 Anonim (Umum)
                        <span>Identitas tidak ditampilkan</span>
                    </div>
                    <div class="toggle-btn" onclick="pilihJenis('KHUSUS', this)">
                        👤 Dengan Identitas (Khusus)
                        <span>Identitas tersimpan aman</span>
                    </div>
                </div>
                <input type="hidden" name="jenis_pelaporan" id="jenis_pelaporan" value="UMUM">
            </div>

            <div class="info-box" id="info-anonim">
                🔒 Mode <strong>Anonim</strong>: Identitas kamu tidak akan diketahui siapapun.
                Kamu akan mendapat <strong>kode laporan</strong> untuk memantau perkembangan kasus.
            </div>

            <!-- Judul -->
            <div class="form-group">
                <label for="judul">Judul Laporan <span class="required">*</span></label>
                <input type="text" name="judul" id="judul"
                       placeholder="Contoh: Intimidasi oleh senior di Gedung A"
                       maxlength="255" required>
            </div>

            <!-- Jenis Kekerasan -->
            <div class="form-group">
                <label for="jenis_kekerasan">Jenis Kekerasan <span class="required">*</span></label>
                <select name="jenis_kekerasan" id="jenis_kekerasan" required>
                    <option value="">-- Pilih jenis kekerasan --</option>
                    <option value="fisik">Kekerasan Fisik</option>
                    <option value="verbal">Kekerasan Verbal</option>
                    <option value="seksual">Kekerasan Seksual</option>
                    <option value="psikologis">Kekerasan Psikologis</option>
                    <option value="perundungan">Perundungan (Bullying)</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>

            <!-- Waktu Kejadian -->
            <div class="form-group">
                <label for="waktu_kejadian">Waktu Kejadian <span class="required">*</span></label>
                <input type="datetime-local" name="waktu_kejadian" id="waktu_kejadian" required>
            </div>

            <!-- Lokasi -->
            <div class="form-group">
                <label for="lokasi_kejadian">Lokasi Kejadian <span class="required">*</span></label>
                <input type="text" name="lokasi_kejadian" id="lokasi_kejadian"
                       placeholder="Contoh: Gedung B lantai 2, Kantin Kampus"
                       maxlength="255" required>
            </div>

            <!-- Deskripsi -->
            <div class="form-group">
                <label for="deskripsi">Kronologi Kejadian <span class="required">*</span></label>
                <textarea name="deskripsi" id="deskripsi"
                          placeholder="Ceritakan kejadian secara detail — apa yang terjadi, siapa yang terlibat, dan dampaknya..."
                          required></textarea>
            </div>

            <!-- Upload Bukti -->
            <div class="form-group">
                <label>Upload Bukti <em style="font-weight:400;color:#999">(opsional)</em></label>
                <div class="upload-area" onclick="document.getElementById('bukti').click()">
                    <input type="file" name="bukti" id="bukti"
                           accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                           onchange="tampilkanNamaFile(this)">
                    <div id="upload-label">
                        📎 Klik untuk pilih file<br>
                        <span style="font-size:0.8rem">JPG, PNG, PDF, DOC — maks 5MB</span>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">📤 Kirim Laporan</button>

        </form>
    </div>
</div>

<script>
function pilihJenis(jenis, el) {
    // Update input hidden
    document.getElementById('jenis_pelaporan').value = jenis;

    // Update tampilan tombol
    document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');

    // Update info box
    const infoBox = document.getElementById('info-anonim');
    if (jenis === 'KHUSUS') {
        infoBox.style.background = '#eafaf1';
        infoBox.style.borderColor = '#27ae60';
        infoBox.style.color = '#1e8449';
        infoBox.innerHTML = '👤 Mode <strong>Khusus</strong>: Identitasmu tersimpan aman dan hanya bisa diakses pihak berwenang.';
    } else {
        infoBox.style.background = '#eaf4fb';
        infoBox.style.borderColor = '#2980b9';
        infoBox.style.color = '#1a5276';
        infoBox.innerHTML = '🔒 Mode <strong>Anonim</strong>: Identitas kamu tidak akan diketahui siapapun. Kamu akan mendapat <strong>kode laporan</strong> untuk memantau perkembangan kasus.';
    }
}

function tampilkanNamaFile(input) {
    const label = document.getElementById('upload-label');
    if (input.files && input.files[0]) {
        label.innerHTML = '✅ ' + input.files[0].name;
    }
}
</script>

</body>
</html>
