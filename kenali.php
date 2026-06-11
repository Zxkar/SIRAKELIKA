<?php
// 1. Ambil tahapan kuis dari URL (Jika baru buka, otomatis Step 1)
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$jawaban = isset($_GET['jawaban']) ? $_GET['jawaban'] : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kenali Situasi Anda - SIRAKELIKA Perlindungan Kampus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body class="bg-[#F8FAFC] font-sans">

    <div class="flex min-h-screen">
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
                <a href="dashboard.php" class="nav-link">
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="#" class="nav-link">
                    <span class="nav-text">Laporan Saya</span>
                </a>

                <div class="nav-group">PENGELOLAAN</div>
                <a href="#" class="nav-link">
                    <span class="nav-text">Manajemen Kasus</span>
                </a>
                <a href="edukasi.php" class="nav-link">
                    <span class="nav-text">Edukasi & Informasi</span>
                </a>
                <a href="kenali.php" class="nav-link active">
                    <span class="nav-text">Kenali Situasi Anda</span>
                </a>

                <div class="nav-group">AKUN</div>
                <a href="#" class="nav-link">
                    <span class="nav-text">Profil</span>
                </a>
                <a href="#" class="nav-link">
                    <span class="nav-text">Pengaturan</span>
                </a>
                <a href="logout.php" class="nav-link logout">
                    <span class="nav-text">Keluar</span>
                </a>
            </nav>
        </aside>

        <main class="main-content" style="padding-top: 24px; display: flex; align-items: center; justify-content: center;">
            
            <div class="bg-white max-w-2xl w-full rounded-2xl border border-gray-100 shadow-md overflow-hidden my-4">
                
                <div class="bg-gradient-to-r from-blue-700 to-indigo-600 p-6 text-white text-center">
                    <h1 class="text-lg font-bold mb-1">🛡️ Sistem Identifikasi Awal Pengaduan</h1>
                    <p class="text-[11px] text-blue-100 opacity-90">Membantu mengenali jenis pelanggaran di lingkungan kampus sebelum melakukan pelaporan resmi</p>
                </div>

                <div class="p-6 md:p-8">

                    <?php if ($step === 1): ?>
                        <div class="text-center py-4">
                            <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 text-3xl">
                                <i class="fa fa-user-shield"></i>
                            </div>
                            <h2 class="text-base font-bold text-gray-800 mb-2">Jangan Diam, Lindungi Diri & Rekan Kampus</h2>
                            <p class="text-xs text-gray-400 max-w-md mx-auto mb-6 leading-relaxed">
                                Melalui sistem SIRAKELIKA, Anda dapat mengenali apakah tindakan mencederai hak akademis yang Anda alami termasuk kategori Pelanggaran PPKS (Kekerasan Seksual), Perundungan (Bullying), atau Intimidasi Psikis.
                            </p>
                            <a href="kenali.php?step=2" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-xs font-bold shadow-sm transition">
                                Mulai Identifikasi Kasus
                            </a>
                        </div>

                    <?php elseif ($step === 2): ?>
                        <div>
                            <span class="text-[9px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded uppercase tracking-wider block w-max mb-3">Langkah 1: Klasifikasi Kejadian</span>
                            <h2 class="text-sm font-bold text-gray-800 mb-5 leading-snug">Pilih kondisi atau perlakuan yang sedang terjadi di lingkungan kampus:</h2>
                            
                            <div class="space-y-3">
                                <a href="kenali.php?step=3&jawaban=ppks" class="group flex items-start gap-4 p-4 border rounded-xl hover:border-blue-500 hover:bg-blue-50 transition">
                                    <div class="bg-red-50 text-red-600 p-2.5 rounded-lg text-xs group-hover:bg-red-100"><i class="fa fa-person-circle-exclamation text-sm"></i></div>
                                    <div>
                                        <span class="block text-xs font-bold text-gray-700 group-hover:text-red-600">Kekerasan Seksual / Catcalling / Isyarat Asusila</span>
                                        <span class="block text-[11px] text-gray-400 mt-0.5">Mendapat komentar bernada seksual, sentuhan fisik sepihak, atau penyebaran konten intim non-konsensual oleh civitas kampus.</span>
                                    </div>
                                </a>

                                <a href="kenali.php?step=3&jawaban=bullying" class="group flex items-start gap-4 p-4 border rounded-xl hover:border-blue-500 hover:bg-blue-50 transition">
                                    <div class="bg-amber-50 text-amber-600 p-2.5 rounded-lg text-xs group-hover:bg-amber-100"><i class="fa fa-users-viewfinder text-sm"></i></div>
                                    <div>
                                        <span class="block text-xs font-bold text-gray-700 group-hover:text-amber-600">Perundungan Senioritas / Bullying / Kekerasan Fisik</span>
                                        <span class="block text-[11px] text-gray-400 mt-0.5">Diancam secara fisik, dikucilkan dalam kelompok belajar secara paksa, atau dipermalukan oleh oknum mahasiswa/dosen.</span>
                                    </div>
                                </a>

                                <a href="kenali.php?step=3&jawaban=intimidasi" class="group flex items-start gap-4 p-4 border rounded-xl hover:border-blue-500 hover:bg-blue-50 transition">
                                    <div class="bg-purple-50 text-purple-600 p-2.5 rounded-lg text-xs group-hover:bg-purple-100"><i class="fa fa-gavel text-sm"></i></div>
                                    <div>
                                        <span class="block text-xs font-bold text-gray-700 group-hover:text-purple-600">Penyalahgunaan Wewenang / Intimidasi Nilai</span>
                                        <span class="block text-[11px] text-gray-400 mt-0.5">Diancam tidak diluluskan mata kuliah, pemerasan finansial berkedok tugas, atau ancaman sanksi sepihak.</span>
                                    </div>
                                </a>
                            </div>

                            <div class="mt-6 border-t pt-4">
                                <a href="kenali.php?step=1" class="text-xs text-gray-400 hover:text-gray-600 font-medium"><i class="fa fa-arrow-left text-[10px] mr-1"></i> Kembali</a>
                            </div>
                        </div>

                    <?php elseif ($step === 3): ?>
                        <?php
                        // Detailing indikasi tindakan berdasarkan apa yang dipilih di Step 2
                        if ($jawaban === 'ppks') {
                            $kategori = "Satgas PPKS Kampus";
                            $judul = "Kategori: Kekerasan Seksual (Melanggar Permendikbudristek No. 30/2021)";
                            $img = "https://picsum.photos/id/119/600/350"; 
                            $bg_badge = "bg-red-50 text-red-600";
                            $prosedur = "Kasus ini ditangani secara khusus dan rahasia oleh Satgas Pencegahan dan Penanganan Kekerasan Seksual (PPKS). Hak identitas Anda dijamin aman sepenuhnya.";
                            
                            // Daftar contoh konkret tindakan PPKS
                            $contoh_tindakan = [
                                "Kekerasan Verbal/Catcalling: Lelucon seksis, siulan menggoda, komentar tidak senonoh atas bentuk tubuh atau pakaian di area kampus.",
                                "Kekerasan Non-Fisik: Menatap dengan nuansa seksual, memperlihatkan konten pornografi sepihak, atau mengirim pesan/pap teks asusila melalui WhatsApp/Media Sosial.",
                                "Kekerasan Fisik: Menyentuh, meraba, memeluk, mencium, atau memegang bagian tubuh tanpa persetujuan (konsen).",
                                "Penyalahgunaan Relasi Kuasa: Pemaksaan tindakan intim oleh oknum berkedok bimbingan skripsi, tugas, atau nilai akademis."
                            ];
                        } elseif ($jawaban === 'bullying') {
                            $kategori = "Komisi Disiplin & Konseling";
                            $judul = "Kategori: Perundungan (Bullying) & Kekerasan Fisik Akademis";
                            $img = "https://picsum.photos/id/64/600/350";
                            $bg_badge = "bg-amber-50 text-amber-600";
                            $prosedur = "Tindakan pelanggaran tata tertib kemahasiswaan. Pelaporan Anda akan diteruskan ke Wakil Dekan Bidang Kemahasiswaan dan Unit Konseling untuk perlindungan psikis.";
                            
                            // Daftar contoh konkret tindakan Bullying
                            $contoh_tindakan = [
                                "Kekerasan Fisik Nyata: Pemukulan, dorongan sengaja, pelemparan benda, atau pemaksaan aktivitas fisik di luar batas normal (misal saat ospek/kegiatan ormawa).",
                                "Perundungan Sosial (Pengucilan): Ajakan massal untuk memboikot atau mengucilkan seorang mahasiswa dari pergaulan kelas atau kelompok belajar.",
                                "Pelecehan Verbal: Penghinaan ras, suku, kondisi fisik (body shaming), atau status ekonomi yang diucapkan secara sengaja untuk menjatuhkan mental."
                            ];
                        } else {
                            $kategori = "Biro Advokasi & Satgas Perlindungan";
                            $judul = "Kategori: Intimidasi Psikis & Penyalahgunaan Kekuasaan";
                            $img = "https://picsum.photos/id/367/600/350";
                            $bg_badge = "bg-purple-50 text-purple-600";
                            $prosedur = "Tindakan pemerasan akademis atau intimidasi hak nilai. Sistem perlindungan SIRAKELIKA akan meneruskan aduan ini langsung ke Ombudsman/Biro Hukum Universitas.";
                            
                            // Daftar contoh konkret tindakan Intimidasi
                            $contoh_tindakan = [
                                "Ancaman Akademis: Ancaman sengaja untuk menahan nilai, mempersulit kelulusan, atau membatalkan beasiswa tanpa alasan objektif yang jelas.",
                                "Pemerasan Finansial: Pemaksaan pembayaran uang atau pembelian barang tertentu di luar ketentuan resmi universitas dengan jaminan kelulusan tugas.",
                                "Teror & Pengancaman: Pengiriman pesan berisi teror psikologis yang membuat korban merasa tidak aman berada di lingkungan kampus."
                            ];
                        }
                        ?>

                        <div class="text-center">
                            <div class="w-full h-44 rounded-xl overflow-hidden mb-5 bg-gray-100">
                                <img src="<?= $img ?>" alt="Ilustrasi Proteksi" class="w-full h-full object-cover">
                            </div>

                            <span class="inline-block text-[10px] font-bold <?= $bg_badge ?> px-2.5 py-1 rounded-full uppercase tracking-wider mb-2">
                                Ranah Penanganan: <?= $kategori ?>
                            </span>
                            <h2 class="text-base font-bold text-gray-800 mb-2"><?= $judul ?></h2>
                            <p class="text-xs text-gray-500 leading-relaxed max-w-md mx-auto mb-6"><?= $prosedur ?></p>

                            <div class="text-left bg-white border border-gray-200 rounded-xl p-4 mb-6 shadow-sm">
                                <h3 class="text-xs font-bold text-gray-700 mb-2 flex items-center gap-2">
                                    <i class="fa fa-list-check text-blue-600"></i> Mengenali Bentuk Tindakan Ini:
                                </h3>
                                <ul class="space-y-2 text-[11px] text-gray-600">
                                    <?php foreach ($contoh_tindakan as $item): ?>
                                        <li class="flex items-start gap-2 border-b border-gray-50 pb-1.5 last:border-0 last:pb-0">
                                            <span class="text-blue-500 mt-0.5">•</span>
                                            <span><?= $item ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>

                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-left text-xs text-gray-600 mb-6 space-y-2">
                                <p class="font-bold text-gray-700"><i class="fa fa-shield-cat text-blue-600 mr-1"></i> Protokol Perlindungan Pelapor:</p>
                                <p>1. <strong>Kumpulkan Bukti:</strong> Tangkapan layar chat digital, rekaman suara, kronologi kejadian, atau saksi mata.</p>
                                <p>2. <strong>Fitur Anonim:</strong> Saat mengisi formulir laporan, Anda dapat mengaktifkan opsi "Sembunyikan Identitas" agar aman dari ancaman balik.</p>
                            </div>

                            <div class="flex gap-3">
                                <a href="kenali.php?step=2" class="flex-1 py-2.5 border rounded-xl text-xs font-bold text-gray-500 hover:bg-gray-50 text-center">
                                    Cek Situasi Lain
                                </a>
                                <a href="buat-laporan.php" class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold text-center shadow-md transition">
                                    <i class="fa fa-file-pen mr-1"></i> Ajukan Laporan Sekarang
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