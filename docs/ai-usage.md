# Ringkasan Penggunaan AI

Dokumen ini mencatat bagaimana AI digunakan selama pembangunan KelolaKos, keputusan yang
tetap diambil manusia, dan cara hasilnya diverifikasi. Tujuannya agar laporan tugas jujur
dan dapat direproduksi.

## Alat yang digunakan

- **OpenCode** sebagai harness agen di terminal.
- **Model bahasa**: DeepSeek V4.1 Flash Free melalui penyedia OpenCode.
- **Superpowers skills**: `brainstorming`, `writing-plans`, `executing-plans`,
  `test-driven-development`, `systematic-debugging`, `verification-before-completion`.
- **Laravel Boost** untuk panduan konvensi Laravel dan pemeriksaan versi paket.
- **PHPUnit, Laravel Pint, Vite, Composer audit, npm audit** untuk verifikasi otomatis.

## Pembagian kerja

| Pekerjaan | AI | Manusia |
|---|---|---|
| Menyusun spesifikasi dan rencana per task | Menyusun draf dari kebutuhan yang diberikan | Menyetujui ruang lingkup, tenggat, dan keputusan arsitektur |
| Menulis test lebih dulu (RED) | Menulis test fitur dan menjalankannya | Meninjau apakah test mewakili aturan bisnis |
| Implementasi | Menulis migration, model, controller, service, view, dan route | Meninjau hasil dan memutuskan kompromi |
| Verifikasi | Menjalankan test, Pint, build aset, audit dependensi | Menjalankan uji manual di browser dan hosting |
| Dokumentasi | README, ringkasan AI, komentar kode seperlunya | Memeriksa kebenaran isi sebelum submit |

## Alur kerja per task

1. AI membaca spesifikasi dan rencana pada `docs/superpowers/`.
2. AI menulis test yang gagal (RED), lalu menjalankannya untuk membuktikan kegagalan.
3. AI mengimplementasikan fitur sampai test hijau (GREEN).
4. AI menjalankan seluruh suite, `vendor/bin/pint --dirty`, dan `npm run build`.
5. AI membuat satu commit dengan pesan yang menjelaskan tahap tersebut.
6. Manusia meninjau diff dan hasil sebelum melanjutkan ke task berikutnya.

Riwayat commit dapat dilihat dengan:

```bash
git log --oneline --decorate
```

## Contoh prompt yang digunakan

- "Buat aplikasi KelolaKos sesuai spesifikasi di `docs/superpowers/specs`. Gunakan Laravel
  13, MySQL untuk pengembangan, SQLite in-memory untuk test, dan Tailwind 4."
- "Lanjutkan Task 8 dengan TDD: tulis test dulu, buktikan RED, implementasi, lalu tunjukkan
  hasil suite dan commit."
- "Perbaiki kegagalan test laporan tanpa mengubah ekspektasi bisnis; jelaskan akar
  penyebabnya."
- "Tambahkan hardening: CSP tanpa `unsafe-inline` pada script, HSTS di HTTPS, batas sesi,
  dan test CSRF/XSS/SQLi/rate limit."
- "Buat seeder realistis berisi 2 kos, 12 kamar, 6 penghuni, dan 3 bulan tagihan, tanpa
  membuat super admin berpassword di kode."
- "Tulis README berbahasa Indonesia yang memuat instalasi, variabel lingkungan tanpa nilai
  rahasia, cara menjalankan test, dan checklist deployment."

## Keputusan yang diambil manusia

- Memilih Laravel 13, MySQL 8, Laragon, dan URL lokal `http://kelola-kos.test`.
- Memilih bekerja langsung di branch `feat/mvp` karena Laragon melayani folder proyek.
- Menetapkan aturan bisnis: satu pemesanan aktif per penghuni, tagihan pertama dibuat saat
  pengajuan disetujui, laporan pendapatan berbasis kas (tanggal verifikasi).
- Memutuskan super admin dibuat lewat perintah Artisan, bukan seeder dengan kata sandi
  tertulis di kode.
- Menyetujui kompromi CSP `style-src 'unsafe-inline'` agar tampilan tidak rusak, sementara
  `script-src` tetap ketat.
- Menentukan bahwa akun demo hanya untuk lokal dan kata sandinya wajib diganti di produksi.

## Verifikasi yang sudah dilakukan

- Suite PHPUnit lengkap hijau (111 test, 355 assertion pada commit terakhir Task 12).
  Angka dapat berubah bila test ditambah; jalankan `php artisan test --compact` untuk
  memastikan kondisi terkini.
- `vendor/bin/pint --dirty --format agent` bersih.
- `npm run build` berhasil.
- `composer audit` dan `npm audit` melaporkan nol kerentanan yang diketahui.
- Test keamanan mencakup CSRF, XSS, SQL injection, rate limit login, header CSP/HSTS,
  isolasi tenant, dan batas sesi.

## Verifikasi yang masih harus dilakukan manusia

- Uji manual di browser untuk seluruh alur demo (pengajuan → persetujuan → tagihan →
  bukti bayar → verifikasi → laporan).
- Uji unggah file nyata (JPG/PNG/PDF) dan pastikan bukti hanya dapat dibuka pihak berhak.
- Uji di hosting produksi: HTTPS, redirect, cookie `Secure`, trusted proxy, dan
  `securityheaders.com` / SSL Labs.
- Periksa tampilan responsif dan aksesibilitas dasar.
- Ekspor database sebelum demo sebagai cadangan.

## Keterbatasan

- AI tidak dapat menjamin perilaku browser, server produksi, atau penyedia hosting.
- AI dapat menghasilkan kode yang lolos test namun tetap perlu ditinjau manusia.
- Riwayat percakapan tidak menjadi pengganti dokumentasi; keputusan penting dicatat di
  spesifikasi, rencana, dan dokumen ini.

## Berkas terkait

- Spesifikasi: `docs/superpowers/specs/2026-11-04-kelolakos-design.md`
- Rencana: `docs/superpowers/plans/2026-11-04-kelolakos-mvp.md`
- Panduan proyek: `AGENTS.md`
