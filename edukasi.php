<?php
// 1. DATA MASTER ARTIKEL (Lengkap sesuai screenshot Figma temanmu)
$all_articles = [
    [
        "id" => 1,
        "img" => "https://picsum.photos/id/119/600/400", // Gambar Macbook/Edukasi
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
        "img" => "https://picsum.photos/id/64/600/400", // Gambar Orang Berpikir/Psikologi
        "tag" => "Psikologi & Kesehatan",
        "slug" => "psikologi-kesehatan",
        "date" => "06 Mei 2026",
        "views" => "842",
        "title" => "Dampak Psikologis: Perundungan pada mahasiswa dan Cara Pemulihannya",
        "desc" => "Perundungan dapat meninggalkan luka psikologis yang mendalam. Artikel ini membahas gejala trauma dan langkah pemulihan yang efektif bagi korban perundungan di lingkungan akademis.",
        "author" => "Dr. Psikologi",
        "url" => "https://p2ptm.kemkes.go.id/infografis-p2ptm/stress/apa-saja-dampak-bullying-bagi-kesehatan-mental"
    ],
    [
        "id" => 3,
        "img" => "https://picsum.photos/id/524/600/400", // Gambar Diskusi/Verbal
        "tag" => "Kekerasan Verbal",
        "slug" => "kekerasan-verbal",
        "date" => "04 Mei 2026",
        "views" => "915",
        "title" => "Mengenali Kekerasan Verbal: Dari candaan hingga ujaran kebencian",
        "desc" => "Kekerasan verbal sering dianggap sepele. Kenali bentuk-bentuknya agar Anda bisa melindungi diri dan orang sekitar dari dampak buruk manipulasi kata-kata.",
        "author" => "Satgas Kampus",
        "url" => "https://www.halodoc.com/artikel/verbal-abuse-pengertian-ciri-ciri-dan-dampaknya?srsltid=AfmBOoqjjDk5Cdv2j7eizKHJQQDlm6pCinhvKkzZTdzI30dt9IR_eNi1"
    ],
    [
        "id" => 4,
        "img" => "https://picsum.photos/id/367/600/400", // Gambar Menulis/Panduan
        "tag" => "Panduan",
        "slug" => "panduan",
        "date" => "02 Mei 2026",
        "views" => "1500",
        "title" => "Panduan Lengkap: Cara melaporkan kekerasan di lingkungan kampus",
        "desc" => "Langkah-langkah praktis yang perlu dilakukan saat menghadapi atau menyaksikan kekerasan di kampus, termasuk cara menggunakan sistem pelaporan SIRAKELIKA.",
        "author" => "Tim SIRAKELIKA",
        "url" => "laporan.php"
    ]
];

