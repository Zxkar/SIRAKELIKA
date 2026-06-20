<?php
// 1. Ambil data dari URL (Pakai cara simpel aja)
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$jawaban = isset($_GET['jawaban']) ? $_GET['jawaban'] : '';

// 2. Data konten buat Step 3 (Biar gak kebanyakan if-else di bawah)
$kategori_data = [
    'ppks' => [
        'kategori'  => "Satgas PPKS Kampus",
        'judul'     => "Kategori: Kekerasan Seksual (Permendikbudristek No. 30/2021)",
        'img'       => "https://picsum.photos/id/119/600/350",
        'bg_badge'  => "bg-red-100 text-red-700",
        'prosedur'  => "Kasus ini ditangani secara rahasia oleh Satgas Pencegahan & Penanganan Kekerasan Seksual (PPKS).",
        'tindakan'  => [
            "Catcalling / Lelucon seksis soal bentuk tubuh.",
            "Mengirim pesan/foto asusila lewat WhatsApp tanpa persetujuan.",
            "Sentuhan fisik sepihak atau pemaksaan tindakan intim."
        ]
    ],
    'bullying' => [
        'kategori'  => "Komisi Disiplin & Kemahasiswaan",
        'judul'     => "Kategori: Perundungan (Bullying) & Fisik",
        'img'       => "https://picsum.photos/id/64/600/350",
        'bg_badge'  => "bg-amber-100 text-amber-700",
        'prosedur'  => "Tindakan ini melanggar tata tertib. Laporan akan diteruskan ke Wakil Dekan III.",
        'tindakan'  => [
            "Kekerasan fisik atau pemaksaan aktivitas di luar batas (saat ospek/ormawa).",
            "Pengucilan digital atau ajakan memboikot teman sekelas.",
            "Pelecehan verbal (body shaming, ejekan suku/ras)."
        ]
    ],
    'intimidasi' => [
        'kategori'  => "Biro Advokasi Kampus",
        'judul'     => "Kategori: Intimidasi & Penyalahgunaan Wewenang",
        'img'       => "https://picsum.photos/id/367/600/350",
        'bg_badge'  => "bg-purple-100 text-purple-700",
        'prosedur'  => "Tindakan pemerasan nilai atau ancaman akademis oleh oknum tertentu.",
        'tindakan'  => [
            "Ancaman tidak diluluskan mata kuliah tanpa alasan objektif.",
            "Pemerasan finansial berkedok nilai tugas kelompok.",
            "Pesan teror yang membuat tidak nyaman ke kampus."
        ]
    ]
];

