# PPI RS (CodeIgniter 3.1.13)

Audit kepatuhan bundle HAIs (VAP, IADP, ISK, IDO, Plebitis) dengan dua tahap per bundle: IGD dan Rawat Inap.

## Pasang (Laragon / XAMPP)

1. Salin folder ini ke `www` (Laragon: `C:\laragon\www\ppi`, sehingga alamatnya `http://ppi.test`).
2. Buka phpMyAdmin, tab **Import**, pilih `database/ppi.sql`. Database `ppi_rs` terbuat otomatis.
3. Cek `application/config/database.php` (host, user, kata sandi MySQL).
4. Buka `http://ppi.test`. Akun awal (kata sandi `admin123`, segera ganti lewat phpMyAdmin):

   | Pengguna | Peran |
   | --- | --- |
   | admin | Administrator: semua menu, termasuk Master Pertanyaan |
   | ipcn | IPCN/IPCLN: dashboard, audit, laporan |
   | ketua | Ketua PPI: dashboard dan laporan |
   | perawat | Perawat ruangan (Ruang 1): dashboard dan laporan ruangannya |

Mengganti kata sandi: di phpMyAdmin jalankan `php -r "echo password_hash('SANDI_BARU', PASSWORD_BCRYPT);"` lalu tempel hasilnya ke kolom `users.password`.

## Struktur data

- `bundle` -> `tahap` (IGD, RI) -> `pertanyaan`: master yang menggantikan hardcode. Pertanyaan tidak dihapus, hanya dinonaktifkan.
- `simrs_pasien`: **sementara** tiruan data SIMRS di MySQL (satu baris = satu registrasi). Hapus data contoh dan isi dengan data asli, atau sambungkan ke SIMRS.
- `audit`: satu baris per registrasi pasien x bundle, berisi salinan data pasien.
- `audit_jawaban`: jawaban per pertanyaan per tanggal (Y/T/N). Teks pertanyaan disalin saat pertama dijawab sehingga audit lama tidak berubah.

## Pindah dari tabel sementara ke SIMRS (SQL Server)

1. `application/config/database.php`: ubah grup `$db['simrs']` (`dbdriver => 'sqlsrv'`, host, database, user, kata sandi).
2. `application/config/ppi.php`: isi `simrs_table` dan `simrs_kolom` dengan nama tabel/kolom SIMRS. Aplikasi hanya membaca enam kolom: no_registrasi, no_rm, nama, ruangan, tgl_masuk (IGD), tgl_masuk_ri, tgl_keluar.
3. Tidak ada kode lain yang berubah. Data pasien di `audit` berupa salinan, jadi dashboard dan laporan tetap jalan.

## Impor pertanyaan lama

Pertanyaan di database lama bisa diekspor dari phpMyAdmin (CSV) lalu diimpor ke tabel `pertanyaan`
(kolom: `tahap_id`, `teks`, `urutan`, `wajib`, `aktif`, `created_at`, `updated_at`).
`tahap_id` dilihat dari tabel `tahap` (kode IGD atau RI per bundle). Atau ketik ulang lewat menu Master Pertanyaan.

## Aturan penting

- Laporan cetak: kolom tanggal = tanggal masuk sampai tanggal keluar (atau hari ini bila masih dirawat), bukan seluruh tanggal bulan.
- Kepatuhan = Ya / (Ya + Tidak). N/A tidak dihitung.
- Tahap IGD diisi satu kali (hari masuk). Tahap Rawat Inap diisi tiap hari rawat sejak `tgl_masuk_ri`.
- Perubahan teks pertanyaan berlaku untuk audit berikutnya.
