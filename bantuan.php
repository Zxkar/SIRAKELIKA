<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Pengaduan & Bantuan - SIRAKELIKA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-[#F8FAFC] font-sans flex items-center justify-center min-h-screen p-4 my-8">

    <div class="max-w-md w-full bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden animate-fade-in">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-blue-600 to-sky-500 p-6 text-white text-center">
            <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3 shadow-inner">
                <i class="fa fa-file-alt text-xl"></i>
            </div>
            <h1 class="text-lg font-bold">Formulir Layanan Bantuan</h1>
            <p class="text-[11px] text-blue-100 mt-1 opacity-90">Kerahasiaan identitas dan data Anda sepenuhnya terjamin.</p>
        </div>

        <!-- Formulir Pengaduan -->
        <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            
            <!-- Input Nama -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Nama <span class="text-gray-400 font-normal text-[10px]">(Opsional)</span></label>
                <input type="text" name="nama" placeholder="Masukkan nama Anda atau kosongkan" 
                       class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
            </div>

            <!-- Input Email -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required placeholder="contoh@mahasiswa.ac.id" 
                       class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
            </div>

            <!-- Input NIM -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">NIM <span class="text-red-500">*</span></label>
                <input type="text" name="nim" required placeholder="Masukkan NIM Anda" 
                       class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
            </div>

            <!-- Input Kolom Deskripsi -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Apa yang bisa kami bantu? <span class="text-red-500">*</span></label>
                <textarea name="pesan" required rows="4" placeholder="Ceritakan situasi atau kendala yang sedang Anda hadapi..." 
                          class="w-full px-3.5 py-2 border border-gray-200 rounded-xl text-xs focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition resize-none"></textarea>
            </div>

            <!-- Input Lampiran File (DIBUAT JELAS OPSIONAL) -->
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">Lampiran <span class="text-gray-400 font-normal text-[10px]">(Opsional - Bukti/Dokumen pendukung)</span></label>
                <div class="border border-dashed border-gray-200 rounded-xl p-3 bg-gray-50 flex items-center justify-between">
                    <input type="file" name="lampiran" id="file-upload" class="hidden">
                    <label for="file-upload" class="bg-white border border-gray-200 px-3 py-1.5 rounded-lg text-[11px] font-semibold text-gray-600 cursor-pointer shadow-sm hover:bg-gray-100 transition">
                        Pilih File
                    </label>
                    <span id="file-name" class="text-[11px] text-gray-400 truncate max-w-[200px]">Belum ada file dipilih</span>
                </div>
            </div>

            <!-- Tombol Kirim -->
            <div class="pt-2">
                <button type="submit" name="kirim" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-xl text-xs shadow-md shadow-blue-200 transition flex items-center justify-center gap-2">
                    <i class="fa fa-paper-plane text-[10px]"></i> Kirim Pengaduan
                </button>
            </div>

        </form>

        <!-- Tombol Kembali ke Halaman Utama -->
        <div class="px-6 pb-5 pt-2 bg-gray-50 border-t flex justify-center">
            <a href="edukasi.php" class="text-xs font-semibold text-gray-500 hover:text-gray-800 transition flex items-center gap-1">
                <i class="fa fa-arrow-left text-[10px]"></i> Kembali ke Artikel Edukasi
            </a>
        </div>

    </div>

    <!-- JavaScript Pendukung (Untuk memunculkan nama file yang dipilih) -->
    <script>
        const fileInput = document.getElementById('file-upload');
        const fileNameSpan = document.getElementById('file-name');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileNameSpan.textContent = this.files[0].name;
                fileNameSpan.classList.remove('text-gray-400');
                fileNameSpan.classList.add('text-gray-700', 'font-medium');
            } else {
                fileNameSpan.textContent = 'Belum ada file dipilih';
            }
        });
    </script>

</body>

</html>