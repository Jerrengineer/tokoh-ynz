# Toko Kelontong YNZ - Sistem Kasir (POS) & Manajemen Toko

Aplikasi manajemen toko kelontong dan sistem kasir (*Point of Sale*) berbasis web yang ringan, cepat, dan responsif. Dirancang menggunakan PHP Native dan MySQL tanpa ketergantungan framework berat, memudahkan instalasi dan pemeliharaan untuk operasional toko retail dan kelontong sehari-hari.

---

## Fitur Utama

- **Dashboard Finansial & Bisnis Real-Time**:
  - Informasi pemasukan harian, pengeluaran harian, dan kalkulasi keuntungan bersih harian (*gross profit* penjualan dikurangi beban pengeluaran).
  - Ringkasan statistik toko: total jenis produk, total kuantitas stok barang, dan akumulasi jumlah transaksi.
  - Peringatan dini stok menipis (*low stock warning*) berdasarkan batas minimum stok.
- **Analisis Performa Produk**:
  - Pelacakan produk paling laku (*best sellers*) untuk perencanaan stok ulang.
  - Pelacakan produk yang jarang terjual (*slow-moving items*) untuk evaluasi inventaris.
- **Point of Sale (POS) Kasir Cepat**:
  - Antarmuka kasir responsif dengan pencarian produk langsung (*live search*).
  - Keranjang belanja interaktif dengan kalkulasi otomatis subtotal, total harga, dan jumlah uang kembalian.
  - Validasi stok otomatis saat transaksi diproses untuk mencegah penjualan barang habis.
- **Cetak Nota & Struk Belanja**:
  - Format nota siap cetak yang telah dioptimalkan untuk printer termal kasir ukuran 80mm maupun printer biasa.
- **Manajemen Inventaris & Produk**:
  - Pengelolaan data produk lengkap: kode/SKU barcode, nama, kategori, harga modal, harga jual, stok fisik, dan batas minimum stok.
  - Fitur pencarian dan filter produk berdasarkan kategori serta status stok rendah.
- **Manajemen Kategori Produk**:
  - Pengelompokan barang untuk mempermudah navigasi dan pengelolaan barang dagangan.
- **Pencatatan Pengeluaran Toko (*Expenses*)**:
  - Pencatatan dan monitoring beban operasional (belanja stok tambahan, listrik, air, transportasi, dan operasional lainnya) dilengkapi filter rentang tanggal.
- **Riwayat & Detail Transaksi**:
  - Pencatatan riwayat setiap transaksi beserta kasir yang bertugas.
  - Filter riwayat transaksi berdasarkan nomor faktur/invoice dan tanggal.
- **Manajemen Hak Akses Multi-User**:
  - **Admin**: Akses penuh ke seluruh fitur sistem, termasuk laporan keuangan, keuntungan, manajemen data master produk, dan pengeluaran toko.
  - **Karyawan / Kasir**: Akses terfokus pada transaksi kasir (POS), pencarian katalog produk, riwayat transaksi, dan cetak nota.
  - Keamanan autentikasi kata sandi menggunakan enkripsi `password_hash()` dan sanitasi input.

---

## Teknologi yang Digunakan

- **Bahasa Pemrograman**: PHP (Versi 7.4+ / PHP 8.x)
- **Database**: MySQL / MariaDB (Driver `mysqli` dengan *prepared statements*)
- **Frontend**: HTML5, Vanilla JavaScript (Modern Fetch API, DOM manipulation)
- **Desain UI**: Custom CSS3 Modern (CSS Variables, Flexbox, CSS Grid, Media Queries untuk cetak nota)
- **Web Server**: Apache (XAMPP / WampServer / LAMP Stack)

---

## Struktur Folder Proyek

