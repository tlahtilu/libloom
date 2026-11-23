LIBLOOM — Where Dreams Grow Between Pages
VERSI TERINTEGRASI: PROJECT UTAMA + BIBLIOGRAFI + BARCODE + SCAN

FITUR UTAMA
- Home publik dan OPAC dengan pencarian koleksi.
- Login dan register petugas menggunakan password_hash/password_verify.
- Dashboard Back Office.
- Bibliografi: tambah, edit, hapus, pencarian, dan pengelolaan eksemplar.
- Barcode Code 128 untuk setiap eksemplar dan cetak melalui browser / Save as PDF.
- Scan barcode menggunakan scanner USB atau kamera perangkat.
- Keanggotaan: tambah, edit, hapus.
- Sirkulasi: peminjaman, pengembalian, dan denda Rp1.000 per hari keterlambatan.
- Riwayat transaksi.
- Notifikasi jatuh tempo H-2, jatuh tempo hari ini, dan keterlambatan.

STRUKTUR
Libloom/
  index.php
  config.php
  actions.php
  layout.php
  pages/
    opac.php, login.php, dashboard.php, koleksi.php, barcode.php, scan.php,
    anggota.php, sirkulasi.php, riwayat.php, notifikasi.php
  assets/style.css
  libloom.db (database lokal; tidak disertakan di paket agar database milikmu tidak tertimpa)

CARA MEMASANG
1. Backup dulu folder Libloom dan database libloom.db yang sekarang kamu pakai.
2. Ekstrak folder Libloom_Terintegrasi ke C:\xampp\htdocs\.
3. Jika folder hasil ekstrak bernama Libloom_Terintegrasi, ubah namanya menjadi Libloom.
4. Salin file kode ke folder project Libloom sesuai struktur di atas. Jangan menimpa libloom.db milikmu.
5. Jalankan Apache dari XAMPP.
6. Buka http://localhost/Libloom/.
7. Jika database belum ada, sistem membuat libloom.db dan tabel awal secara otomatis.
8. Jika tabel users masih kosong, buka Login > Register untuk membuat akun pertama (otomatis admin).

CATATAN DATABASE
- Project saat ini memakai SQLite melalui PDO SQLite, bukan MySQL. Ini mengikuti kode project utama yang tersedia dan menjaga kompatibilitas database libloom.db.
- Pastikan ekstensi pdo_sqlite aktif pada PHP XAMPP.
- Jangan mengimpor file libloom.db ke phpMyAdmin; SQLite bukan database MySQL.
- Paket ini tidak menyertakan database agar data lokal yang sudah ada tidak tertimpa.

CATATAN FITUR
- Barcode dibuat dari ID eksemplar dengan pola LB00001 dan seterusnya.
- Pencetakan barcode memakai JsBarcode melalui CDN; perlu koneksi internet saat halaman barcode dibuka.
- Pemindaian kamera memakai html5-qrcode melalui CDN; kamera browser hanya berjalan pada localhost atau HTTPS dan setelah izin diberikan.
- Scan saat ini mencari data inventaris dan menampilkan status eksemplar. Peminjaman/pengembalian tetap dicatat melalui menu Sirkulasi.
- Notifikasi saat ini tampil di dalam Back Office, belum mengirim WhatsApp, email, atau push notification.
- Akun register berikutnya otomatis menjadi petugas. Untuk penggunaan produksi, pendaftaran publik perlu dibatasi.

PENGUJIAN YANG DISARANKAN
1. Login dan logout.
2. Tambah bibliografi dengan 1–2 eksemplar.
3. Pastikan barcode muncul dan dapat dicetak.
4. Scan barcode dengan scanner USB atau kamera.
5. Tambah anggota.
6. Pinjam eksemplar dan pastikan status berubah.
7. Kembalikan buku; uji denda dengan tanggal jatuh tempo lampau.
8. Periksa riwayat transaksi dan notifikasi.
