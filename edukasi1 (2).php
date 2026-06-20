<?php
session_start();
require_once 'conn.php';

// ======================== PROSES FORM KONSULTASI ========================
$konsultasi_success = '';
$konsultasi_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kirim_konsultasi'])) {
    $nama_pengirim = trim($_POST['nama'] ?? '');
    $topik = trim($_POST['topik'] ?? '');
    $pesan_isi = trim($_POST['pesan'] ?? '');
    $id_mahasiswa = $_SESSION['id_mahasiswa'] ?? null;
    $is_anonim = (empty($nama_pengirim) || strtolower($nama_pengirim) === 'anonim') ? 1 : 0;

    if (empty($pesan_isi)) {
        $konsultasi_error = 'Pesan tidak boleh kosong.';
    } else {
        // Gabungkan topik + nama (jika ada) ke dalam pesan yang disimpan
        $pesan_lengkap = '';
        if (!empty($topik)) {
            $pesan_lengkap .= "[Topik: $topik]\n";
        }
        if (!$is_anonim) {
            $pesan_lengkap .= "Nama: $nama_pengirim\n";
        }
        $pesan_lengkap .= $pesan_isi;

        // Simpan ke database (tabel konsultasi)
        $stmt = $conn->prepare("INSERT INTO konsultasi (id_mahasiswa, id_admin, pesan, pengirim, is_anonim) VALUES (?, NULL, ?, 'mahasiswa', ?)");
        $stmt->bind_param("isi", $id_mahasiswa, $pesan_lengkap, $is_anonim);

        if ($stmt->execute()) {
            // Kirim email notifikasi ke admin
            $to = "sirakelika@itbj.ac.id";
            $subject = "Pesan Konsultasi Baru - SIRAKELIKA";
            $nama_tampil = $is_anonim ? 'Anonim' : htmlspecialchars($nama_pengirim);
            $topik_tampil = !empty($topik) ? htmlspecialchars($topik) : '-';

            $body = "Ada pesan konsultasi baru dari sistem SIRAKELIKA.\n\n";
            $body .= "Nama: $nama_tampil\n";
            $body .= "Topik: $topik_tampil\n";
            $body .= "Pesan:\n" . $pesan_isi . "\n\n";
            $body .= "Waktu: " . date('d-m-Y H:i:s') . "\n";

            $headers = "From: SIRAKELIKA <no-reply@itbj.ac.id>\r\n";
            $headers .= "Reply-To: no-reply@itbj.ac.id\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            // Kirim email (membutuhkan server mendukung fungsi mail())
            @mail($to, $subject, $body, $headers);

            $konsultasi_success = 'Pesan Anda berhasil dikirim. Tim kami akan segera merespon.';
        } else {
            $konsultasi_error = 'Gagal menyimpan pesan. Silakan coba lagi.';
        }
        $stmt->close();
    }
}