```text
tokoh-ynz/
├── api/
│   ├── get_products.php         # API internal pencarian & daftar produk POS
│   └── process_transaction.php  # API pemrosesan transaksi penjualan POS
├── css/
│   └── style.css                # Desain sistem & stylesheet utama
├── db/
│   └── schema.sql               # Skema tabel database dan data awal (seeder)
├── categories.php               # Modul manajemen kategori produk
├── config.example.php           # Contoh template konfigurasi database
├── config.php                   # Konfigurasi database & fungsi helper sistem
├── dashboard.php                # Halaman dashboard analitik & ringkasan toko
├── expenses.php                 # Modul pencatatan pengeluaran toko
├── footer.php                   # Template footer layout
├── header.php                   # Template header layout & navigasi
├── index.php                    # Gerbang utama / auto-redirect
├── login.php                    # Halaman autentikasi masuk sistem
├── logout.php                   # Proses keluar & penghancuran sesi pengguna
├── pos.php                      # Antarmuka kasir / Point of Sale
├── products.php                 # Modul manajemen data produk & stok
├── run_migration.php            # Skrip pembantu migrasi skema database
├── transactions.php             # Modul riwayat transaksi penjualan
├── transaction_detail.php       # Tampilan detail transaksi & cetak nota
├── .gitignore                   # Konfigurasi file yang diabaikan oleh Git
└── README.md                    # Dokumentasi lengkap proyek
```

---

## Persyaratan Sistem

Pastikan lingkungan server lokal atau hosting Anda memenuhi syarat berikut:
- **Web Server**: Apache (direkomendasikan melalui XAMPP / Laragon / LAMP)
- **PHP**: Versi 7.4 atau lebih baru (PHP 8.0 - 8.3 didukung penuh)
- **Ekstensi PHP**: `mysqli`, `session`, `json`
- **Database**: MySQL 5.7+ atau MariaDB 10.3+
- **Browser**: Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari versi terbaru

---

## Panduan Instalasi & Konfigurasi

### 1. Salin Proyek ke Direktori Web Server
Letakkan folder proyek ini ke dalam folder root web server Anda:
- **XAMPP (Windows)**: `C:\xampp\htdocs\tokoh-ynz` atau `D:\xampp\htdocs\tokoh-ynz`
- **Laragon**: `C:\laragon\www\tokoh-ynz`
- **Linux (Apache)**: `/var/www/html/tokoh-ynz`

### 2. Konfigurasi Database
Buka file `config.php` (atau salin dari `config.example.php`) dan sesuaikan parameter koneksi database Anda:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tokoh_ynz');
```

### 3. Mengimpor Skema Database
Buat database baru bernama `tokoh_ynz`, kemudian impor file SQL `db/schema.sql`:

#### Opsi A: Melalui phpMyAdmin
1. Buka browser dan akses `http://localhost/phpmyadmin`.
2. Buat database baru bernama **`tokoh_ynz`** dengan collation `utf8mb4_general_ci`.
3. Pilih database `tokoh_ynz`, lalu klik tab **Import**.
4. Pilih file `db/schema.sql` yang ada di dalam direktori proyek ini, lalu klik tombol **Go / Impor**.

#### Opsi B: Melalui Terminal / Command Prompt
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS tokoh_ynz;"
mysql -u root -p tokoh_ynz < db/schema.sql
```

---

## Menjalankan Aplikasi

1. Pastikan modul **Apache** dan **MySQL** pada kontrol panel XAMPP/server Anda sudah dalam status **Running**.
2. Buka browser web dan kunjungi alamat:
   ```text
   http://localhost/tokoh-ynz/
   ```
3. Anda akan diarahkan ke halaman login.

---

## Akun Demo untuk Pengujian

Database telah dilengkapi dengan akun bawaan (*seed users*) untuk kebutuhan pengujian:

| Role / Peran | Username | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Admin (Pemilik)** | `admin` | `admin123` | Akses penuh: Dashboard Keuangan, Produk, Kategori, Pengeluaran, Kasir, Riwayat |
| **Karyawan (Kasir)** | `karyawan` | `karyawan123` | Akses kasir: Point of Sale (POS), Katalog Produk, Riwayat Transaksi & Cetak Nota |

> **Catatan Keamanan**: Untuk lingkungan produksi yang dapat diakses publik, sangat disarankan untuk segera mengganti kata sandi default dan menyesuaikan kredensial database.

---

## Lisensi & Kontribusi

Proyek ini dikembangkan untuk kebutuhan operasional toko retail dan kelontong. Kontribusi, pelaporan isu, dan saran pengembangan selalu terbuka.