// Antisipasi kalau jawaban ngawur
if ($step === 3 && !array_key_exists($jawaban, $kategori_data)) {
    $jawaban = 'intimidasi'; 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenali Situasi - SIRAKELIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body class="bg-slate-50 font-sans">

    <div class="flex min-h-screen">
        
        <!-- SIDEBAR (Tetap bawaan kamu) -->
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
                <a href="#" class="nav-link"><span class="nav-text">Manajemen Kasus</span></a>
                <a href="edukasi.php" class="nav-link"><span class="nav-text">Edukasi</span></a>
                <a href="kenali.php" class="nav-link active"><span class="nav-text">Kenali Situasi</span></a>
                <div class="nav-group">AKUN</div>
                <a href="logout.php" class="nav-link logout"><span class="nav-text">Keluar</span></a>
            </nav>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="flex-1 flex items-center justify-center p-6">
            
            <!-- CARD UTAMA -->
            <div class="bg-white max-w-xl w-full rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                
                <!-- HEADER CARD -->
                <div class="bg-blue-600 p-5 text-white text-center">
                    <h1 class="text-base font-bold">🛡️ Sistem Identifikasi Awal Pengaduan</h1>
                    <p class="text-[11px] text-blue-100">Cek jenis pelanggaran sebelum melakukan pelaporan resmi</p>
                </div>

                <div class="p-6">

                    <!-- STEP 1 -->
                    <?php if ($step === 1): ?>
                        <div class="text-center py-4">
                            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
                                <i class="fa fa-user-shield"></i>
                            </div>
                            <h2 class="text-sm font-bold text-slate-800 mb-2">Kenali Situasi di Sekitarmu</h2>
                            <p class="text-xs text-slate-500 max-w-sm mx-auto mb-6 leading-relaxed">
                                Fitur ini membantu mahasiswa mendeteksi apakah masalah yang dialami masuk ranah Kekerasan Seksual, Bullying, atau Intimidasi Nilai.
                            </p>
                            <a href="kenali.php?step=2" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-xs font-semibold transition">
                                Mulai Cek
                            </a>
                        </div>

                    <!-- STEP 2 -->
                    <?php elseif ($step === 2): ?>
                        <div>
                            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded mb-3 inline-block">Langkah 1: Pilih Kondisi</span>
                            <h2 class="text-xs font-bold text-slate-700 mb-4">Apa yang sedang kamu alami di kampus?</h2>
                            
                            <div class="space-y-2.5">
                                <a href="kenali.php?step=3&jawaban=ppks" class="block p-4 border rounded-xl hover:border-blue-500 hover:bg-blue-50/50 transition">
                                    <span class="block text-xs font-bold text-slate-800">1. Kekerasan Seksual / Catcalling</span>
                                    <span class="block text-[11px] text-slate-400 mt-0.5">Sering digoda tidak sopan, disentuh sepihak, atau dikirimi chat asusila.</span>
                                </a>

                                <a href="kenali.php?step=3&jawaban=bullying" class="block p-4 border rounded-xl hover:border-blue-500 hover:bg-blue-50/50 transition">
                                    <span class="block text-xs font-bold text-slate-800">2. Perundungan / Bullying / Kekerasan Fisik</span>
                                    <span class="block text-[11px] text-slate-400 mt-0.5">Diancam fisik, dikucilkan paksa, atau dipermalukan senior/oknum kampus.</span>
                                </a>

                                <a href="kenali.php?step=3&jawaban=intimidasi" class="block p-4 border rounded-xl hover:border-blue-500 hover:bg-blue-50/50 transition">
                                    <span class="block text-xs font-bold text-slate-800">3. Penyalahgunaan Wewenang / Intimidasi Nilai</span>
                                    <span class="block text-[11px] text-slate-400 mt-0.5">Diancam tidak lulus sepihak atau diperas uang berkedok tugas kuliah.</span>
                                </a>
                            </div>

                            <div class="mt-5 border-t pt-3">
                                <a href="kenali.php?step=1" class="text-xs text-slate-400 hover:text-slate-600"><i class="fa fa-arrow-left text-[10px] mr-1"></i> Kembali</a>
                            </div>
                        </div>

                    <!-- STEP 3 -->
                    <?php elseif ($step === 3): ?>
                        <?php $current = $kategori_data[$jawaban]; ?>
                        <div>
                            <div class="w-full h-40 rounded-xl overflow-hidden mb-4 bg-slate-100">
                                <img src="<?= $current['img'] ?>" class="w-full h-full object-cover">
                            </div>

                            <div class="text-center mb-4">
                                <span class="inline-block text-[10px] font-bold <?= $current['bg_badge'] ?> px-2 py-0.5 rounded mb-1">
                                    Unit Penanganan: <?= $current['kategori'] ?>
                                </span>
                                <h2 class="text-sm font-bold text-slate-800"><?= $current['judul'] ?></h2>
                                <p class="text-xs text-slate-500 mt-1"><?= $current['prosedur'] ?></p>
                            </div>

                            <div class="bg-slate-50 border rounded-xl p-4 mb-4 text-left">
                                <h3 class="text-xs font-bold text-slate-700 mb-2">Contoh Tindakan:</h3>
                                <ul class="space-y-1.5 text-[11px] text-slate-600">
                                    <?php foreach ($current['tindakan'] as $item): ?>
                                        <li class="flex items-start gap-1">
                                            <span class="text-blue-500">•</span>
                                            <span><?= $item ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="flex gap-2">
                                <a href="kenali.php?step=2" class="flex-1 py-2 border rounded-lg text-xs font-semibold text-slate-500 hover:bg-slate-50 text-center">
                                    Ulangi Cek
                                </a>
                                <a href="buat_laporan.php" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold text-center transition">
                                    Buat Laporan Sekarang
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </main>
    </div>

</body>
</html>