$all_articles = [
    [
        "id" => 1,
        "img" => "https://picsum.photos/id/119/600/400",
        "tag" => "Hukum & Hak",
        "slug" => "hukum-hak",
        "date" => "08 Mei 2026",
        "views" => "1200",
        "title" => "Memahami Permendikbudristek No. 30/2021: Hak dan Perlindungan Korban Kekerasan Seksual di Kampus",
        "desc" => "Peraturan Menteri ini menjadi landasan hukum penting yang mengatur mekanisme pencegahan dan penanganan kekerasan seksual di lingkungan perguruan tinggi. Pelajari hak-hak yang dimiliki korban.",
        "author" => "Tim Hukum SIRAKELIKA",
        "url" => "https://repositori.kemendikdasmen.go.id/24916/1/Buku%20Saku%20Permendikbudristek%20No.%2030%20Tahun%202021.pdf"
    ],
    [
        "id" => 2,
        "img" => "https://picsum.photos/id/64/600/400",
        "tag" => "Psikologi & Kesehatan",
        "slug" => "psikologi-kesehatan",
        "date" => "06 Mei 2026",
        "views" => "842",
        "title" => "Dampak Psikologis: Perundungan pada Mahasiswa dan Cara Pemulihannya",
        "desc" => "Perundungan dapat meninggalkan luka psikologis yang mendalam. Artikel ini membahas gejala trauma dan langkah pemulihan yang efektif bagi korban perundungan di lingkungan akademis.",
        "author" => "Dr. Psikologi",
        "url" => "https://shariajournal.com/index.php/IERJ/article/view/745"
    ],
    [
        "id" => 3,
        "img" => "https://picsum.photos/id/524/600/400",
        "tag" => "Kekerasan Verbal",
        "slug" => "kekerasan-verbal",
        "date" => "04 Mei 2026",
        "views" => "915",
        "title" => "Mengenali Kekerasan Verbal: Dari Candaan hingga Ujaran Kebencian",
        "desc" => "Kekerasan verbal sering dianggap sepele. Kenali bentuk-bentuknya agar Anda bisa melindungi diri dan orang sekitar dari dampak buruk manipulasi kata-kata.",
        "author" => "Satgas Kampus",
        "url" => "https://www.halodoc.com/artikel/verbal-abuse-pengertian-ciri-ciri-dan-dampaknya"
    ],
    [
        "id" => 4,
        "img" => "https://picsum.photos/id/367/600/400",
        "tag" => "Panduan",
        "slug" => "panduan",
        "date" => "02 Mei 2026",
        "views" => "1500",
        "title" => "Panduan Lengkap: Cara Melaporkan Kekerasan di Lingkungan Kampus",
        "desc" => "Langkah-langkah praktis yang perlu dilakukan saat menghadapi atau menyaksikan kekerasan di kampus, termasuk cara menggunakan sistem pelaporan SIRAKELIKA.",
        "author" => "Tim SIRAKELIKA",
        "url" => "kenali.php"
    ]
];

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['kategori']) ? $_GET['kategori'] : 'semua';

$filtered_articles = [];
foreach ($all_articles as $article) {
    $match_search = true;
    $match_category = true;
    if (!empty($search_query)) {
        if (stripos($article['title'], $search_query) === false && stripos($article['desc'], $search_query) === false) {
            $match_search = false;
        }
    }
    if ($category_filter !== 'semua') {
        if ($article['slug'] !== $category_filter) {
            $match_category = false;
        }
    }
    if ($match_search && $match_category) {
        $filtered_articles[] = $article;
    }
}

