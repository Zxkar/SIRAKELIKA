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
            $to = "sirakelika@gmail.com";
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
        "icon" => "fa-scale-balanced",
        "color" => "indigo",
        "time" => "6 menit",
        "tag" => "Hukum & Hak",
        "slug" => "hukum-hak",
        "date" => "08 Mei 2026",
        "views" => "1200",
        "title" => "Memahami Permendikbudristek No. 30/2021: Hak dan Perlindungan Korban Kekerasan Seksual di Kampus",
        "desc" => "Peraturan Menteri ini menjadi landasan hukum penting yang mengatur mekanisme pencegahan dan penanganan kekerasan seksual di lingkungan perguruan tinggi. Pelajari hak-hak yang dimiliki korban.",
        "author" => "Tim Hukum SIRAKELIKA",
        "url" => "https://repositori.kemendikdasmen.go.id/24916/1/Buku%20Saku%20Permendikbudristek%20No.%2030%20Tahun%202021.pdf",
        "content" => [
            "Permendikbudristek No. 30 Tahun 2021 lahir karena kekerasan seksual di kampus sering tidak tertangani dengan baik. Aturan ini mewajibkan setiap perguruan tinggi membentuk Satuan Tugas Pencegahan dan Penanganan Kekerasan Seksual (Satgas PPKS) yang bertugas menerima, memeriksa, dan menindaklanjuti laporan.",
            "Sebagai korban, kamu punya beberapa hak yang dijamin: didampingi selama proses pelaporan, identitas dirahasiakan, tidak dipaksa berdamai atau bermediasi dengan pelaku, serta mendapat akses ke layanan kesehatan dan psikologis bila dibutuhkan.",
            "Proses penanganan juga tidak boleh menyalahkan korban (victim blaming) dan harus berjalan tanpa diskriminasi, baik pelapor maupun korban adalah mahasiswa, dosen, atau tenaga kependidikan.",
            "Melalui SIRAKELIKA, kamu bisa melaporkan kejadian secara umum (anonim) maupun khusus, dan seluruh hak di atas tetap berlaku terlepas dari metode pelaporan yang kamu pilih."
        ],
        "extra" => ["type" => "source", "label" => "Lihat dokumen resmi (PDF)", "icon" => "fa-file-pdf"]
    ],
    [
        "id" => 2,
        "icon" => "fa-heart-pulse",
        "color" => "rose",
        "time" => "5 menit",
        "tag" => "Psikologi & Kesehatan",
        "slug" => "psikologi-kesehatan",
        "date" => "06 Mei 2026",
        "views" => "842",
        "title" => "Dampak Psikologis: Perundungan pada Mahasiswa dan Cara Pemulihannya",
        "desc" => "Perundungan dapat meninggalkan luka psikologis yang mendalam. Artikel ini membahas gejala trauma dan langkah pemulihan yang efektif bagi korban perundungan di lingkungan akademis.",
        "author" => "Dr. Psikologi",
        "url" => "https://shariajournal.com/index.php/IERJ/article/view/745",
        "content" => [
            "Perundungan tidak selalu meninggalkan bekas fisik, tapi dampaknya pada psikologis bisa berlangsung lama. Korban sering mengalami kecemasan berlebih, sulit berkonsentrasi saat kuliah, gangguan tidur, hingga menarik diri dari pergaulan.",
            "Pada beberapa kasus, rasa percaya diri menurun drastis dan muncul perasaan malu atau bersalah — padahal kesalahan ada pada pelaku, bukan pada korban.",
            "Langkah pemulihan bisa dimulai dari hal sederhana: mengakui bahwa apa yang dialami itu nyata dan valid, bercerita pada orang yang dipercaya, lalu jika diperlukan menemui psikolog atau konselor kampus untuk pendampingan lebih lanjut.",
            "Kamu tidak harus menghadapi ini sendirian. Tim SIRAKELIKA menyediakan layanan konsultasi bagi mahasiswa yang ingin bercerita atau butuh arahan sebelum memutuskan melapor."
        ],
        "extra" => ["type" => "action", "label" => "Hubungi Tim Konsultasi", "target" => "kontak", "icon" => "fa-comments"]
    ],
    [
        "id" => 3,
        "icon" => "fa-comment-slash",
        "color" => "amber",
        "time" => "3 menit",
        "tag" => "Kekerasan Verbal",
        "slug" => "kekerasan-verbal",
        "date" => "04 Mei 2026",
        "views" => "915",
        "title" => "Mengenali Kekerasan Verbal: Dari Candaan hingga Ujaran Kebencian",
        "desc" => "Kekerasan verbal sering dianggap sepele. Kenali bentuk-bentuknya agar Anda bisa melindungi diri dan orang sekitar dari dampak buruk manipulasi kata-kata.",
        "author" => "Satgas Kampus",
        "url" => "https://www.halodoc.com/artikel/verbal-abuse-pengertian-ciri-ciri-dan-dampaknya",
        "content" => [
            "Kekerasan verbal sering dianggap 'cuma bercanda', padahal mencakup ejekan berulang, hinaan di depan umum, ancaman, hingga sindiran yang menjatuhkan mental seseorang.",
            "Ciri yang membedakannya dari candaan biasa adalah pola yang berulang, terasa menyakitkan bagi penerimanya, dan biasanya ada ketimpangan posisi atau kekuasaan antara pelaku dan korban.",
            "Kalau dibiarkan, kekerasan verbal bisa meningkat menjadi bentuk kekerasan lain — mulai dari intimidasi sosial hingga kekerasan fisik.",
            "Yang bisa kamu lakukan: tegaskan batasanmu, simpan bukti seperti tangkapan layar chat atau rekaman jika ada, dan jangan ragu melapor lewat SIRAKELIKA meski tanpa bukti fisik — laporan tetap akan ditinjau oleh tim terkait."
        ],
        "extra" => ["type" => "source", "label" => "Baca referensi tambahan", "icon" => "fa-arrow-up-right-from-square"]
    ],
    [
        "id" => 4,
        "icon" => "fa-route",
        "color" => "emerald",
        "time" => "4 menit",
        "tag" => "Panduan",
        "slug" => "panduan",
        "date" => "02 Mei 2026",
        "views" => "1500",
        "title" => "Panduan Lengkap: Cara Melaporkan Kekerasan di Lingkungan Kampus",
        "desc" => "Langkah-langkah praktis yang perlu dilakukan saat menghadapi atau menyaksikan kekerasan di kampus, termasuk cara menggunakan sistem pelaporan SIRAKELIKA.",
        "author" => "Tim SIRAKELIKA",
        "url" => "kenali.php",
        "content" => [
            "Melaporkan kejadian kekerasan lewat SIRAKELIKA hanya butuh beberapa langkah sederhana."
        ],
        "steps" => [
            "Masuk ke menu Dashboard, lalu pilih jenis pelaporan: Umum (anonim) atau Khusus (mencantumkan identitas).",
            "Isi kronologi kejadian selengkap mungkin: jenis kekerasan, waktu, dan lokasi kejadian.",
            "Lampirkan bukti pendukung jika ada, seperti foto, dokumen, atau tangkapan layar percakapan.",
            "Tinjau kembali isian laporan sebelum mengirim — pastikan tidak ada bagian penting yang kosong.",
            "Setelah terkirim, pantau perkembangannya kapan saja lewat menu \"Laporan Saya\"."
        ],
        "content_after" => [
            "Kamu bisa berhenti dan lanjut mengisi nanti — laporan tidak harus selesai dalam sekali duduk."
        ],
        "extra" => ["type" => "action", "label" => "Coba \"Kenali Situasi Anda\"", "url" => "kenali.php", "icon" => "fa-compass"]
    ]
];

