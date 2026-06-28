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
    <script>tailwind.config = { corePlugins: { preflight: false } }</script>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="kenali.css">
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
            <a href="../mahasiswa/dashboard.php" class="nav-link"><span class="nav-text">Dashboard</span></a>
            <a href="../mahasiswa/laporan.php" class="nav-link"><span class="nav-text">Laporan Saya</span></a>
            <div class="nav-group">PENGELOLAAN</div>
            <a href="edukasi1.php" class="nav-link"><span class="nav-text">Edukasi & Informasi</span></a>
            <a href="../kenali-situasi/kenali.php" class="nav-link active"><span class="nav-text">Kenali Situasi Anda</span></a>
            <div class="nav-group">AKUN</div>
            <a href="#" class="nav-link"><span class="nav-text">Profil</span></a>
            <a href="#" class="nav-link"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Keluar</span></a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content" style="padding-top:0;">

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
                <p>Tidak semua kekerasan meninggalkan bekas fisik. Gunakan fitur ini untuk mengenali apa yang kamu atau temanmu alami — sebelum memutuskan langkah selanjutnya.</p>
            </div>
        </section>

        <!-- PROGRESS -->
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
                <div class="progress-bar-fill bg-blue-600 h-1.5 rounded-full" style="width:<?= $progress ?>%"></div>
            </div>
        </div>

        <!-- KONTEN STEP -->
        <div class="step-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-8">

            <?php if ($step === 1): ?>
            <!-- ======== STEP 1: INTRO ======== -->
            <div class="p-8">

                <!-- Visual banner pengganti gambar -->
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-6 mb-7 text-white flex items-center gap-5">
                    <div class="w-16 h-16 bg-white/15 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa fa-user-shield text-3xl text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold mb-1">Kamu Tidak Sendiri</h3>
                        <p class="text-sm text-blue-100 leading-relaxed">Banyak korban tidak menyadari bahwa yang mereka alami adalah kekerasan. Jawab beberapa pertanyaan singkat — kami bantu identifikasi situasimu.</p>
                    </div>
                </div>

                <h2 class="text-base font-bold text-gray-800 mb-1 text-center">Apa yang Bisa Kamu Kenali di Sini?</h2>
                <p class="text-xs text-gray-400 text-center mb-6">Sistem ini mencakup tiga kategori pelanggaran di lingkungan kampus ITH.</p>

                <!-- 3 Kategori -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div class="kategori-card bg-red-50 border border-red-100">
                        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center mb-3">
                            <i class="fa fa-person-circle-exclamation text-red-500 text-lg"></i>
                        </div>
                        <h4 class="text-xs font-bold text-red-700 mb-2">Kekerasan Seksual</h4>
                        <p class="text-[11px] text-red-500 leading-relaxed">Catcalling, rabaan tanpa izin, ancaman seksual, hingga penyebaran konten intim oleh civitas kampus.</p>
                    </div>
                    <div class="kategori-card bg-amber-50 border border-amber-100">
                        <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center mb-3">
                            <i class="fa fa-users-viewfinder text-amber-500 text-lg"></i>
                        </div>
                        <h4 class="text-xs font-bold text-amber-700 mb-2">Perundungan / Bullying</h4>
                        <p class="text-[11px] text-amber-500 leading-relaxed">Ancaman fisik, pengucilan paksa, body shaming, atau dipermalukan berulang oleh mahasiswa maupun dosen.</p>
                    </div>
                    <div class="kategori-card bg-purple-50 border border-purple-100">
                        <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center mb-3">
                            <i class="fa fa-gavel text-purple-500 text-lg"></i>
                        </div>
                        <h4 class="text-xs font-bold text-purple-700 mb-2">Intimidasi & Penyalahgunaan Kuasa</h4>
                        <p class="text-[11px] text-purple-500 leading-relaxed">Ancaman nilai, pemerasan finansial, atau sanksi akademis sepihak yang menekan kebebasanmu.</p>
                    </div>
                </div>

                <div class="text-center">
                    <a href="kenali.php?step=2"
                       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl text-sm font-bold shadow-sm transition">
                        <i class="fa fa-arrow-right"></i> Mulai Identifikasi Sekarang
                    </a>
                    <p class="text-[11px] text-gray-400 mt-3"><i class="fa fa-lock mr-1"></i>Jawabanmu bersifat rahasia dan tidak tersimpan di tahap ini.</p>
                </div>
            </div>

            <?php elseif ($step === 2): ?>
            <!-- ======== STEP 2: PILIH KATEGORI ======== -->
            <div class="p-6 md:p-8">
                <div class="mb-6">
                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full uppercase tracking-wider">Klasifikasi Kejadian</span>
                    <h2 class="text-base font-bold text-gray-800 mt-3 mb-1">Apa yang kamu atau temanmu alami?</h2>
                    <p class="text-xs text-gray-400">Pilih yang paling mendekati situasimu. Tidak ada jawaban yang salah — ini bukan ujian.</p>
                </div>

                <div class="space-y-4">

                    <a href="kenali.php?step=3&jawaban=ppks" class="option-card red group flex items-start gap-4 p-5 rounded-2xl block">
                        <div class="w-11 h-11 bg-red-50 text-red-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition">
                            <i class="fa fa-person-circle-exclamation text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-800 group-hover:text-red-600 transition">Kekerasan Seksual / Catcalling / Pelecehan Fisik</span>
                                <i class="fa fa-chevron-right text-xs text-gray-300 group-hover:text-red-400 transition"></i>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 leading-relaxed">Disentuh tanpa izin, dikirim konten asusila, dapat komentar bernada seksual, atau dipaksa dalam situasi intim oleh siapapun di lingkungan kampus.</p>
                        </div>
                    </a>

                    <a href="kenali.php?step=3&jawaban=bullying" class="option-card amber group flex items-start gap-4 p-5 rounded-2xl block">
                        <div class="w-11 h-11 bg-amber-50 text-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-amber-100 transition">
                            <i class="fa fa-users-viewfinder text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-800 group-hover:text-amber-600 transition">Perundungan / Bullying / Kekerasan Fisik</span>
                                <i class="fa fa-chevron-right text-xs text-gray-300 group-hover:text-amber-400 transition"></i>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 leading-relaxed">Dipukul, ditampar, atau diancam secara fisik. Dikucilkan, dihina, dipermalukan di depan umum secara berulang oleh mahasiswa maupun oknum dosen.</p>
                        </div>
                    </a>

                    <a href="kenali.php?step=3&jawaban=intimidasi" class="option-card purple group flex items-start gap-4 p-5 rounded-2xl block">
                        <div class="w-11 h-11 bg-purple-50 text-purple-500 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-purple-100 transition">
                            <i class="fa fa-gavel text-lg"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-800 group-hover:text-purple-600 transition">Intimidasi Nilai / Pemerasan / Penyalahgunaan Wewenang</span>
                                <i class="fa fa-chevron-right text-xs text-gray-300 group-hover:text-purple-400 transition"></i>
                            </div>
                            <p class="text-xs text-gray-400 mt-1 leading-relaxed">Diancam tidak diluluskan, dipaksa membayar di luar ketentuan, atau mendapat sanksi akademis sepihak yang terasa tidak adil dan menekan.</p>
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
                $judul        = "Kekerasan Seksual (Permendikbudristek No. 30/2021)";
                $badge_class  = "bg-red-50 text-red-600";
                $icon         = "fa-person-circle-exclamation";
                $icon_color   = "text-red-500";
                $icon_bg      = "bg-red-50";
                $banner_bg    = "bg-gradient-to-br from-red-500 to-rose-600";
                $banner_icon_bg = "bg-white/15";
                $btn_color    = "bg-red-600 hover:bg-red-700";
                $border_color = "border-red-100";
                $tagline      = "Ini serius. Kamu berhak dilindungi dan identitasmu dijamin aman sepenuhnya.";
                $prosedur     = "Kasus ini ditangani secara khusus dan rahasia oleh Satgas Pencegahan dan Penanganan Kekerasan Seksual (PPKS). Identitasmu tidak akan dibocorkan kepada siapapun, termasuk pelaku.";
                $contoh_tindakan = [
                    "<strong>Catcalling & Komentar Seksual:</strong> Siulan menggoda, lelucon seksis, atau komentar tidak senonoh atas penampilan fisik di area kampus.",
                    "<strong>Pelecehan Non-Fisik:</strong> Menatap dengan nuansa seksual, memperlihatkan konten pornografi sepihak, atau mengirim pesan asusila lewat WhatsApp/media sosial.",
                    "<strong>Pelecehan Fisik:</strong> Menyentuh, meraba, memeluk, atau mencium bagian tubuh tanpa persetujuan (konsen).",
                    "<strong>Penyalahgunaan Relasi Kuasa:</strong> Pemaksaan tindakan intim oleh oknum dosen berkedok bimbingan skripsi, tugas, atau nilai akademis."
                ];
            } elseif ($jawaban === 'bullying') {
                $kategori     = "Komisi Disiplin & Konseling";
                $judul        = "Perundungan (Bullying) & Kekerasan Fisik";
                $badge_class  = "bg-amber-50 text-amber-600";
                $icon         = "fa-users-viewfinder";
                $icon_color   = "text-amber-500";
                $icon_bg      = "bg-amber-50";
                $banner_bg    = "bg-gradient-to-br from-amber-500 to-orange-500";
                $banner_icon_bg = "bg-white/15";
                $btn_color    = "bg-amber-500 hover:bg-amber-600";
                $border_color = "border-amber-100";
                $tagline      = "Kamu tidak harus menerimanya. Tindakan ini melanggar tata tertib dan bisa dilaporkan.";
                $prosedur     = "Pelanggaran tata tertib kemahasiswaan. Laporanmu akan diteruskan ke Wakil Dekan Bidang Kemahasiswaan dan Unit Konseling untuk perlindungan psikis.";
                $contoh_tindakan = [
                    "<strong>Kekerasan Fisik:</strong> Dipukul, ditampar, didorong, dianiaya, atau dipaksa melakukan aktivitas fisik melampaui batas saat ospek atau kegiatan ormawa.",
                    "<strong>Pengucilan Sosial:</strong> Dijauhi, dikeluarkan dari grup, atau diboikot secara massal dan terorganisir dari lingkungan kelas atau kelompok belajar.",
                    "<strong>Pelecehan Verbal Berulang:</strong> Dihina berdasarkan ras, suku, kondisi fisik (body shaming), atau status ekonomi secara terus-menerus di depan orang lain."
                ];
            } else {
                $kategori     = "Biro Advokasi & Satgas Perlindungan";
                $judul        = "Intimidasi Psikis & Penyalahgunaan Kekuasaan";
                $badge_class  = "bg-purple-50 text-purple-600";
                $icon         = "fa-gavel";
                $icon_color   = "text-purple-500";
                $icon_bg      = "bg-purple-50";
                $banner_bg    = "bg-gradient-to-br from-purple-600 to-violet-700";
                $banner_icon_bg = "bg-white/15";
                $btn_color    = "bg-purple-600 hover:bg-purple-700";
                $border_color = "border-purple-100";
                $tagline      = "Tekanan akademis yang disengaja adalah pelanggaran serius. Kamu berhak melawannya secara resmi.";
                $prosedur     = "Tindakan pemerasan akademis atau intimidasi hak nilai. Aduanmu akan diteruskan langsung ke Ombudsman dan Biro Hukum Universitas untuk ditindaklanjuti.";
                $contoh_tindakan = [
                    "<strong>Ancaman Akademis:</strong> Ancaman menahan nilai, mempersulit kelulusan, atau membatalkan beasiswa tanpa alasan objektif yang jelas.",
                    "<strong>Pemerasan Finansial:</strong> Dipaksa membayar uang atau membeli barang tertentu di luar ketentuan resmi kampus dengan imbalan kelulusan atau nilai.",
                    "<strong>Teror & Pengancaman:</strong> Menerima pesan ancaman psikologis yang membuatmu merasa tidak aman atau tertekan untuk hadir di kampus."
                ];
            }
            ?>

            <div class="p-6 md:p-8">

                <!-- Visual banner pengganti gambar -->
                <div class="<?= $banner_bg ?> rounded-2xl p-6 mb-6 text-white flex items-center gap-5">
                    <div class="w-14 h-14 <?= $banner_icon_bg ?> rounded-2xl flex items-center justify-center flex-shrink-0">
                        <i class="fa <?= $icon ?> text-2xl text-white"></i>
                    </div>
                    <div>
                        <span class="inline-block text-[10px] font-bold bg-white/20 px-2 py-1 rounded-md mb-2 uppercase tracking-wide">
                            Ranah Penanganan: <?= $kategori ?>
                        </span>
                        <h3 class="text-sm font-bold leading-snug mb-1"><?= $judul ?></h3>
                        <p class="text-xs text-white/80 leading-relaxed"><?= $tagline ?></p>
                    </div>
                </div>

                <!-- Prosedur -->
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-5 flex gap-3">
                    <i class="fa fa-circle-info text-blue-500 mt-0.5 flex-shrink-0"></i>
                    <p class="text-xs text-blue-700 leading-relaxed"><?= $prosedur ?></p>
                </div>

                <!-- Bentuk Tindakan -->
                <div class="border border-gray-100 rounded-xl p-4 mb-5">
                    <h3 class="text-xs font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <i class="fa fa-list-check text-blue-600"></i> Kenali Bentuk Tindakannya:
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
                            <p><strong>Kumpulkan Bukti:</strong> Tangkapan layar percakapan, rekaman suara, kronologi tertulis, atau keterangan saksi mata yang kamu percaya.</p>
                        </div>
                        <div class="flex items-start gap-3 text-xs text-gray-600">
                            <span class="w-5 h-5 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 text-[9px] font-bold mt-0.5">2</span>
                            <p><strong>Mode Anonim Tersedia:</strong> Kamu bisa melaporkan tanpa mencantumkan nama. Identitasmu tidak akan diketahui pelaku maupun pihak yang tidak berwenang.</p>
                        </div>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="grid grid-cols-2 gap-3">
                    <a href="kenali.php?step=2"
                       class="py-3 border-2 border-gray-200 rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-50 text-center transition flex items-center justify-center gap-2">
                        <i class="fa fa-rotate-left"></i> Cek Situasi Lain
                    </a>
                    <a href="../mahasiswa/buat_laporan.php"
                       class="py-3 <?= $btn_color ?> text-white rounded-xl text-xs font-bold text-center shadow-md transition flex items-center justify-center gap-2">
                        <i class="fa fa-file-pen"></i> Ajukan Laporan Sekarang
                    </a>
                </div>

            </div>
            <?php endif; ?>

        </div>

        <!-- Info kerahasiaan (step 1 saja) -->
        <?php if ($step === 1): ?>
        <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5 mb-8 flex items-start gap-4">
            <div class="w-9 h-9 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fa fa-lock text-green-500"></i>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-1">Kerahasiaan Terjamin</h4>
                <p class="text-xs text-gray-400 leading-relaxed">Semua informasi yang kamu berikan dijaga ketat dan hanya dapat diakses oleh pihak yang berwenang. Kamu juga bisa memilih mode anonim saat melaporkan — tanpa nama, tetap diproses.</p>
            </div>
        </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>

