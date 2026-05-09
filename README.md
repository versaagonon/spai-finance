<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Cara Menjalankan Aplikasi Web Secara Lokal

Berikut adalah panduan langkah demi langkah untuk menjalankan aplikasi Laravel ini di komputer lokal Anda.

### 1. Prerequisites (Prasyarat)

Sebelum memulai, pastikan Anda telah menginstal perangkat lunak berikut:

- **PHP** versi 8.1 atau lebih tinggi
- **Composer** (pengelola paket PHP)
- **Node.js** versi 18 atau lebih tinggi
- **npm** (manajer paket Node.js)
- Ekstensi PHP yang diperlukan:
  - SQLite (pdo_sqlite)
  - OpenSSL
  - PDO
  - Mbstring
  - Tokenizer
  - XML

### 2. Install Dependencies

Instal dependensi PHP menggunakan Composer:

```bash
composer install
```

### 3. Generate Application Key

Buat kunci aplikasi Laravel:

```bash
php artisan key:generate
```

### 4. Konfigurasi Database

Aplikasi ini menggunakan **SQLite** sebagai database. Pastikan jalan kan xampp 

```xampp
hidup kan di xampp APACHE dan MYSQL
```

### 5. Jalankan Server Pengembangan

Jalankan server pengembangan Laravel:

```bash
php artisan serve
```

Biasanya server akan berjalan di `http://127.0.0.1:8000`.

### 5. Akun Default (Jika Menggunakan Seeder)

akun login :
usrspai=admin
pswspai=spaifinance123
pin=112233



### Perintah Tambahan

Berikut adalah beberapa perintah Laravel yang berguna:

| Perintah | Deskripsi |
|----------|-----------|
| `php artisan cache:clear` | Hapus cache |
| `php artisan config:clear` | Hapus konfigurasi cache |
| `php artisan route:clear` | Hapus route cache |
| `php artisan view:clear` | Hapus view cache |
| `php artisan optimize` | Optimasi aplikasi |

### Troubleshooting

Jika Anda mengalami masalah:

1. **Error database**: Pastikan file `.env` sudah dikonfigurasi dengan benar dan path database sudah sesuai.
2. **Error permission**: Pastikan folder `storage` dan `bootstrap/cache` memiliki izin tulis.
3. **Error Node.js**: Pastikan Node.js sudah terinstal dengan benar dan coba jalankan `npm install` ulang.

### Informasi Tambahan

- **Framework**: Laravel 10+
- **Database**: SQLite
- **Frontend**: Vite + Blade Templates
- **Authentication**: Custom dengan PIN

## Lisensi

Laravel adalah perangkat lunak open-source yang dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).