// 2. LOGIKA PHP UNTUK PENCARIAN & FILTER KATEGORI (DISEMPURNAKAN AGAR TIDAK HILANG)
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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edukasi & Informasi Kekerasan Kampus - SIRAKELIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                <a href="laporan.php" class="nav-link">
                    <span class="nav-text">Laporan Saya</span>
                </a>

                <div class="nav-group">PENGELOLAAN</div>
                <a href="#" class="nav-link">
                    <span class="nav-text">Manajemen Kasus</span>
                </a>
                <a href="edukasi.php" class="nav-link active">
                    <span class="nav-text">Edukasi & Informasi</span>
                </a>
                <a href="kenali.php" class="nav-link">
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

        <main class="main-content" style="padding-top: 24px;">
            
            <div class="bg-gradient-to-r from-blue-600 to-sky-500 rounded-2xl p-6 md:p-8 text-white mb-8 shadow-md">
                <h1 class="text-xl md:text-2xl font-bold mb-2">📚 Edukasi & Informasi Kekerasan Kampus</h1>
                <p class="text-blue-50 max-w-2xl text-xs md:text-sm mb-6 opacity-90">Pelajari berbagai bentuk kekerasan, hak-hak Anda, serta cara melaporkan dan mendapatkan bantuan untuk menciptakan lingkungan kampus yang aman.</p>
                
                <form action="edukasi.php" method="GET" class="flex gap-2 max-w-md">
                    <input type="text" name="search" value="<?= htmlspecialchars($search_query) ?>" placeholder="Cari artikel, panduan..." class="w-full px-4 py-2.5 rounded-xl text-gray-800 text-sm focus:outline-none">
                    <button type="submit" class="bg-blue-950 hover:bg-black px-6 py-2.5 rounded-xl text-sm font-medium transition">Cari</button>
                </form>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                    <div class="bg-blue-50 text-blue-600 p-2.5 rounded-lg text-sm"><i class="fa fa-file-alt"></i></div>
                    <div><span class="text-lg font-bold text-gray-800 block">4</span><span class="text-[10px] text-gray-400">Total Artikel</span></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                    <div class="bg-emerald-50 text-emerald-600 p-2.5 rounded-lg text-sm"><i class="fa fa-video"></i></div>
                    <div><span class="text-lg font-bold text-gray-800 block">2</span><span class="text-[10px] text-gray-400">Video Edukasi</span></div>
                </div>
                <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                    <div class="bg-orange-50 text-orange-600 p-2.5 rounded-lg text-sm"><i class="fa fa-arrow-down"></i></div>
                    <div><span class="text-lg font-bold text-gray-800 block">3</span><span class="text-[10px] text-gray-400">Panduan & Infografis</span></div>
                </div>
              
            </div>

            <div class="flex overflow-x-auto gap-2 mb-8 pb-1">
                <a href="edukasi.php?kategori=semua" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap <?= $category_filter == 'semua' ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 border' ?>">Semua</a>
                <a href="edukasi.php?kategori=hukum-hak" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap <?= $category_filter == 'hukum-hak' ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 border' ?>">Hukum & Hak</a>
                <a href="edukasi.php?kategori=kekerasan-verbal" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap <?= $category_filter == 'kekerasan-verbal' ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 border' ?>">Kekerasan Verbal</a>
                <a href="edukasi.php?kategori=psikologi-kesehatan" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap <?= $category_filter == 'psikologi-kesehatan' ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 border' ?>">Psikologi & Kesehatan</a>
                <a href="edukasi.php?kategori=panduan" class="px-4 py-2 rounded-full text-xs font-bold transition whitespace-nowrap <?= $category_filter == 'panduan' ? 'bg-blue-600 text-white' : 'bg-white text-gray-500 border' ?>">Panduan</a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-6">
                    <h2 class="text-md font-bold text-gray-800">Artikel Terbaru</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php if (empty($filtered_articles)): ?>
                            <div class="col-span-2 bg-white p-8 text-center text-xs text-gray-400 rounded-xl border">Materi tidak ditemukan.</div>
                        <?php else: ?>
                            <?php foreach ($filtered_articles as $art): ?>
                                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                                    <div class="h-44 w-full overflow-hidden bg-gray-100">
                                        <img src="<?= $art['img'] ?>" alt="Foto Edukasi" class="w-full h-full object-cover">
                                    </div>
                                    <div class="p-5 flex-1 flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-center text-[10px] text-gray-400 mb-2">
                                                <span class="font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md"><?= $art['tag'] ?></span>
                                                <span><?= $art['date'] ?></span>
                                            </div>
                                            <h3 class="text-sm font-bold text-gray-800 mb-2 line-clamp-2"><?= $art['title'] ?></h3>
                                            <p class="text-xs text-gray-500 line-clamp-3 mb-4"><?= $art['desc'] ?></p>
                                        </div>
                                        <div class="flex justify-between items-center border-t pt-3 text-[11px]">
    <span class="text-gray-400 font-medium"><i class="fa fa-user-shield text-[10px] mr-1"></i> <?= $art['author'] ?></span>
    <a href="<?= $art['url'] ?>" target="_blank" class="text-blue-600 font-bold hover:underline">Baca Selengkapnya →</a>