$faqs = [
    [
        "q" => "Apakah laporan yang saya buat bersifat rahasia?",
        "a" => "Ya, setiap laporan yang masuk ke sistem SIRAKELIKA dijaga kerahasiaannya. Data pelapor hanya dapat diakses oleh pihak yang berwenang, yaitu Admin dan Tim Investigasi yang telah ditunjuk oleh kampus."
    ],
    [
        "q" => "Bisakah saya melapor secara anonim?",
        "a" => "Bisa. SIRAKELIKA menyediakan dua mode pelaporan: pelaporan umum (anonim) di mana identitas Anda tidak dicantumkan, dan pelaporan khusus (non-anonim) di mana identitas Anda tersimpan namun tetap dijaga kerahasiaannya."
    ],
    [
        "q" => "Berapa lama proses penanganan laporan?",
        "a" => "Setiap laporan akan diverifikasi dalam 1x24 jam oleh Admin. Setelah diverifikasi, Tim Investigasi akan menindaklanjuti laporan sesuai prosedur kampus, biasanya dalam 3-7 hari kerja tergantung kompleksitas kasus."
    ],
    [
        "q" => "Apa saja jenis kekerasan yang bisa dilaporkan melalui SIRAKELIKA?",
        "a" => "SIRAKELIKA menerima laporan terkait kekerasan fisik, kekerasan verbal, pelecehan seksual, kekerasan psikologis, dan perundungan (bullying) yang terjadi di lingkungan kampus Institut Teknologi B.J. Habibie."
    ],
    [
        "q" => "Apakah saya bisa memantau perkembangan laporan saya?",
        "a" => "Ya. Setelah login, Anda dapat memantau status laporan melalui menu 'Laporan Saya'. Status akan diperbarui secara real-time saat ada perubahan dari Admin atau Tim Investigasi."
    ],
    [
        "q" => "Bagaimana jika saya tidak tahu termasuk jenis kekerasan apa yang saya alami?",
        "a" => "Gunakan fitur 'Kenali Situasi Anda' di menu sidebar. Fitur ini akan memandu Anda melalui serangkaian pertanyaan sederhana untuk membantu mengidentifikasi jenis kekerasan yang Anda alami dan merekomendasikan langkah selanjutnya."
    ],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edukasi & Informasi - SIRAKELIKA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { corePlugins: { preflight: false } }
    </script>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        .faq-item .faq-answer { display: none; }
        .faq-item.open .faq-answer { display: block; }
        .faq-item.open .faq-icon { transform: rotate(45deg); }
        .faq-icon { transition: transform 0.25s ease; }
        .section-anchor { scroll-margin-top: 24px; }
        .tab-btn.active-tab {
            background: #2563eb;
            color: #fff;
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
            
            <a href="edukasi.php" class="nav-link active"><span class="nav-text">Edukasi & Informasi</span></a>
            <a href="kenali.php" class="nav-link"><span class="nav-text">Kenali Situasi Anda</span></a>
            <div class="nav-group">AKUN</div>
            <a href="#" class="nav-link"><span class="nav-text">Profil</span></a>
            <a href="#" class="nav-link"><span class="nav-text">Pengaturan</span></a>
            <a href="logout.php" class="nav-link logout"><span class="nav-text">Keluar</span></a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main-content" style="padding-top: 0;">

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

        <!-- HERO BANNER -->
        <section class="welcome-banner mb-8">
            <div class="banner-text">
                <h2>Edukasi & Informasi</h2>
                <p>Pelajari berbagai bentuk kekerasan, hak-hak korban, dan cara melaporkannya. Pengetahuan adalah langkah pertama perlindungan diri.</p>
            </div>
        </section>

        <!-- NAVIGASI SECTION (Tab) -->
        <div class="flex gap-2 mb-8 flex-wrap">
            <button onclick="scrollToSection('artikel')" class="tab-btn active-tab px-5 py-2 rounded-full text-xs font-bold border border-blue-600 transition">📰 Artikel</button>
            <button onclick="scrollToSection('video')" class="tab-btn px-5 py-2 rounded-full text-xs font-bold bg-white border text-gray-500 transition hover:bg-blue-50">🎥 Video</button>
            <button onclick="scrollToSection('faq')" class="tab-btn px-5 py-2 rounded-full text-xs font-bold bg-white border text-gray-500 transition hover:bg-blue-50">❓ FAQ</button>
            <button onclick="scrollToSection('kontak')" class="tab-btn px-5 py-2 rounded-full text-xs font-bold bg-white border text-gray-500 transition hover:bg-blue-50">📞 Kontak</button>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                <div class="bg-blue-50 text-blue-600 p-2.5 rounded-lg text-sm"><i class="fa fa-file-alt"></i></div>
                <div><span class="text-lg font-bold text-gray-800 block">4</span><span class="text-[10px] text-gray-400">Total Artikel</span></div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                <div class="bg-emerald-50 text-emerald-600 p-2.5 rounded-lg text-sm"><i class="fa fa-video"></i></div>
                <div><span class="text-lg font-bold text-gray-800 block">2</span><span class="text-[10px] text-gray-400">Video Edukasi</span></div>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                <div class="bg-purple-50 text-purple-600 p-2.5 rounded-lg text-sm"><i class="fa fa-question-circle"></i></div>
                <div><span class="text-lg font-bold text-gray-800 block"><?= count($faqs) ?></span><span class="text-[10px] text-gray-400">FAQ Tersedia</span></div>
            </div>
        </div>

        <!-- ======================== SECTION: ARTIKEL ======================== -->
        <section id="artikel" class="section-anchor mb-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-800">📰 Artikel Terbaru</h2>
            </div>

            <!-- Search & Filter -->
            <form method="GET" class="flex gap-2 mb-5">
                <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Cari artikel..." class="flex-1 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition">Cari</button>
                <?php if (!empty($search_query) || $category_filter !== 'semua'): ?>
                    <a href="edukasi.php" class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-4 py-2.5 rounded-xl text-sm font-semibold transition">Reset</a>
                <?php endif; ?>
            </form>

            <!-- Filter Kategori -->
            <div class="flex overflow-x-auto gap-2 mb-6 pb-1">
                <?php
                $kategori_list = [
                    'semua' => 'Semua',
                    'hukum-hak' => 'Hukum & Hak',
                    'kekerasan-verbal' => 'Kekerasan Verbal',
                    'psikologi-kesehatan' => 'Psikologi & Kesehatan',
                    'panduan' => 'Panduan',
                ];
                foreach ($kategori_list as $slug => $label):
                    $isActive = $category_filter === $slug;
                ?>
                <a href="edukasi.php?kategori=<?= $slug ?><?= !empty($search_query) ? '&search='.urlencode($search_query) : '' ?>"
                   class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap <?= $isActive ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 border hover:bg-blue-50' ?>">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>

            <!-- Grid Artikel -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <?php if (empty($filtered_articles)): ?>
                    <div class="col-span-2 bg-white p-10 text-center text-sm text-gray-400 rounded-2xl border">
                        <i class="fa fa-search text-2xl mb-3 block text-gray-300"></i>
                        Artikel tidak ditemukan untuk pencarian "<strong><?= htmlspecialchars($search_query) ?></strong>".
                    </div>
                <?php else: ?>
                    <?php foreach ($filtered_articles as $art): ?>
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition">
                        <div class="h-44 w-full overflow-hidden bg-gray-100">
                            <img src="<?= $art['img'] ?>" alt="<?= htmlspecialchars($art['title']) ?>" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                        </div>
                        <div class="p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md"><?= $art['tag'] ?></span>
                                    <span class="text-[10px] text-gray-400"><?= $art['date'] ?></span>
                                </div>
                                <h3 class="text-sm font-bold text-gray-800 mb-2 leading-snug line-clamp-2"><?= $art['title'] ?></h3>
                                <p class="text-xs text-gray-500 line-clamp-3 mb-4 leading-relaxed"><?= $art['desc'] ?></p>
                            </div>
                            <div class="flex justify-between items-center border-t pt-3 text-[11px]">
                                <span class="text-gray-400"><i class="fa fa-user-shield mr-1"></i><?= $art['author'] ?></span>
                                <?php $isInternal = !str_starts_with($art['url'], 'http'); ?>
                                <a href="<?= $art['url'] ?>" <?= !$isInternal ? 'target="_blank"' : '' ?> class="text-blue-600 font-bold hover:underline">Baca →</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- ======================== SECTION: VIDEO ======================== -->
        <section id="video" class="section-anchor mb-10">
            <h2 class="text-base font-bold text-gray-800 mb-4">🎥 Video Edukasi</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <iframe class="w-full h-48" src="https://www.youtube.com/embed/OQU48FWHlkM" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <div class="p-4">
                        <h4 class="text-sm font-bold text-gray-800 mb-1">Mengenali Kekerasan Seksual Berdasarkan Gender</h4>
                        <span class="text-[11px] text-gray-400"><i class="fa fa-clock mr-1"></i>8:32 Menit</span>
                    </div>
                </div>
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm">
                    <iframe class="w-full h-48" src="https://www.youtube.com/embed/Pz797_hCeRc" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <div class="p-4">
                        <h4 class="text-sm font-bold text-gray-800 mb-1">Cara Memberikan Dukungan Psikologis ke Korban</h4>
                        <span class="text-[11px] text-gray-400"><i class="fa fa-clock mr-1"></i>12:15 Menit</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================== SECTION: FAQ ======================== -->
        <section id="faq" class="section-anchor mb-10">
            <h2 class="text-base font-bold text-gray-800 mb-2">❓ Pertanyaan yang Sering Diajukan (FAQ)</h2>
            <p class="text-xs text-gray-400 mb-6">Temukan jawaban atas pertanyaan umum seputar sistem pelaporan SIRAKELIKA.</p>

            <div class="space-y-3">
                <?php foreach ($faqs as $i => $faq): ?>
                <div class="faq-item bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden <?= $i === 0 ? 'open' : '' ?>">
                    <button onclick="toggleFAQ(this)" class="w-full flex justify-between items-center p-5 text-left">
                        <span class="text-sm font-semibold text-gray-800 pr-4"><?= $faq['q'] ?></span>
                        <span class="faq-icon text-blue-600 text-lg font-bold flex-shrink-0">+</span>
                    </button>
                    <div class="faq-answer px-5 pb-5">
                        <p class="text-xs text-gray-600 leading-relaxed border-t border-gray-50 pt-4"><?= $faq['a'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

    <section id="kontak" class="section-anchor mb-10">
            <h2 class="text-base font-bold text-gray-800 mb-2">📞 Kontak & Hubungan Bantuan</h2>
            <p class="text-xs text-gray-400 mb-6">Butuh bantuan atau layanan konsultasi? Silakan hubungi tim SIRAKELIKA langsung melalui saluran di bawah ini.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex flex-col gap-3">
                        <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600">
                            <i class="fa fa-envelope text-base"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 mb-1">Layanan Email Support</h4>
                            <p class="text-xs text-gray-500 leading-relaxed">Kirimkan pertanyaan, kendala teknis akun, atau berkas pengaduan Anda secara formal. Tim kami akan merespon dalam waktu maksimal 1x24 jam.</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50">
                        <!-- Tombol Email dengan Auto-Fill Subjek dan Isi Pesan -->
                    <!-- Tombol Email Terbaru menggunakan sirakelika@gmail.com -->
                    <a href="mailto:sirakelika@gmail.com?subject=Kendala%20Sistem%20SIRAKELIKA&body=Halo%20Tim%20Support%20SIRAKELIKA,%0A%0Asaya%20membutuhkan%20bantuan%20terkait..." 
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition w-full justify-center">
                        <i class="fa fa-paper-plane"></i> Hubungi via Email
                    </a>
                    </div>
                </div>

                <div class="bg-white border border-gray-100 rounded-2xl shadow-sm p-6 flex flex-col justify-between hover:shadow-md transition">
                    <div class="flex flex-col gap-3">
                        <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-600">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800 mb-1">Hotline WhatsApp (Fast Response)</h4>
                            <p class="text-xs text-gray-500 leading-relaxed">Hubungi pusat bantuan tanggap cepat untuk konsultasi langsung via chat teks bersama operator posko pengaduan kampus pada jam kerja operasional.</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-50">
                        <a href="https://wa.me/628123456789?text=Halo%20Admin%20SIRAKELIKA,%20saya%20butuh%20bantuan." target="_blank" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition w-full justify-center">
                            <i class="fa-brands fa-whatsapp text-sm"></i> Hubungi via WhatsApp
                        </a>
                    </div>
                </div>

            </div>
        </section>
        

            

    </main>
</div>

<script>
    function toggleFAQ(btn) {
        const item = btn.closest('.faq-item');
        const isOpen = item.classList.contains('open');
        document.querySelectorAll('.faq-item').forEach(el => el.classList.remove('open'));
        if (!isOpen) item.classList.add('open');
    }

    function scrollToSection(id) {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth' });
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active-tab', 'bg-blue-600', 'text-white'));
        event.currentTarget.classList.add('active-tab');
    }
</script>

</body>
</html>