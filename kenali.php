<?php
session_start();
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$jawaban = isset($_GET['jawaban']) ? $_GET['jawaban'] : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenali Situasi Anda - SIRAKELIKA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { corePlugins: { preflight: false } }
    </script>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Lindungi sidebar dari override Tailwind */
        .sidebar {
            width: 260px !important;
            background-color: #ffffff !important;
            border-right: 1px solid #e2e8f0 !important;
            padding: 24px 16px !important;
            display: flex !important;
            flex-direction: column !important;
            position: fixed !important;
            height: 100vh !important;
        }
        .nav-link {
            display: flex !important;
            align-items: center !important;
            padding: 10px 12px !important;
            color: #64748b !important;
            text-decoration: none !important;
            outline: none !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            border-radius: 8px !important;
            margin: 0 !important;
        }
        .nav-link:hover {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }
        .nav-link.active {
            background-color: #e0f2fe !important;
            color: #0369a1 !important;
            font-weight: 600 !important;
        }
        .nav-link.logout { color: #ef4444 !important; }
        .nav-link.logout:hover { background-color: #fef2f2 !important; }
        .nav-group {
            font-size: 11px !important;
            font-weight: 600 !important;
            color: #94a3b8 !important;
            margin-top: 16px !important;
            margin-bottom: 8px !important;
            padding-left: 8px !important;
        }
        .nav-container {
            display: flex !important;
            flex-direction: column !important;
            gap: 4px !important;
        }
        .logo-area {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            margin-bottom: 32px !important;
            padding-left: 8px !important;
        }
        .main-content {
            margin-left: 260px !important;
        }
        .option-card {
            transition: all 0.2s ease;
            border: 1.5px solid #e2e8f0;
            text-decoration: none !important;
            color: inherit !important;
        }
        .option-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(37,99,235,0.10);
        }
        .option-card.red:hover   { border-color: #f87171; background: #fff5f5; }
        .option-card.amber:hover { border-color: #fbbf24; background: #fffbeb; }
        .option-card.purple:hover{ border-color: #a78bfa; background: #f5f3ff; }
        .option-card * { text-decoration: none !important; }

        .progress-bar-fill {
            transition: width 0.4s ease;
        }

        .step-card {
            animation: fadeUp 0.35s ease;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .result-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body class="bg-[#F8FAFC] font-sans">

<div class="flex min-h-screen">

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
            <a href="dashboard.php" class="nav-link"><span class="nav-text">Dashboard</span></a>
            <a href="laporan.php" class="nav-link"><span class="nav-text">Laporan Saya</span></a>
            <div class="nav-group">PENGELOLAAN</div>
          
            <a href="edukasi1.php" class="nav-link"><span class="nav-text">Edukasi & Informasi</span></a>
            <a href="kenali.php" class="nav-link active"><span class="nav-text">Kenali Situasi Anda</span></a>
            <div class="nav-group">AKUN</div>
            <a href="#" class="nav-link"><span class="nav-text">Profil</span></a>
            <a href="#" class="nav-link"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Keluar</span></a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content">

        <!-- TOPBAR -->
        <header class="topbar">
            <div></div>
            <div class="user-profile">
                <div class="notif-btn">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <div class="avatar">MA</div>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($_SESSION['nama'] ?? 'M. Alif') ?></span>
                    <span class="user-role">Mahasiswa</span>
                </div>
            </div>
        </header>

        <!-- WELCOME BANNER -->
        <section class="welcome-banner mb-8">
            <div class="banner-text">
                <h2>🛡️ Kenali Situasi Anda</h2>
                <p>Sistem identifikasi awal untuk membantu mengenali jenis pelanggaran di lingkungan kampus sebelum melakukan pelaporan resmi.</p>
            </div>
        </section>

        <!-- PROGRESS STEPS -->
        <?php
        $progress = $step === 1 ? 0 : ($step === 2 ? 50 : 100);
        $step_labels = ['Mulai', 'Klasifikasi', 'Hasil'];
        ?>
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5 mb-6">
            <div class="flex items-center justify-between mb-3">
                <?php for ($i = 1; $i <= 3; $i++): ?>
                    <div class="flex items-center <?= $i < 3 ? 'flex-1' : '' ?>">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                <?= $step >= $i ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400' ?>">
                                <?= $step > $i ? '✓' : $i ?>
                            </div>
                            <span class="text-[10px] mt-1 font-semibold <?= $step >= $i ? 'text-blue-600' : 'text-gray-400' ?>">
                                <?= $step_labels[$i-1] ?>
                            </span>
                        </div>
                        <?php if ($i < 3): ?>
                            <div class="flex-1 h-0.5 mx-2 mt-[-14px] <?= $step > $i ? 'bg-blue-600' : 'bg-gray-100' ?>"></div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="progress-bar-fill bg-blue-600 h-1.5 rounded-full" style="width: <?= $progress ?>%"></div>
            </div>
        </div>

        <!-- CARD KONTEN -->
        <div class="step-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8">

            <?php if ($step === 1): ?>
            <!-- ======== STEP 1: INTRO ======== -->
            <div class="p-8 text-center">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-5">
                    <i class="fa fa-user-shield text-blue-600 text-4xl"></i>
                </div>
                <h2 class="text-lg font-bold text-gray-800 mb-3">Jangan Diam, Lindungi Diri & Rekan Kampus</h2>
                <p class="text-sm text-gray-500 max-w-lg mx-auto mb-8 leading-relaxed">
                    Melalui sistem SIRAKELIKA, Anda dapat mengenali apakah tindakan yang Anda alami termasuk kategori
                    <strong class="text-red-500">Kekerasan Seksual (PPKS)</strong>,
                    <strong class="text-amber-500">Perundungan (Bullying)</strong>, atau
                    <strong class="text-purple-500">Intimidasi Psikis</strong>.
                </p>

                <!-- Kartu preview 3 kategori -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 text-left">
                    <div class="bg-red-50 border border-red-100 rounded-xl p-4">
                        <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center mb-3">
                            <i class="fa fa-person-circle-exclamation text-red-500"></i>
                        </div>
                        <h4 class="text-xs font-bold text-red-700 mb-1">Kekerasan Seksual</h4>
                        <p class="text-[11px] text-red-400 leading-relaxed">Catcalling, sentuhan fisik tanpa izin, atau konten asusila oleh civitas kampus.</p>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                        <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center mb-3">
                            <i class="fa fa-users-viewfinder text-amber-500"></i>
                        </div>
                        <h4 class="text-xs font-bold text-amber-700 mb-1">Perundungan / Bullying</h4>
                        <p class="text-[11px] text-amber-400 leading-relaxed">Ancaman fisik, pengucilan paksa, atau permaluan oleh mahasiswa maupun dosen.</p>
                    </div>
                    <div class="bg-purple-50 border border-purple-100 rounded-xl p-4">
                        <div class="w-9 h-9 bg-purple-100 rounded-lg flex items-center justify-center mb-3">
                            <i class="fa fa-gavel text-purple-500"></i>
                        </div>
                        <h4 class="text-xs font-bold text-purple-700 mb-1">Intimidasi / Penyalahgunaan Kuasa</h4>
                        <p class="text-[11px] text-purple-400 leading-relaxed">Ancaman nilai, pemerasan finansial, atau sanksi akademis sepihak oleh oknum.</p>
                    </div>
                </div>

                <a href="kenali.php?step=2"
                   class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-sm transition">
                    <i class="fa fa-arrow-right"></i> Mulai Identifikasi
                </a>
            </div>

            <?php elseif ($step === 2): ?>
            <!-- ======== STEP 2: PILIH KATEGORI ======== -->
            <div class="p-6 md:p-8">
                <div class="mb-6">
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-wider">Langkah 1 dari 1 — Klasifikasi Kejadian</span>
                    <h2 class="text-base font-bold text-gray-800 mt-3 mb-1">Pilih kondisi yang paling mendekati situasi Anda:</h2>
                    <p class="text-xs text-gray-400">Pilih satu kategori yang paling sesuai. Jawaban Anda bersifat rahasia.</p>
                </div>

                <div class="space-y-4">

                    <!-- Opsi PPKS -->
                    <a href="kenali.php?step=3&jawaban=ppks" class="option-card red group flex items-start gap-4 p-5 rounded-2xl block">
                        <div class="w-11 h-11 bg-red-50 text-red-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition">
                            <i class="fa fa-person-circle-exclamation text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-800 group-hover:text-red-600 transition">Kekerasan Seksual / Catcalling / Isyarat Asusila</span>
                                <i class="fa fa-chevron-right text-xs text-gray-300 group-hover:text-red-400 transition"></i>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 leading-relaxed">Mendapat komentar bernada seksual, sentuhan fisik sepihak, atau penyebaran konten intim non-konsensual oleh civitas kampus.</p>
                        </div>
                    </a>

                    <!-- Opsi Bullying -->
                    <a href="kenali.php?step=3&jawaban=bullying" class="option-card amber group flex items-start gap-4 p-5 rounded-2xl block">
                        <div class="w-11 h-11 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 transition">
                            <i class="fa fa-users-viewfinder text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-800 group-hover:text-amber-600 transition">Perundungan Senioritas / Bullying / Kekerasan Fisik</span>
                                <i class="fa fa-chevron-right text-xs text-gray-300 group-hover:text-amber-400 transition"></i>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 leading-relaxed">Diancam secara fisik, dikucilkan dalam kelompok belajar secara paksa, atau dipermalukan oleh oknum mahasiswa/dosen.</p>
                        </div>
                    </a>

                    <!-- Opsi Intimidasi -->
                    <a href="kenali.php?step=3&jawaban=intimidasi" class="option-card purple group flex items-start gap-4 p-5 rounded-2xl block">
                        <div class="w-11 h-11 bg-purple-50 text-purple-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition">
                            <i class="fa fa-gavel text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-800 group-hover:text-purple-600 transition">Penyalahgunaan Wewenang / Intimidasi Nilai</span>
                                <i class="fa fa-chevron-right text-xs text-gray-300 group-hover:text-purple-400 transition"></i>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 leading-relaxed">Diancam tidak diluluskan mata kuliah, pemerasan finansial berkedok tugas, atau ancaman sanksi sepihak.</p>
                        </div>
                    </a>

                </div>

                <div class="mt-6 pt-4 border-t border-gray-50">
                    <a href="kenali.php?step=1" class="inline-flex items-center gap-2 text-xs text-gray-400 hover:text-gray-600 font-medium transition">
                        <i class="fa fa-arrow-left text-[10px]"></i> Kembali ke awal
                    </a>
                </div>
            </div>

            <?php elseif ($step === 3): ?>
            <!-- ======== STEP 3: HASIL ======== -->
            <?php
            if ($jawaban === 'ppks') {
                $kategori     = "Satgas PPKS Kampus";
                $judul        = "Kekerasan Seksual (Melanggar Permendikbudristek No. 30/2021)";
                $img          = "https://picsum.photos/id/119/600/350";
                $badge_class  = "bg-red-50 text-red-600";
                $icon         = "fa-person-circle-exclamation";
                $icon_color   = "text-red-500";
                $icon_bg      = "bg-red-50";
                $btn_color    = "bg-red-600 hover:bg-red-700";
                $border_color = "border-red-100";
                $prosedur     = "Kasus ini ditangani secara khusus dan rahasia oleh Satgas Pencegahan dan Penanganan Kekerasan Seksual (PPKS). Hak identitas Anda dijamin aman sepenuhnya.";
                $contoh_tindakan = [
                    "<strong>Kekerasan Verbal/Catcalling:</strong> Lelucon seksis, siulan menggoda, komentar tidak senonoh atas bentuk tubuh atau pakaian di area kampus.",
                    "<strong>Kekerasan Non-Fisik:</strong> Menatap dengan nuansa seksual, memperlihatkan konten pornografi sepihak, atau mengirim pesan asusila melalui WhatsApp/Media Sosial.",
                    "<strong>Kekerasan Fisik:</strong> Menyentuh, meraba, memeluk, mencium, atau memegang bagian tubuh tanpa persetujuan (konsen).",
                    "<strong>Penyalahgunaan Relasi Kuasa:</strong> Pemaksaan tindakan intim oleh oknum berkedok bimbingan skripsi, tugas, atau nilai akademis."
                ];
            } elseif ($jawaban === 'bullying') {
                $kategori     = "Komisi Disiplin & Konseling";
                $judul        = "Perundungan (Bullying) & Kekerasan Fisik Akademis";
                $img          = "https://picsum.photos/id/64/600/350";
                $badge_class  = "bg-amber-50 text-amber-600";
                $icon         = "fa-users-viewfinder";
                $icon_color   = "text-amber-500";
                $icon_bg      = "bg-amber-50";
                $btn_color    = "bg-amber-500 hover:bg-amber-600";
                $border_color = "border-amber-100";
                $prosedur     = "Tindakan pelanggaran tata tertib kemahasiswaan. Pelaporan Anda akan diteruskan ke Wakil Dekan Bidang Kemahasiswaan dan Unit Konseling untuk perlindungan psikis.";
                $contoh_tindakan = [
                    "<strong>Kekerasan Fisik Nyata:</strong> Pemukulan, dorongan sengaja, pelemparan benda, atau pemaksaan aktivitas fisik di luar batas normal (misal saat ospek/kegiatan ormawa).",
                    "<strong>Perundungan Sosial (Pengucilan):</strong> Ajakan massal untuk memboikot atau mengucilkan seorang mahasiswa dari pergaulan kelas atau kelompok belajar.",
                    "<strong>Pelecehan Verbal:</strong> Penghinaan ras, suku, kondisi fisik (body shaming), atau status ekonomi yang diucapkan secara sengaja untuk menjatuhkan mental."
                ];
            } else {
                $kategori     = "Biro Advokasi & Satgas Perlindungan";
                $judul        = "Intimidasi Psikis & Penyalahgunaan Kekuasaan";
                $img          = "https://picsum.photos/id/367/600/350";
                $badge_class  = "bg-purple-50 text-purple-600";
                $icon         = "fa-gavel";
                $icon_color   = "text-purple-500";
                $icon_bg      = "bg-purple-50";
                $btn_color    = "bg-purple-600 hover:bg-purple-700";
                $border_color = "border-purple-100";
                $prosedur     = "Tindakan pemerasan akademis atau intimidasi hak nilai. Sistem perlindungan SIRAKELIKA akan meneruskan aduan ini langsung ke Ombudsman/Biro Hukum Universitas.";
                $contoh_tindakan = [
                    "<strong>Ancaman Akademis:</strong> Ancaman sengaja untuk menahan nilai, mempersulit kelulusan, atau membatalkan beasiswa tanpa alasan objektif yang jelas.",
                    "<strong>Pemerasan Finansial:</strong> Pemaksaan pembayaran uang atau pembelian barang tertentu di luar ketentuan resmi universitas dengan jaminan kelulusan tugas.",
                    "<strong>Teror & Pengancaman:</strong> Pengiriman pesan berisi teror psikologis yang membuat korban merasa tidak aman berada di lingkungan kampus."
                ];
            }
            ?>

            <div class="p-6 md:p-8">

                <!-- Header Hasil -->
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-12 h-12 <?= $icon_bg ?> rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="fa <?= $icon ?> <?= $icon_color ?> text-xl"></i>
                    </div>
                    <div>
                        <span class="result-badge <?= $badge_class ?> <?= $border_color ?> border mb-1">
                            <i class="fa fa-building-columns text-[10px]"></i>
                            Ranah Penanganan: <?= $kategori ?>
                        </span>
                        <h2 class="text-base font-bold text-gray-800 leading-snug">Kategori: <?= $judul ?></h2>
                    </div>
                </div>

                <!-- Gambar -->
                <div class="w-full h-44 rounded-2xl overflow-hidden mb-5 bg-gray-100">
                    <img src="<?= $img ?>" alt="Ilustrasi" class="w-full h-full object-cover">
                </div>

                <!-- Prosedur Penanganan -->
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-5 flex gap-3">
                    <i class="fa fa-circle-info text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <p class="text-xs text-blue-700 leading-relaxed"><?= $prosedur ?></p>
                </div>

                <!-- Daftar Bentuk Tindakan -->
                <div class="border border-gray-100 rounded-xl p-4 mb-5">
                    <h3 class="text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fa fa-list-check text-blue-600"></i> Mengenali Bentuk Tindakan Ini:
                    </h3>
                    <ul class="space-y-3">
                        <?php foreach ($contoh_tindakan as $item): ?>
                        <li class="flex items-start gap-2.5 text-[11px] text-gray-600 leading-relaxed border-b border-gray-50 pb-3 last:border-0 last:pb-0">
                            <span class="w-4 h-4 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-[9px] font-bold">✓</span>
                            <span><?= $item ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Protokol Perlindungan -->
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mb-6">
                    <p class="text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fa fa-shield-halved text-blue-600"></i> Protokol Perlindungan Pelapor:
                    </p>
                    <div class="space-y-2.5">
                        <div class="flex items-start gap-3 text-xs text-gray-600">
                            <span class="w-5 h-5 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 text-[9px] font-bold mt-0.5">1</span>
                            <p><strong>Kumpulkan Bukti:</strong> Tangkapan layar chat digital, rekaman suara, kronologi kejadian, atau saksi mata.</p>
                        </div>
                        <div class="flex items-start gap-3 text-xs text-gray-600">
                            <span class="w-5 h-5 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 text-[9px] font-bold mt-0.5">2</span>
                            <p><strong>Fitur Anonim:</strong> Saat mengisi formulir laporan, Anda dapat mengaktifkan opsi "Sembunyikan Identitas" agar aman dari ancaman balik.</p>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="grid grid-cols-2 gap-3">
                    <a href="kenali.php?step=2"
                       class="py-3 border-2 border-gray-200 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-50 text-center transition flex items-center justify-center gap-2">
                        <i class="fa fa-rotate-left"></i> Cek Situasi Lain
                    </a>
                    <a href="buat_laporan.php"
                       class="py-3 <?= $btn_color ?> text-white rounded-xl text-xs font-bold text-center shadow-md transition flex items-center justify-center gap-2">
                        <i class="fa fa-file-pen"></i> Ajukan Laporan Sekarang
                    </a>
                </div>

            </div>
            <?php endif; ?>

        </div><!-- end step-card -->

        <!-- Info tambahan bawah -->
        <?php if ($step === 1): ?>
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5 mb-8 flex items-start gap-4">
            <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa fa-lock text-green-500"></i>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-1">Kerahasiaan Terjamin</h4>
                <p class="text-xs text-gray-400 leading-relaxed">Semua informasi yang Anda berikan dalam sistem ini bersifat rahasia dan hanya dapat diakses oleh pihak yang berwenang. Anda juga dapat memilih mode anonim saat melaporkan.</p>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>