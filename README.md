# SIRAKELIKA
### Sistem Pelaporan Kekerasan di Lingkungan Kampus
Institut Teknologi Bacharuddin Jusuf Habibie — 2026

---

## 👥 Tim Pengembang

| Nama | NIM | Peran | Branch |
|---|---|---|---|
| Alexander Zulfakar | 241011064 | Ketua / Git Master | `develop` + review semua |
| Christiano Samuel Sapan | 241011063 | Developer | `feature/auth` + `feature/admin-panel` |
| Muhammad Alif Al Fathir | 241011075 | Developer | `feature/pelaporan` + `feature/status-riwayat` |
| Abrar Basri | 241011048 | Developer | `feature/dashboard` + `feature/faq-kontak` |
| Gad | 241011015 | Developer | `feature/edukasi-informasi` + `feature/kenali-situasi` |

---

## 🛠️ Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP (native, migrasi ke Laravel di tahap final)
- **Database:** MySQL
- **Server lokal:** XAMPP

---

## ⚙️ Cara Setup di Laptop (Wajib Dibaca Sebelum Mulai)

### 1. Install XAMPP
Download di https://www.apachefriends.org — pilih versi PHP 8.x

### 2. Clone Repository
Buka **Git Bash** atau **terminal**, jalankan:
```bash
git clone https://github.com/Zxkar/sirakelika.git
```

Lalu pindahkan folder hasil clone ke:
```
C:\xampp\htdocs\sirakelika
```

### 3. Import Database
- Buka phpMyAdmin → `localhost/phpmyadmin`
- Buka tab **SQL**
- Copy-paste isi file `database/sirakelika.sql`
- Klik **Go**

### 4. Konfigurasi Koneksi
Buka file `config/database.php`, sesuaikan jika perlu:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');   // default XAMPP
define('DB_PASS', '');       // default XAMPP kosong
define('DB_NAME', 'sirakelika');
```

### 5. Jalankan
Buka browser → akses `http://localhost/sirakelika`

---

## 🌿 Aturan Branch & Git

### Struktur Branch
```
main          ← Kode final & stabil (JANGAN langsung edit)
develop       ← Integrasi semua fitur (hanya ketua yang merge ke sini)
feature/...   ← Branch kerja masing-masing anggota
```

### Alur Kerja Harian

**Setiap mulai kerja, lakukan ini dulu:**
```bash
# Pastikan branch kamu up-to-date
git checkout feature/NAMA-BRANCH-KAMU
git pull origin develop
```

**Setelah selesai coding:**
```bash
git add .
git commit -m "feat: deskripsi singkat apa yang dikerjakan"
git push origin feature/NAMA-BRANCH-KAMU
```

**Lalu buat Pull Request di GitHub:**
1. Buka GitHub → repo SIRAKELIKA
2. Klik tab **Pull Requests** → **New Pull Request**
3. Base: `develop` ← Compare: `feature/branch-kamu`
4. Tulis deskripsi singkat → klik **Create Pull Request**
5. **Tunggu review dari ketua (Alexander)**

### Aturan Wajib
- ❌ Dilarang push langsung ke `main` atau `develop`
- ❌ Dilarang merge PR sendiri tanpa review ketua
- ✅ Selalu pull dari `develop` sebelum mulai kerja
- ✅ Satu commit = satu perubahan yang jelas

---

## 📝 Format Pesan Commit

```
feat: tambah form login mahasiswa
fix: perbaiki validasi email register
style: rapikan tampilan dashboard
db: tambah kolom foto_profil di tabel mahasiswa
docs: update README
```

---

## 📁 Struktur Folder

```
sirakelika/
├── config/
│   └── database.php       ← Koneksi MySQL
├── assets/
│   ├── css/               ← File stylesheet
│   ├── js/                ← File javascript
│   └── img/               ← Gambar & logo
├── auth/
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── mahasiswa/
│   ├── dashboard.php
│   ├── buat-laporan.php
│   ├── status-laporan.php
│   ├── riwayat.php
│   └── konsultasi.php
├── admin/
│   ├── dashboard.php
│   ├── kelola-laporan.php
│   └── kelola-pengguna.php
├── edukasi/
│   └── index.php
├── faq/
│   └── index.php
├── uploads/               ← File bukti laporan (jangan di-commit ke GitHub)
├── database/
│   └── sirakelika.sql     ← Script database
└── index.php              ← Halaman utama
```

---

## ❓ Troubleshooting Umum

| Masalah | Solusi |
|---|---|
| Halaman tidak muncul | Pastikan XAMPP (Apache + MySQL) sudah Start |
| Database error | Cek `config/database.php`, pastikan DB_NAME = `sirakelika` |
| Gambar tidak muncul | Pastikan folder `uploads/` ada dan permission writable |
| Git conflict | Hubungi ketua (Alexander) jangan diselesaikan sendiri |

---

## 📞 Kontak Tim
Hubungi ketua **Alexander Zulfakar** jika ada masalah teknis terkait GitHub atau integrasi antar fitur.
