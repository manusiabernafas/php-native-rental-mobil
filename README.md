# Sistem Rental Mobil (PHP Native + MySQL)

Sistem Rental Mobil berbasis PHP Native dan MySQL yang siap pakai untuk usaha sewa mobil skala kecil sampai menengah. Semua proses inti ada di satu dashboard: data armada, data pelanggan, transaksi sewa dengan atau tanpa sopir, pengembalian mobil dengan denda keterlambatan otomatis, invoice siap cetak, sampai laporan pendapatan per periode.

Kodenya ditulis tanpa framework sehingga mudah dipelajari dan dimodifikasi, cocok untuk tugas kuliah, skripsi, belajar PHP, maupun dipakai langsung oleh pemilik rental yang ingin meninggalkan pencatatan di buku atau Excel. Cukup XAMPP, import satu file SQL, dan aplikasi langsung jalan tanpa perlu Composer atau koneksi internet.

## Fitur

- Login multi-user dengan 2 role: Admin dan Petugas
- Dashboard: mobil tersedia, sewa berjalan, terlambat kembali, pendapatan bulan ini
- Grafik pendapatan 6 bulan terakhir tanpa library tambahan
- Daftar mobil yang terlambat kembali beserta jumlah hari telat
- CRUD Data Mobil lengkap dengan upload foto, transmisi, bahan bakar, dan status (Tersedia/Disewa/Servis)
- CRUD Data Pelanggan dengan validasi NIK 16 digit, No. SIM, dan riwayat sewa per pelanggan
- Transaksi sewa dengan kalkulasi biaya otomatis (harga x hari + biaya sopir)
- Opsi sewa dengan sopir, tarif sopir per hari bisa diatur
- Pembayaran DP / lunas dan pelacakan sisa tagihan
- Pengembalian mobil dengan denda keterlambatan otomatis (persentase bisa diatur)
- Pilihan kondisi mobil saat kembali: siap disewa atau masuk servis
- Pembatalan transaksi yang otomatis mengembalikan status mobil
- Invoice rapi dan siap cetak (print-friendly)
- Laporan pendapatan per periode: total, denda, piutang, dan performa per mobil
- Ekspor laporan ke CSV (bisa dibuka di Excel) dan cetak laporan
- Pengaturan profil usaha, tarif sopir, persentase denda, dan manajemen pengguna
- Keamanan: PDO prepared statement, password_hash, proteksi CSRF, validasi upload
- Tampilan bersih dan responsif (desktop, tablet, HP), ikon tersimpan lokal sehingga bisa jalan offline
- Tanpa framework dan tanpa Composer, mudah dipelajari dan dimodifikasi

## Instalasi

**Kebutuhan:** PHP 8.0 atau lebih baru dan MySQL/MariaDB. Paling mudah pakai XAMPP atau Laragon.

1. Ekstrak file ZIP lalu pindahkan folder `rental-mobil` ke folder web server:
   - XAMPP: `C:\xampp\htdocs\rental-mobil`
   - Laragon: `C:\laragon\www\rental-mobil`

2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel.

3. Buka `http://localhost/phpmyadmin`, klik tab **Import**, pilih file `database/rental_mobil.sql`, lalu klik **Import**. Database `rental_mobil` beserta data contohnya akan dibuat otomatis.

   Atau lewat terminal:
   ```bash
   mysql -u root -p < database/rental_mobil.sql
   ```

4. Jika user/password MySQL kamu bukan bawaan XAMPP (`root` tanpa password), sesuaikan file `config/database.php`:
   ```php
   return [
       'host'     => 'localhost',
       'port'     => 3306,
       'database' => 'rental_mobil',
       'username' => 'root',
       'password' => '',
   ];
   ```

5. Buka aplikasi di browser:
   ```
   http://localhost/rental-mobil
   ```

6. Login dengan akun demo:
   - Admin: `admin` / `admin123`
   - Petugas: `petugas` / `admin123`

   Segera ganti password lewat menu **Pengaturan** setelah login.

**Tanpa XAMPP?** Bisa juga pakai server bawaan PHP dari dalam folder project:
```bash
php -S localhost:8000
```
lalu buka `http://localhost:8000`.

**Data contoh yang sudah tersedia:** 8 mobil, 5 pelanggan, dan riwayat transaksi 6 bulan terakhir sehingga grafik dan laporan langsung terisi.

## Struktur Folder

```
rental-mobil/
├── index.php              # Router utama (index.php?page=...)
├── config/database.php    # Koneksi database
├── includes/              # Helper, layout header & footer
├── pages/                 # Halaman: dashboard, mobil, pelanggan, sewa, laporan, pengaturan
├── assets/                # CSS, JavaScript, folder upload foto mobil
└── database/rental_mobil.sql
```

---
Source code ini dibagikan gratis oleh **Papicode** — https://papicode.id
Temukan source code gratis lainnya dan tutorial pemrograman di Papicode.
