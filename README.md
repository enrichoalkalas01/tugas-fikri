# EAD Collection — remake project Fikri

Dua pengalaman digital untuk presentasi portofolio: **EAD Supply** (commerce) dan **EAD Stay** (hospitality). Dibangun ulang dari modul praktikum dengan tampilan, interaksi, dan penyimpanan demo baru. Ini bukan toko/hotel operasional.

## Buka project

Aktifkan **Apache** di XAMPP, lalu buka:

- Showcase: http://localhost/projects/fikri/
- EAD Supply: http://localhost/projects/fikri/modul_1/home.html
- EAD Stay: http://localhost/projects/fikri/modul_3/index.php

Gunakan Ctrl+F5 jika browser menyimpan styling lama. Project tidak membutuhkan MySQL, Composer, npm, API key, atau koneksi internet untuk aset frontend. Jangan membuka HTML dengan `file://`: katalog menggunakan endpoint PHP.

Kebutuhan: PHP 8.2, PDO SQLite, mbstring, Apache yang menerapkan `.htaccess`, dan folder `storage/` yang dapat ditulis proses PHP. SQLite `storage/demo.sqlite` dibuat otomatis saat pertama kali dibutuhkan.

## Alur demonstrasi

### EAD Supply

1. Jelajahi home dan kategori.
2. Di Koleksi, coba pencarian, filter kategori, ketersediaan, dan urutan harga.
3. Buka detail produk, tambah ke Bag, lalu ubah jumlah.
4. Lanjut ke checkout dan klik **Gunakan data demo**.
5. Simpan pesanan: nomor referensi dan total dari server ditampilkan. Tidak ada pembayaran/pengiriman nyata.

Keranjang disimpan di localStorage. Pesanan tersimpan di tabel `orders` dalam SQLite. Harga berasal dari katalog server, bukan nilai yang dikirim browser. Satu token checkout menghasilkan satu pesanan, sehingga pengiriman ulang tidak membuat duplikat.

### EAD Stay

1. Pilih tanggal dan jumlah tamu, atau pilih salah satu kamar.
2. Atur tipe kamar dan layanan tambahan. Ringkasan harga berubah langsung.
3. Isi data sintetis, lalu konfirmasi reservasi demo.
4. Coba refresh halaman My reservations: data tetap tersedia.
5. Buka detail atau batalkan reservasi melalui tombol Cancel reservation.

Harga: Standard/The Essential $90 per malam; Deluxe/The Slow Stay $90 per malam; Luxury/The Grand Retreat $200 per malam. Dua tarif $90 mempertahankan angka kartu kamar awal; penamaan kamar kedua diperjelas. Breakfast dan room service masing-masing $20 **sekali per masa menginap**, sesuai angka layanan pada form awal. Server menghitung durasi dari check-in/check-out, bukan dari angka total atau durasi yang dikirim browser. Rentang menginap 1–30 malam; kapasitas kamar demo 1/2/4 tamu.

Reservasi disimpan pada tabel `bookings`, dibatasi ke identitas cookie acak browser (30 hari), dengan CSRF, query berparameter, escaping output, dan status pembatalan. Menghapus cookie membuat reservasi lama tidak lagi tampil pada browser itu. Sistem ini bukan autentikasi akun pelanggan.

## Apa yang berubah

- Halaman showcase editorial untuk membuka kedua demo.
- Identitas toko: charcoal, off-white, lime; visual hero konsep AI dan ilustrasi kategori CSS.
- Identitas hotel: cream, brown, serif; foto kamar lokal dari project awal.
- Semua halaman publik didesain ulang, dengan dialog native, state kosong/error, navigasi mobile, fokus keyboard, dan reduced motion.
- Form lama yang tidak menyimpan data diganti alur nyata menggunakan SQLite.
- Harga, durasi, extra, validasi, dan handler booking lama yang rentan error dibetulkan.
- Baris katalog mouse M238 dan Lenovo AIO yang tertukar diselaraskan. Harga dan spesifikasi tetap **data demo**, bukan penawaran terkini.

## Struktur utama

- `index.html`: showcase.
- `modul_1/*.html`: home, koleksi, profil toko.
- `modul_3/*.php`: home hotel, booking, daftar reservasi, layout.
- `assets/css/site.css`: fondasi serta dua tema responsif.
- `assets/js/store.js`: katalog, cart, dialog, checkout, dan error handling.
- `assets/js/hotel.js`: navigasi, live summary, validasi antarmuka, konfirmasi pembatalan.
- `app/bootstrap.php`: katalog, sesi, identitas browser, dan SQLite.
- `api.php`: API katalog dan penyimpanan pesanan.
- `ASSETS.md`: provenance aset serta prompt hero AI.

Bootstrap/jQuery dan CSS lama masih ada tetapi tidak dimuat oleh UI baru. `modul_3/tester.php` dialihkan ke booking agar handler duplikat lama tidak digunakan.

## Verifikasi lokal

Chromium/Playwright: tujuh halaman pada lebar 320, 390, 768, dan 1440 px; pencarian/filter/sort; dialog detail dan cart; kuantitas/persistensi cart; checkout server; total booking; pembatalan; isolasi reservasi antarbrowser; CSRF; validasi tanggal/kapasitas; idempotensi pesanan; harga server; kegagalan jaringan; proteksi HTTP terhadap database/kode server. Lint PHP dan sintaks JavaScript juga diperiksa. Data sintetis pengujian dibersihkan setelah selesai.

Dockerfile disesuaikan dengan ekstensi SQLite/mbstring, tetapi build Docker **belum diuji** karena Docker tidak tersedia di lingkungan ini. Jalur XAMPP di atas telah diuji. Pada Linux/Docker, pastikan izin tulis volume `storage/` sesuai user Apache; jangan memakai chmod 777 sebagai jalan pintas.

## Batasan untuk publikasi

Siap didemonstrasikan secara lokal, bukan aplikasi transaksi produksi. Belum ada payment gateway, pengiriman email, inventori real-time, panel admin, autentikasi akun, atau proteksi spam untuk deployment publik. Sebelum hosting, tambahkan kontrol akses/rate limiting, HTTPS, kebijakan privasi/retensi, dan audit keamanan. Jangan commit database yang berisi data pengunjung; `.gitignore` sudah mengecualikannya. `.htaccess` melarang akses HTTP ke folder `app/` dan `storage/`.

Aset lama memiliki asal lisensi yang belum terdokumentasi. Verifikasi hak penggunaan foto kamar sebelum publikasi portofolio publik. Detail aset ada di `ASSETS.md`.

Backup sebelum remake: `C:\xampp\tmp\fikri-before-remake-20260924-193424` (di luar web root).