</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- 🎥 BAGIAN VIDEO EDUKASI (PUTAR DI WEB + FIX LAYOUT URUTAN) -->
                    <div class="pt-6">
                        <h2 class="text-md font-bold text-gray-800 mb-4">Video Edukasi</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            
                            <!-- Video 1: Seri Edukasi Permen PPKS Kemendikbud -->
                            <div class="bg-white border rounded-2xl overflow-hidden shadow-sm flex flex-col justify-between">
                                <iframe class="w-full h-40" src="https://www.youtube.com/embed/OQU48FWHlkM" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>                      
                                <div class="p-3">
                                    <h4 class="text-xs font-bold text-gray-800">Mengenali Kekerasan Seksual Berdasarkan Gender</h4>
                                    <span class="text-[10px] text-gray-400 block mt-2">▶ 8:32 Menit</span>
                                </div>
                            </div>

                            <!-- Video 2: Pendampingan & Dukungan Korban (Narasi TV) -->
                            <div class="bg-white border rounded-2xl overflow-hidden shadow-sm flex flex-col justify-between">
                                <iframe class="w-full h-40" src="https://www.youtube.com/embed/Pz797_hCeRc" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>                      
                                <div class="p-3">
                                    <h4 class="text-xs font-bold text-gray-800">Cara Memberikan Dukungan Psikologis ke Korban</h4>
                                    <span class="text-[10px] text-gray-400 block mt-2">▶ 12:15 Menit</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div> 


            </div>

            <div class="mt-12">
                <h2 class="text-md font-bold text-gray-800 mb-4">Materi & Panduan Unduhan</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white border rounded-2xl overflow-hidden shadow-sm flex flex-col justify-between">
                        <img src="https://picsum.photos/id/24/500/300" class="w-full h-32 object-cover">
                        <div class="p-4">
                            <h4 class="font-bold text-gray-800 text-xs mb-1">Buku Panduan Pelaporan Kekerasan</h4>
                            <p class="text-[11px] text-gray-400 mb-4">Langkah demi langkah pelaporan resmi.</p>
                            <div class="flex justify-between items-center text-[10px] border-t pt-3"><span class="text-gray-400 font-medium">PDF • 2.4 MB</span><button class="bg-blue-600 text-white px-3 py-1 rounded-md font-bold">Unduh</button></div>
                        </div>
                    </div>

                    <div class="bg-white border rounded-2xl overflow-hidden shadow-sm flex flex-col justify-between">
                        <img src="https://picsum.photos/id/180/500/300" class="w-full h-32 object-cover">
                        <div class="p-4">
                            <h4 class="font-bold text-gray-800 text-xs mb-1">Infografis: Jenis-jenis Kekerasan</h4>
                            <p class="text-[11px] text-gray-400 mb-4">Visual spektrum kekerasan verbal hingga fisik.</p>
                            <div class="flex justify-between items-center text-[10px] border-t pt-3"><span class="text-gray-400 font-medium">PNG • 1.1 MB</span><button class="bg-blue-600 text-white px-3 py-1 rounded-md font-bold">Unduh</button></div>
                        </div>
                    </div>

                    <div class="bg-white border rounded-2xl overflow-hidden shadow-sm flex flex-col justify-between">
                        <img src="https://picsum.photos/id/42/500/300" class="w-full h-32 object-cover">
                        <div class="p-4">
                            <h4 class="font-bold text-gray-800 text-xs mb-1">Poster: Kampus Bebas Perundungan</h4>
                            <p class="text-[11px] text-gray-400 mb-4">Materi kampanye siap cetak di mading.</p>
                            <div class="flex justify-between items-center text-[10px] border-t pt-3"><span class="text-gray-400 font-medium">PDF • 3.8 MB</span><button class="bg-blue-600 text-white px-3 py-1 rounded-md font-bold">Unduh</button></div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>
</html>