// Artikel dengan jumlah pembaca terbanyak, ditonjolkan di bagian atas
$featured_article = $all_articles[0];
foreach ($all_articles as $art_cek) {
    if ((int)$art_cek['views'] > (int)$featured_article['views']) {
        $featured_article = $art_cek;
    }
}

// Data artikel untuk modal baca-di-tempat (dipakai oleh JavaScript)
$articles_json = json_encode($all_articles, JSON_UNESCAPED_UNICODE);

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
    <style>a { text-decoration: none !important; }</style>
    <script>
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
        .article-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 60;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .article-modal-overlay.open { display: flex; }
        .article-modal-box {
            background: #fff;
            border-radius: 20px;
            max-width: 640px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            padding: 32px;
            position: relative;
        }
        .article-modal-close {
            position: absolute;
            top: 16px;
            right: 16px;
            width: 32px;
            height: 32px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s ease;
            border: none;
            cursor: pointer;
        }
        .article-modal-close:hover { background: #e2e8f0; }
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
                <p>Sebelum melapor, kenali dulu apa yang sedang Anda atau orang di sekitar Anda alami. Semua artikel dan video di bawah bisa dibaca atau ditonton dalam hitungan menit.</p>
                <a href="kenali.php" class="inline-flex items-center gap-2 bg-white text-blue-700 text-xs font-bold px-4 py-2.5 rounded-xl mt-4 hover:bg-blue-50 transition">
                    <i class="fa fa-compass"></i> Belum tahu harus mulai dari mana? Coba "Kenali Situasi Anda"
                </a>
            </div>
        </section>

        <!-- NAVIGASI SECTION (Tab) -->
        <div class="flex gap-2 mb-8 flex-wrap">
            <button onclick="scrollToSection('artikel')" class="tab-btn active-tab px-5 py-2 rounded-full text-xs font-bold border border-blue-600 transition">📰 Artikel</button>
            <button onclick="scrollToSection('video')" class="tab-btn px-5 py-2 rounded-full text-xs font-bold bg-white border text-gray-500 transition hover:bg-blue-50">🎥 Video</button>
            <button onclick="scrollToSection('faq')" class="tab-btn px-5 py-2 rounded-full text-xs font-bold bg-white border text-gray-500 transition hover:bg-blue-50">❓ FAQ</button>
            <button onclick="scrollToSection('kontak')" class="tab-btn px-5 py-2 rounded-full text-xs font-bold bg-white border text-gray-500 transition hover:bg-blue-50">📞 Kontak</button>
        </div>

        <!-- ======================== SECTION: KENALI JENIS KEKERASAN (Flip Cards) ======================== -->
        <section id="artikel" class="section-anchor mb-10">
            <div class="mb-5">
                <h2 class="text-base font-bold text-gray-800 mb-1">🧠 Kenali Jenis Kekerasan</h2>
                <p class="text-xs text-gray-400">Tap kartu untuk lihat apa yang bisa kamu lakukan.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">

                <!-- Kartu 1: Kekerasan Fisik -->
                <div class="flip-card" onclick="this.classList.toggle('flipped')">
                    <div class="flip-inner">
                        <div class="flip-front" style="background: linear-gradient(135deg,#fee2e2,#fecaca); border:1.5px solid #fca5a5;">
                            <div class="flip-icon" style="background:#ef4444;color:#fff;"><i class="fa fa-hand-fist"></i></div>
                            <div class="flip-label" style="color:#b91c1c;">Kekerasan Fisik</div>
                            <div class="flip-hint">Tap untuk lihat tanda &amp; cara lapor →</div>
                        </div>
                        <div class="flip-back" style="background:#ef4444;">
                            <div class="flip-back-title">Tanda-tanda:</div>
                            <ul class="flip-back-list">
                                <li>Dipukul, ditampar, atau dianiaya</li>
                                <li>Diancam akan disakiti jika melawan</li>
                                <li>Merasa tubuhmu tidak aman di dekat pelaku</li>
                            </ul>
                            <a href="laporan.php" class="flip-back-btn">Lapor Sekarang <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Kartu 2: Kekerasan Verbal -->
                <div class="flip-card" onclick="this.classList.toggle('flipped')">
                    <div class="flip-inner">
                        <div class="flip-front" style="background:linear-gradient(135deg,#fef9c3,#fde68a);border:1.5px solid #fcd34d;">
                            <div class="flip-icon" style="background:#f59e0b;color:#fff;"><i class="fa fa-comment-slash"></i></div>
                            <div class="flip-label" style="color:#92400e;">Kekerasan Verbal</div>
                            <div class="flip-hint">Tap untuk lihat tanda &amp; cara lapor →</div>
                        </div>
                        <div class="flip-back" style="background:#f59e0b;">
                            <div class="flip-back-title">Tanda-tanda:</div>
                            <ul class="flip-back-list">
                                <li>Dihina, dicaci, atau diremehkan di depan umum</li>
                                <li>Kata-katanya terus terngiang dan menyakiti</li>
                                <li>Diancam lewat ucapan agar kamu diam dan patuh</li>
                            </ul>
                            <a href="laporan.php" class="flip-back-btn">Lapor Sekarang <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Kartu 3: Pelecehan Seksual -->
                <div class="flip-card" onclick="this.classList.toggle('flipped')">
                    <div class="flip-inner">
                        <div class="flip-front" style="background:linear-gradient(135deg,#fce7f3,#fbcfe8);border:1.5px solid #f9a8d4;">
                            <div class="flip-icon" style="background:#ec4899;color:#fff;"><i class="fa fa-triangle-exclamation"></i></div>
                            <div class="flip-label" style="color:#9d174d;">Pelecehan Seksual</div>
                            <div class="flip-hint">Tap untuk lihat tanda &amp; cara lapor →</div>
                        </div>
                        <div class="flip-back" style="background:#ec4899;">
                            <div class="flip-back-title">Tanda-tanda:</div>
                            <ul class="flip-back-list">
                                <li>Disentuh, diraba, atau dicium tanpa persetujuan</li>
                                <li>Dikirim konten atau ucapan seksual yang tidak kamu minta</li>
                                <li>Merasa jijik, takut, dan ingin menghilang dari hadapan pelaku</li>
                            </ul>
                            <a href="laporan.php" class="flip-back-btn">Lapor Sekarang <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Kartu 4: Perundungan -->
                <div class="flip-card" onclick="this.classList.toggle('flipped')">
                    <div class="flip-inner">
                        <div class="flip-front" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe);border:1.5px solid #c4b5fd;">
                            <div class="flip-icon" style="background:#7c3aed;color:#fff;"><i class="fa fa-users-slash"></i></div>
                            <div class="flip-label" style="color:#4c1d95;">Perundungan</div>
                            <div class="flip-hint">Tap untuk lihat tanda &amp; cara lapor →</div>
                        </div>
                        <div class="flip-back" style="background:#7c3aed;">
                            <div class="flip-back-title">Tanda-tanda:</div>
                            <ul class="flip-back-list">
                                <li>Dijauhi, dikucilkan, dan sengaja diabaikan banyak orang</li>
                                <li>Dipermalukan terus-menerus sampai kamu malu untuk hadir</li>
                                <li>Takut masuk kampus karena tahu pelaku ada di sana</li>
                            </ul>
                            <a href="laporan.php" class="flip-back-btn">Lapor Sekarang <i class="fa fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ======================== QUIZ SINGKAT ======================== -->
            <div class="mb-10">
                <h2 class="text-base font-bold text-gray-800 mb-1">🤔 Apakah Ini Termasuk Kekerasan?</h2>
                <p class="text-xs text-gray-400 mb-5">Pilih salah satu skenario — kamu akan langsung tahu jawabannya.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3" id="quizGrid">

                    <?php
                    $quiz_items = [
                        ["q" => "Teman satu kelas sering memanggil nama kamu dengan sebutan yang merendahkan, dan tertawa saat kamu marah.", "verdict" => "ya", "label" => "Ya, ini kekerasan verbal.", "explain" => "Ejekan berulang yang disengaja dan menyakitkan termasuk kekerasan verbal — bukan candaan biasa."],
                        ["q" => "Dosen memberimu nilai rendah karena kamu sering absen, dan itu membuatmu kesal.", "verdict" => "bukan", "label" => "Bukan kekerasan.", "explain" => "Sanksi akademis sesuai aturan adalah kebijakan yang sah, bukan bentuk kekerasan."],
                        ["q" => "Seseorang terus-menerus mengirim pesan tidak senonoh padamu meski kamu sudah minta berhenti.", "verdict" => "ya", "label" => "Ya, ini pelecehan seksual.", "explain" => "Pesan seksual yang tidak diinginkan dan dilanjutkan meski ditolak adalah pelecehan seksual, termasuk secara online."],
                        ["q" => "Kamu dikeluarkan dari grup chat tugas kelompok tanpa penjelasan dan kemudian diabaikan.", "verdict" => "ya", "label" => "Ya, ini bisa termasuk perundungan.", "explain" => "Pengucilan sosial yang disengaja adalah bentuk perundungan — tidak harus ada kontak fisik untuk disebut kekerasan."],
                        ["q" => "Senior memaksamu mengerjakan tugasnya dengan ancaman akan mempermalukanmu di depan angkatan jika menolak.", "verdict" => "ya", "label" => "Ya, ini kekerasan psikologis.", "explain" => "Ancaman dan paksaan menggunakan rasa takut adalah bentuk kekerasan psikologis, terlepas dari ada tidaknya kontak fisik."],
                        ["q" => "Temanmu tidak sengaja menyenggol bahumu saat berpapasan di koridor yang sempit.", "verdict" => "bukan", "label" => "Bukan kekerasan.", "explain" => "Kontak fisik yang tidak disengaja dan tidak berulang bukan termasuk kekerasan. Niat dan pola perilaku adalah faktor penting."],
                        ["q" => "Seseorang mengambil foto kamu diam-diam lalu menyebarkannya ke grup tanpa izin disertai komentar merendahkan.", "verdict" => "ya", "label" => "Ya, ini kekerasan digital.", "explain" => "Menyebarkan foto tanpa izin disertai konten yang merendahkan adalah bentuk kekerasan berbasis gender online (KBGO)."],
                        ["q" => "Dosenmu sering memberikan komentar soal penampilan fisikmu di depan kelas, bukan soal akademik.", "verdict" => "ya", "label" => "Ya, ini termasuk pelecehan.", "explain" => "Komentar berulang tentang tubuh atau penampilan oleh figur yang punya otoritas adalah bentuk pelecehan yang tidak pantas."],
                        ["q" => "Kamu tidak lolos seleksi kepanitiaan karena kurang pengalaman, dan kamu merasa diperlakukan tidak adil.", "verdict" => "bukan", "label" => "Bukan kekerasan.", "explain" => "Keputusan seleksi berdasarkan kriteria objektif bukan termasuk kekerasan, meskipun terasa mengecewakan."],
                        ["q" => "Teman satu kosmu meminjam barang tanpa izin berulang kali dan marah saat kamu memintanya kembali.", "verdict" => "ya", "label" => "Ya, ini bisa termasuk intimidasi.", "explain" => "Perilaku yang berulang disertai respons agresif saat dikonfrontasi adalah pola intimidasi yang perlu diwaspadai."],
                    ];
                    foreach ($quiz_items as $qi => $quiz):
                    ?>
                    <div class="quiz-card bg-white border border-gray-100 rounded-2xl shadow-sm p-5 cursor-pointer select-none" onclick="revealQuiz(this)" data-verdict="<?= $quiz['verdict'] ?>">
                        <div class="quiz-question">
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-2 block">Skenario <?= $qi+1 ?></span>
                            <p class="text-sm text-gray-700 leading-relaxed font-medium">"<?= $quiz['q'] ?>"</p>
                            <div class="quiz-tap-hint mt-3 text-xs text-blue-500 font-semibold flex items-center gap-1.5">
                                <i class="fa fa-hand-pointer text-[11px]"></i> Tap untuk lihat jawaban
                            </div>
                        </div>
                        <div class="quiz-answer hidden mt-4 pt-4 border-t border-gray-100">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-bold mb-2 <?= $quiz['verdict'] === 'ya' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' ?>">
                                <i class="fa <?= $quiz['verdict'] === 'ya' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i>
                                <?= $quiz['label'] ?>
                            </div>
                            <p class="text-xs text-gray-500 leading-relaxed"><?= $quiz['explain'] ?></p>
                            <?php if ($quiz['verdict'] === 'ya'): ?>
                            <a href="laporan.php" class="inline-flex items-center gap-1.5 mt-3 text-xs font-bold text-red-600 hover:underline">
                                <i class="fa fa-flag"></i> Buat laporan →
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <!-- ======================== CARA LAPOR: STEPS VISUAL ======================== -->
            <div class="mb-2">
                <h2 class="text-base font-bold text-gray-800 mb-1">📋 Cara Lapor di SIRAKELIKA</h2>
                <p class="text-xs text-gray-400 mb-5">5 langkah, selesai dalam hitungan menit.</p>
                <div class="flex flex-col gap-3">
                    <?php
                    $steps = [
                        ["icon" => "fa-right-to-bracket", "color" => "blue",    "num" => "1", "title" => "Login ke Dashboard",         "desc" => "Masuk menggunakan akun yang telah kamu buat."],
                        ["icon" => "fa-list-check",        "color" => "indigo",  "num" => "2", "title" => "Pilih Jenis Pelaporan",       "desc" => "Laporan Umum (anonim) atau Laporan Khusus (dengan identitas)."],
                        ["icon" => "fa-pen-to-square",     "color" => "violet",  "num" => "3", "title" => "Isi Kronologi Kejadian",      "desc" => "Ceritakan apa yang terjadi: jenis kekerasan, waktu, dan lokasi."],
                        ["icon" => "fa-paperclip",         "color" => "purple",  "num" => "4", "title" => "Lampirkan Bukti ", "desc" => " Upload foto, dokumen,audio, atau video."],
                        ["icon" => "fa-paper-plane",       "color" => "fuchsia", "num" => "5", "title" => "Kirim & Pantau Status",       "desc" => "Setelah terkirim, pantau perkembangannya di menu \"Laporan Saya\"."],
                    ];
                    foreach ($steps as $step):
                    ?>
                    <div class="flex items-start gap-4 bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
                        <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-<?= $step['color'] ?>-50 text-<?= $step['color'] ?>-600 flex items-center justify-center text-sm font-black"><?= $step['num'] ?></div>
                        <div class="flex items-center gap-3 flex-1">
                            <div class="w-8 h-8 rounded-lg bg-<?= $step['color'] ?>-50 text-<?= $step['color'] ?>-500 flex items-center justify-center text-sm flex-shrink-0">
                                <i class="fa <?= $step['icon'] ?>"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-800"><?= $step['title'] ?></div>
                                <div class="text-xs text-gray-500 mt-0.5"><?= $step['desc'] ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mt-4 flex justify-end">
                    <a href="laporan.php" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-5 py-2.5 rounded-xl transition">
                        <i class="fa fa-flag"></i> Buat Laporan Sekarang
                    </a>
                </div>
            </div>

        </section>

        <style>
            /* Flip Cards — fade swap (aman di semua browser) */
            .flip-card { position: relative; height: 190px; cursor: pointer; border-radius: 18px; overflow: hidden; }
            .flip-front, .flip-back { position: absolute; inset: 0; border-radius: 18px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px; text-align: center; transition: opacity 0.3s ease, transform 0.3s ease; }
            .flip-back { color: #fff; align-items: flex-start; justify-content: flex-start; padding: 18px; opacity: 0; transform: scale(0.96); pointer-events: none; }
            .flip-card.flipped .flip-front { opacity: 0; transform: scale(0.96); pointer-events: none; }
            .flip-card.flipped .flip-back { opacity: 1; transform: scale(1); pointer-events: auto; }
            .flip-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; margin-bottom: 10px; }
            .flip-label { font-size: 13px; font-weight: 800; margin-bottom: 6px; }
            .flip-hint { font-size: 10px; opacity: .6; }
            .flip-back-title { font-size: 10px; font-weight: 800; opacity: .8; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 6px; }
            .flip-back-list { list-style: none; padding: 0; margin: 0 0 10px; width: 100%; }
            .flip-back-list li { font-size: 10px; line-height: 1.6; padding: 2px 0; opacity: .95; }
            .flip-back-list li::before { content: "✓ "; font-weight: 800; }
            .flip-back-btn { display: inline-flex; align-items: center; gap: 4px; background: rgba(255,255,255,.2); color: #fff; font-size: 10px; font-weight: 800; padding: 5px 12px; border-radius: 20px; text-decoration: none; margin-top: auto; }
            .flip-back-btn:hover { background: rgba(255,255,255,.35); }
            /* Quiz */
            .quiz-card:hover { border-color: #bfdbfe; }
            .quiz-answer.hidden { display: none; }
            .quiz-answer { display: block; }
        </style>

        <script>
            // Pastikan semua flip card mulai dari sisi depan
            document.querySelectorAll('.flip-card').forEach(c => c.classList.remove('flipped'));

            function revealQuiz(card) {
                const hint = card.querySelector('.quiz-tap-hint');
                const answer = card.querySelector('.quiz-answer');
                if (answer.classList.contains('hidden')) {
                    answer.classList.remove('hidden');
                    if (hint) hint.style.display = 'none';
                    card.style.cursor = 'default';
                }
            }
        </script>

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
                    <!-- Tombol Email yang Dijamin 100% Terbuka di Laptop lewat Browser -->
                    <a href="https://mail.google.com/mail/?view=cm&fs=1&to=sirakelika@gmail.com&su=Kendala%20Sistem%20SIRAKELIKA&body=Halo%20Tim%20Support%20SIRAKELIKA,%0A%0Asaya%20membutuhkan%20bantuan%20terkait..." 
                    target="_blank" 
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
                    <!-- Tautan WA Resmi yang Sudah Dibersihkan dari Spasi dan Karakter Spesial -->
                    <a href="https://wa.me/6285179614915?text=Halo%20Admin%20SIRAKELIKA,%20saya%20butuh%20bantuan%20terkait..." 
                    target="_blank" 
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition w-full justify-center">
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