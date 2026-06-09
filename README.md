# RongsokHub

RongsokHub lahir dari pengalaman saya mengikuti kegiatan pengumpulan rongsok di sekitar Jakarta Utara, tepatnya di Sunter. Dari kegiatan itu, saya melihat bahwa proses pengumpulan, penawaran, dan penjemputan masih banyak dilakukan secara manual. Akhirnya saya membuat sistem ini untuk membantu menghubungkan warga dan pengepul dengan lebih mudah. Saya berperan dalam menganalisis kebutuhan, merancang aliran data, serta merancang antarmuka dan fitur yang mendukung pencatatan item, pengelolaan pengepul, dan pelacakan status permintaan.

RongsokHub adalah sistem web untuk menghubungkan warga yang ingin menjual atau menukar barang rongsok dengan pengepul. Aplikasi ini dibangun dengan PHP, MySQL, dan Tailwind CSS.

## Fitur Utama

### Umum

- Halaman beranda dengan statistik dan listing barang rongsok terbaru
- Login dan pendaftaran pengguna
- Sistem role-based access: `warga`, `pengepul`, dan `admin`

### Warga

- Tambah barang rongsok lengkap dengan foto
- Melihat daftar barang yang sudah dimasukkan
- Melihat daftar pengepul terdaftar
- Mengajukan penjemputan barang ke pengepul
- Melihat riwayat pengajuan

### Pengepul

- Melihat marketplace barang rongsok
- Mengajukan penjemputan via marketplace
- Melihat pengajuan masuk (status pending, accepted, pickup, completed)
- Melihat riwayat transaksi
- Dashboard status permintaan

### Admin

- Kelola pengguna (`warga` dan `pengepul`)
- Kelola pengepul dan profil pengepul
- Kelola kategori barang rongsok
- Kelola pengajuan penjemputan
- Laporan statistik dasar

## Struktur Proyek

- `index.php` - Halaman utama dan login
- `login.php` - Halaman login
- `register.php` - Halaman pendaftaran pengguna
- `logout.php` - Logout pengguna
- `config/database.php` - Konfigurasi koneksi database
- `includes/auth.php` - Fungsi otentikasi dan pemeriksaan role
- `admin/` - Halaman backend admin
- `warga/` - Halaman untuk pengguna warga
- `pengepul/` - Halaman untuk pengguna pengepul
- `assets/` - File statis, termasuk CSS dan upload foto
- `.sql` - Skrip pembuatan database awal

## Persyaratan

- PHP 7.x atau lebih baru
- MySQL / MariaDB
- Web server lokal seperti Laragon, XAMPP, atau WAMP

## Akses aplikasi melalui browser, misalnya:

```
http://localhost/rongsokhub/
```

## Database

File `.sql` di root berisi:

- `users` - akun pengguna
- `collector_profiles` - profil pengepul
- `categories` - kategori barang rongsok
- `items` - daftar barang warga
- `item_photos` - foto barang
- `pickup_requests` - permintaan jemput dari warga atau pengepul

## Akun Default

Terdapat akun admin default:

- Email: `admin@rongsokhub.com`
- Password: `123`

## Catatan

- Pengepul baru akan membuat entri kosong pada tabel `collector_profiles` saat pendaftaran.
- Upload foto disimpan di folder `assets/uploads/`.
- Pastikan folder `assets/uploads/` memiliki izin tulis.

## Penggunaan

1. Daftar sebagai `warga` atau `pengepul`.
2. Login menggunakan akun yang dibuat.
3. Akses dashboard sesuai role.
4. Warga dapat menambahkan barang dan mengajukan penjemputan.
5. Pengepul dapat melihat marketplace dan mengelola status pengajuan.
6. Admin dapat mengelola pengguna, pengepul, kategori, dan permintaan.

---

Dokumentasi ini dibuat untuk memudahkan instalasi dan penggunaan sistem RongsokHub.
