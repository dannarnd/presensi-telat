# 🏫 Sistem Presensi Keterlambatan Siswa
**SMKN 5 Telkom Banda Aceh**

Sistem Presensi Keterlambatan Siswa adalah aplikasi berbasis web yang dirancang khusus untuk mempermudah pencatatan, pemantauan, dan penanganan siswa yang terlambat. Sistem ini menggantikan proses pencatatan manual menjadi digital, memberikan laporan real-time untuk Kepala Sekolah, Wali Kelas, Guru Piket, dan Guru Bimbingan Konseling (BK).

---

## 🚀 Teknologi yang Digunakan

Aplikasi ini dibangun dengan teknologi modern untuk memastikan performa yang cepat dan pengalaman pengguna yang interaktif:

* **Backend:** [Laravel v13.x](https://laravel.com/) (Framework PHP)
* **Frontend:** [Vue.js 3](https://vuejs.org/) (Composition API) via [Inertia.js](https://inertiajs.com/)
* **Styling:** [Tailwind CSS](https://tailwindcss.com/)
* **Database:** MySQL / MariaDB

---

## 📦 Prasyarat (Requirements)

Sebelum menginstal aplikasi ini di komputer baru, pastikan perangkat Anda sudah terinstal:
1. **PHP** (Minimal versi 8.2+)
2. **Composer** (Untuk dependensi PHP/Laravel)
3. **Node.js & npm** (Minimal versi 18+ untuk Vue dan Vite)
4. **MySQL/MariaDB** (Bisa menggunakan aplikasi seperti **Laragon**, **XAMPP**, atau **MAMP**)

---

## 🛠️ Cara Instalasi di Komputer Baru

Ikuti langkah-langkah berikut secara berurutan untuk memasang aplikasi di komputer baru:

**1. Clone Repository (atau ekstrak folder project)**
```bash
git clone https://github.com/username/presensi-telat.git
cd presensi-telat
```

**2. Install Dependensi Backend (Laravel)**
```bash
composer install
```

**3. Install Dependensi Frontend (Vue.js & Tailwind)**
```bash
npm install
```

**4. Siapkan File Konfigurasi Lingkungan (.env)**
Copy file `.env.example` menjadi `.env`.
* Jika di Windows/Powershell: `cp .env.example .env`
* Jika di Linux/Mac: `cp .env.example .env`

**5. Atur Database di file `.env`**
Buka file `.env` dan sesuaikan nama databasenya. Contoh jika menggunakan Laragon/XAMPP (pastikan database `presensi_telat` sudah dibuat di phpMyAdmin / HeidiSQL):
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=presensi_telat
DB_USERNAME=root
DB_PASSWORD=
```

**6. Generate Application Key**
```bash
php artisan key:generate
```

**7. Migrasi Database (dan Seed data awal)**
Perintah ini akan membuat struktur tabel di database Anda.
```bash
php artisan migrate
```
*(Catatan: Jika ada file seeder untuk data dummy atau akun awal, gunakan `php artisan migrate --seed`)*

---

## 💻 Cara Menjalankan Aplikasi (Development)

Untuk menjalankan aplikasi ini di mode pengembangan (development), Anda harus membuka **dua terminal/command prompt** di folder proyek (`presensi-telat`) secara bersamaan.

**Terminal 1 (Menjalankan server backend Laravel):**
```bash
php artisan serve
```
*Ini akan menjalankan server di `http://127.0.0.1:8000`*

**Terminal 2 (Menjalankan server frontend Vue.js/Vite):**
```bash
npm run dev
```
*Ini penting agar Vue.js dan TailwindCSS di-compile secara real-time.*

**Buka Aplikasi:**
Buka browser dan akses alamat: 👉 **http://localhost:8000**

---

## 🏗️ Persiapan untuk Deploy (Production)

Jika Anda ingin mengunggah (deploy) aplikasi ini ke hosting atau server production (misal cPanel/VPS), jangan gunakan `npm run dev`. Anda perlu mem-build aset frontend terlebih dahulu:

```bash
npm run build
```

Setelah perintah tersebut selesai, folder `public/build` akan terisi dengan aset-aset Vue/CSS yang sudah dikompresi. Barulah file-file siap untuk di-upload ke server.
