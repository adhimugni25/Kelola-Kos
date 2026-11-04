# KelolaKos — Dokumen Desain

Tanggal: 4 November 2026
Status: draf untuk disetujui
Konteks: Tugas Besar mata kuliah Cyber Security (individu, pra-UTS, kumpul 16 November 2026)

---

## 1. Ringkasan

KelolaKos adalah aplikasi web untuk mengelola kos-kosan. Satu instalasi dipakai oleh
banyak kos: setiap kos punya pengurus sendiri yang hanya melihat data kosnya, sementara
pemilik platform (super admin) mendaftarkan kos dan akun pengurusnya.

Penyewa berinteraksi lewat dua cara: halaman publik kos untuk melihat kamar kosong dan
mengajukan sewa, lalu akun penghuni untuk melihat tagihan, mengunggah bukti pembayaran,
dan memantau status sewanya. Semua proses pembayaran terjadi di dalam aplikasi
(transfer bank atau e-wallet, lalu unggah bukti, lalu verifikasi pengurus).

Aplikasi dibangun dari nol sebagai repo baru dengan riwayat commit bertahap, bukan
salinan dari proyek kos-kosan yang pernah ada.

## 2. Tujuan dan kriteria sukses

Tujuan:

1. Memenuhi seluruh fitur minimum tugas: registrasi/login, CRUD berdatabase, dan laporan.
2. Menerapkan keenam kewajiban keamanan dan sebanyak mungkin nilai tambahnya.
3. Online di `https://kelolakos.site` (atau subdomainnya) dengan HTTPS valid, stabil
   sampai nilai diumumkan.
4. Menghasilkan bahan laporan yang jujur: tabel self-testing dengan bukti, ringkasan
   penggunaan AI, dan README yang rapi.

Kriteria sukses yang bisa diukur:

- Alur lengkap pemesan → disetujui → penghuni → tagihan → bukti bayar → lunas berjalan
  tanpa error di data demo.
- Tiga peran tidak bisa saling menusuk: pengurus A tidak bisa membuka data kos B,
  penghuni tidak bisa membuka tagihan penghuni lain, tidak ada halaman pengurus yang
  bisa dibuka penghuni.
- Minimal 8 skenario uji keamanan dengan hasil dan bukti; temuan yang ada diperbaiki
  dan ditunjukkan sebelum/sesudah.
- securityheaders.com memberi minimal grade B (target A), SSL Labs minimal A.
- `composer audit` bersih, tidak ada secret di riwayat git.

## 3. Pengguna dan peran

| Peran | Siapa | Bisa apa | Batas |
|---|---|---|---|
| `super_admin` | Pemilik platform (mahasiswa) | Mendaftarkan kos, membuat/menonaktifkan akun pengurus, melihat statistik platform | Tidak mengelola operasional harian kos (kamar, tagihan) dan tidak membaca bukti pembayaran |
| `pengurus` | Pengelola satu kos | Mengelola profil kos, kamar, pengajuan sewa, penghuni, tagihan, verifikasi pembayaran, laporan kosnya | Hanya data `kos_id` miliknya |
| `penghuni` | Pemesan / penyewa | Melihat halaman publik, mengajukan sewa, melihat tagihannya, mengunggah bukti bayar, melihat riwayat dan status sewa | Hanya data miliknya sendiri |

Keputusan model:

- Satu akun punya satu peran tetap. Peran tidak berubah; yang berubah adalah status
  pemesanan (`menunggu` → `aktif`), sehingga "pemesan" dan "penghuni" adalah orang yang
  sama pada tahap berbeda. Ini menghindari dua tabel akun dan dua alur login.
- Satu akun pengurus terikat ke satu kos (`users.kos_id`). Pengurus dengan banyak kos
  bukan kebutuhan saat ini.
- Akun penghuni bersifat global (tidak terikat kos), tetapi setiap pemesanan terikat ke
  satu kos. Satu akun boleh punya riwayat pemesanan di kos berbeda, dengan aturan hanya
  satu pemesanan aktif pada satu waktu.
- Akun pengurus dibuat oleh super admin, tidak ada pendaftaran mandiri untuk pengurus.
  Pendaftaran mandiri hanya untuk penghuni.
- Super admin tidak dibuat lewat seeder dengan password tertulis di kode; akunnya dibuat
  lewat perintah artisan khusus yang membaca input/env, dan password diganti setelah
  login pertama.

## 4. Ruang lingkup

### 4.1 Fitur wajib (langsung dinilai)

1. **Autentikasi**: registrasi penghuni (nama, email, no. HP, password + konfirmasi),
   login, logout, proteksi halaman berdasarkan peran, pembatasan percobaan login.
2. **CRUD berdatabase**:
   - Super admin: kos (buat, lihat, ubah, nonaktifkan) dan akun pengurus (buat, reset
     password, nonaktifkan).
   - Pengurus: kamar (CRUD penuh + status), pengajuan sewa (lihat, setujui, tolak),
     tagihan (buat, ubah nominal/jatuh tempo, batalkan), metode pembayaran (CRUD).
   - Penghuni: pemesanan (buat, batalkan selama masih menunggu), bukti pembayaran
     (unggah, hapus selama belum diverifikasi).
3. **Laporan**:
   - Dashboard pengurus: tingkat hunian, pendapatan bulan berjalan, tagihan belum lunas,
     pembayaran menunggu verifikasi.
   - Halaman laporan: rekap pendapatan per bulan (tabel + grafik batang sederhana),
     rekap keterisian kamar, daftar tagihan belum lunas.
   - Ekspor CSV dan PDF untuk rekap pendapatan dan daftar tagihan.
   - Dashboard super admin: jumlah kos, kamar, pengurus, penghuni aktif per kos.
4. **Keamanan**: lihat bagian 8.

### 4.2 Fitur tambahan (nilai plus, dikerjakan setelah wajib beres)

- **Import CSV kamar dan penghuni** — memudahkan migrasi data dari Excel tanpa input
  manual. Template download, validasi, preview error, insert batch.
- Log aktivitas (login berhasil/gagal, verifikasi pembayaran, perubahan kamar).
- Security headers + CSP.
- Bukti pembayaran disimpan di storage privat dan disajikan lewat route ber-otorisasi.
- Halaman publik kos menampilkan kamar kosong real-time dari database.
- Indikator tagihan terlambat (dihitung dari jatuh tempo, bukan status tersimpan).
- Tombol "salin link halaman kos".

### 4.3 Non-goals (tegas tidak dikerjakan)

- Direktori/marketplace lintas kos dan pencarian kos.
- Payment gateway (Midtrans/Xendit) dan verifikasi webhook.
- Langganan berbayar SaaS, invoice platform, dan pembatasan paket.
- Multi-gedung/cabang di dalam satu kos.
- Notifikasi email/WhatsApp, reset password mandiri, verifikasi email, 2FA.
- API publik atau aplikasi mobile.
- Multi-bahasa (UI hanya Bahasa Indonesia).
- QR code kuitansi.

Non-goal boleh disebut di laporan sebagai rencana pengembangan skripsi, bukan dikerjakan.

## 5. Alur utama

### 5.1 Penyiapan kos

1. Super admin membuat kos (nama, slug, alamat, kota, deskripsi) dan akun pengurus
   (nama, email, password awal).
2. Pengurus login, melengkapi profil kos (kontak, aturan, fasilitas umum), mengisi
   metode pembayaran (bank/e-wallet: jenis, nama, nomor, atas nama), lalu menambah kamar.
3. Selama kos berstatus `nonaktif` atau belum punya kamar kosong, halaman publiknya
   menampilkan pesan "belum menerima pemesanan".

### 5.2 Pengajuan sewa

1. Pengunjung membuka `https://kelolakos.site/k/{slug}`.
2. Melihat daftar kamar kosong (nomor disamarkan opsional; harga, tipe, fasilitas, foto).
3. Menekan "Ajukan sewa" → diarahkan login/daftar bila belum punya akun → kembali ke
   form pengajuan.
4. Form: kamar, tanggal mulai, durasi (bulan), catatan. Sistem memvalidasi kamar masih
   kosong dan pengguna belum punya pemesanan aktif.
5. Pemesanan tersimpan berstatus `menunggu`. Kamar tetap `kosong` sampai disetujui.
6. Pengurus melihat daftar pengajuan, lalu menyetujui (menentukan tanggal mulai final
   dan durasi) atau menolak dengan alasan wajib.
7. Saat disetujui dalam satu transaksi database:
   - pemesanan → `aktif`, `berakhir_pada` diisi dari tanggal mulai + durasi,
   - kamar → `terisi`,
   - pengajuan `menunggu` lain untuk kamar yang sama otomatis menjadi `ditolak`
     dengan alasan "Kamar sudah terisi",
   - tagihan pertama dibuat untuk bulan berjalan, jatuh tempo 7 hari,
   - pengguna mendapat akses halaman penghuni.
8. Penghuni boleh membatalkan selama status masih `menunggu`; setelah `aktif`,
   pembatalan dilakukan pengurus (status `selesai`/`dibatalkan`) agar data keuangan
   tetap konsisten.

Aturan tambahan:

- Satu pengguna maksimal punya satu pemesanan `menunggu`/`aktif` pada satu waktu.
- Beberapa pengguna boleh mengajukan kamar yang sama; yang disetujui lebih dulu menang
  dan sisanya otomatis ditolak.
- Persetujuan memakai transaksi + `lockForUpdate` pada baris kamar untuk mencegah dua
  pengajuan disetujui bersamaan pada kamar yang sama.
- Menolak atau membatalkan wajib menyimpan alasan.
- Pengurus tidak boleh memindahkan pengajuan ke kamar lain saat menyetujui; jika kamar
  yang diminta sudah tidak cocok, pengajuan ditolak dan pemesan mengajukan ulang.

### 5.3 Tagihan dan pembayaran

1. Tagihan berisi: kos, pemesanan, periode (bulan/tahun), nominal sewa, biaya tambahan
   opsional (mis. wifi) dengan keterangan, total, jatuh tempo, status.
2. Status tagihan: `belum_bayar`, `menunggu_verifikasi`, `lunas`, `dibatalkan`. Status
   `ditolak` hanya milik bukti pembayaran, bukan tagihan.
3. **Periode dan nominal**:
   - Periode adalah bulan kalender (1–akhir bulan), bukan siklus tanggal masuk.
   - Tidak ada prorata. Sewa bulan pertama dihitung penuh, berapa pun tanggal masuknya;
     ini aturan bisnis yang ditulis di halaman kos agar tidak menimbulkan sengketa.
   - Nominal sewa memakai `pemesanan.harga_bulanan` (salinan harga saat disetujui), bukan
     harga kamar terkini, sehingga perubahan harga tidak mengubah tagihan lama.
   - Masa sewa ditentukan `berakhir_pada`; pengingat "sewa berakhir" muncul 7 hari
     sebelumnya, tetapi tagihan tetap dibuat manual sampai pengurus menandai selesai.
4. Pembuatan tagihan: otomatis untuk bulan berjalan saat persetujuan; untuk bulan
   berikutnya pengurus menekan "Buat tagihan bulan ini" yang membuat tagihan untuk semua
   pemesanan aktif yang belum punya tagihan pada periode itu (idempoten, tidak dobel;
   dijaga unique index dan transaksi).
5. Penghuni membuka tagihan → melihat tujuan transfer/e-wallet dan total → unggah bukti
   (JPG/PNG/PDF maks. 2 MB) → status `menunggu_verifikasi`.
6. Pengurus membuka bukti (lewat route ber-otorisasi, bukan URL publik), melihat
   kecocokan nominal dan tujuan, lalu menerima atau menolak dengan catatan.
7. **Nominal harus sama persis** dengan total tagihan. Tidak ada pembayaran sebagian;
   jika kurang atau lebih, bukti ditolak dan penghuni mengunggah ulang. Setiap unggahan
   adalah baris `pembayaran` baru; riwayat yang ditolak tetap tersimpan.
8. Satu tagihan hanya boleh punya satu pembayaran `diterima`. Setelah diterima, tagihan
   `lunas` dan tidak bisa diubah lagi.
9. **Aturan ubah tagihan**: nominal dan biaya tambahan hanya bisa diubah selama
   `belum_bayar` dan tidak ada bukti menunggu; jatuh tempo bisa diubah selama belum
   `lunas`; tagihan bisa dibatalkan hanya selama `belum_bayar` atau setelah bukti terakhir
   ditolak.
10. **Pendapatan** dihitung dari `pembayaran.diverifikasi_pada` (basis kas), bukan dari
    periode tagihan. Dashboard dan laporan memakai basis yang sama agar angkanya tidak
    berbeda.
11. Semua perubahan status dicatat di log aktivitas (`audit_logs`).

### 5.6 Import data (migrasi dari sistem lama)

Pengurus bisa migrasi data bulk lewat CSV tanpa input manual. Ini mempercepat onboarding
kos yang sudah berjalan.

**Import kamar**:
1. Download template CSV (nomor, tipe, harga_bulanan, fasilitas, status).
2. Isi di Excel, upload.
3. Sistem validasi: nomor unik per kos, harga numerik positif, status valid, fasilitas
   maks. 500 karakter.
4. Preview error per baris jika ada; jika valid, insert batch dalam transaksi.
5. Foto kamar ditambahkan manual setelahnya (opsional untuk migrasi cepat).

**Import penghuni aktif**:
1. Download template (nama, email, no_hp, nomor_kamar, tanggal_mulai, durasi_bulan).
2. Sistem validasi: email format valid (dibuat akun jika belum ada), nomor kamar ada dan
   kosong, tanggal mulai tidak di masa depan, durasi 1–24.
3. Preview konflik (email sudah ada dengan nama beda, kamar sudah terisi).
4. Jika valid, dalam satu transaksi per baris:
   - Buat/ambil akun penghuni dengan peran `penghuni`, password random (dikirim email
     reset atau ditampilkan di layar untuk dicatat pengurus).
   - Buat pemesanan langsung status `aktif`, `berakhir_pada` dihitung, `harga_bulanan`
     disalin dari kamar, `disetujui_oleh` = pengurus yang import.
   - Set kamar `terisi`.
   - **Tidak** otomatis buat tagihan saat import; setelah selesai, pengurus menekan
     "Buat tagihan bulan ini" seperti biasa.

**Yang tidak diimport**: tagihan/pembayaran lama (historis disimpan di luar sistem),
metode pembayaran (manual karena sensitif), gedung/lantai (tidak ada di scope).

**Validasi keamanan**: file maks. 5 MB, ekstensi `.csv`, parse dengan library aman
(bukan `eval`), rate limit 3 upload/jam, log semua import dengan jumlah sukses/gagal.

### 5.4 Akhir masa sewa

Pengurus menandai pemesanan `selesai` (atau `dibatalkan` bila berhenti di tengah jalan).
Syaratnya: tidak ada tagihan `belum_bayar`/`menunggu_verifikasi` yang tersisa — pengurus
harus menyelesaikan atau membatalkannya lebih dulu, sehingga tidak ada tagihan menggantung.
Kamar kembali `kosong`. Akun penghuni tetap bisa login dan melihat riwayat, tetapi tidak
bisa mengunggah bukti untuk tagihan yang sudah ditutup.

Pengurus menandai pemesanan `selesai` (atau `dibatalkan` bila berhenti di tengah jalan).
Syaratnya: tidak ada tagihan `belum_bayar`/`menunggu_verifikasi` yang tersisa — pengurus
harus menyelesaikan atau membatalkannya lebih dulu, sehingga tidak ada tagihan menggantung.
Kamar kembali `kosong`. Akun penghuni tetap bisa login dan melihat riwayat, tetapi tidak
bisa mengunggah bukti untuk tagihan yang sudah ditutup.

## 6. Model data

MySQL 8, InnoDB, `utf8mb4`. Semua tabel memakai `id` big integer auto increment dan
`timestamps`, kecuali log.

### `users`

- `id`, `name`, `email` (unique), `password` (hash), `role` enum(`super_admin`,
  `pengurus`, `penghuni`), `phone` (nullable), `kos_id` (nullable, FK `kos`, hanya untuk
  pengurus), `is_active` boolean default true, `last_login_at` nullable,
  `remember_token`, `timestamps`.
- Index: `(role)`, `(kos_id)`.

### `kos`

- `id`, `nama`, `slug` (unique), `alamat`, `kota`, `deskripsi` text, `kontak` (no. HP/WA),
  `fasilitas_umum` text nullable, `is_active` boolean default true, `timestamps`.
- Soft delete tidak dipakai; penonaktifan lewat `is_active` agar riwayat tagihan aman.

### `kamar`

- `id`, `kos_id` FK, `nomor`, `tipe`, `harga_bulanan` decimal(12,2), `fasilitas` text,
  `foto_path` nullable, `status` enum(`kosong`,`terisi`,`perbaikan`), `catatan` nullable,
  `timestamps`.
- Unique `(kos_id, nomor)`, index `(kos_id, status)`.

### `pemesanan`

- `id`, `kos_id` FK, `kamar_id` FK, `user_id` FK, `tanggal_mulai` date,
  `durasi_bulan` smallint, `harga_bulanan` decimal (disalin saat disetujui agar riwayat
  tidak berubah saat harga kamar diubah), `berakhir_pada` date nullable (diisi saat
  disetujui, dipakai untuk laporan keterisian), `status` enum(`menunggu`,`aktif`,
  `ditolak`,`selesai`,`dibatalkan`), `catatan` text nullable, `alasan_penolakan` text
  nullable, `disetujui_oleh` FK users nullable, `disetujui_pada` datetime nullable,
  `timestamps`.
- Index `(kos_id, status)`, `(user_id, status)`, `(kamar_id, status)`, `(kos_id, tanggal_mulai)`, `(kos_id, berakhir_pada)`.

### `tagihan`

- `id`, `kos_id` FK, `pemesanan_id` FK, `periode_bulan` tinyint, `periode_tahun`
  smallint, `nominal_sewa` decimal(12,2), `biaya_tambahan` decimal(12,2) default 0,
  `keterangan_tambahan` varchar nullable, `total` decimal(12,2), `jatuh_tempo` date,
  `status` enum(`belum_bayar`,`menunggu_verifikasi`,`lunas`,`dibatalkan`), `timestamps`.
- Unique `(pemesanan_id, periode_tahun, periode_bulan)` untuk mencegah tagihan dobel.
- Index `(kos_id, status)`, `(jatuh_tempo)`.
- Aturan "satu pembayaran diterima per tagihan" dijaga di `PaymentService` dalam
  transaksi, bukan lewat unique index (MySQL tidak punya partial index).

### `pembayaran`

- `id`, `tagihan_id` FK, `metode_pembayaran_id` FK, `jumlah` decimal(12,2),
  `bukti_path`, `catatan_penghuni` nullable, `status` enum(`menunggu`,`diterima`,
  `ditolak`), `catatan_verifikasi` nullable, `diverifikasi_oleh` FK users nullable,
  `diverifikasi_pada` datetime nullable, `timestamps`.
- Index `(tagihan_id, status)`.

### `metode_pembayaran`

- `id`, `kos_id` FK, `tipe` enum(`bank`,`ewallet`), `nama` (mis. BCA, DANA), `nomor`,
  `atas_nama`, `is_active` boolean, `timestamps`.
- Index `(kos_id, is_active)`.

### `audit_logs`

- `id`, `user_id` nullable FK, `kos_id` nullable FK (diambil dari pengguna yang
  bertindak; null untuk aksi platform), `aksi` varchar (mis. `login.gagal`,
  `pembayaran.terima`), `entitas` varchar nullable, `entitas_id` bigint nullable,
  `detail` json nullable, `ip` varchar(45), `user_agent` text nullable, `created_at`.
- Index `(kos_id, created_at)`, `(user_id, created_at)`, `(aksi, created_at)`.
- Pengurus hanya melihat log dengan `kos_id` miliknya; super admin melihat log platform
  (login gagal, pembuatan akun) dan tidak melihat log operasional kos.

### Keputusan tambahan

- Uang selalu `decimal`, tidak pernah float.
- Status kamar satu-satunya sumber kebenaran keterisian; `tagihan` tidak menyimpan status
  kamar.
- Tagihan tidak dihapus; pembatalan memakai status.
- Semua foreign key memakai `restrictOnDelete` kecuali `pembayaran` ke `tagihan`
  (`cascade` tidak dipakai; penghapusan data tidak disediakan di UI).

## 7. Arsitektur dan stack

- **Framework**: Laravel 12, PHP 8.3 (Laragon), MySQL 8 (phpMyAdmin untuk inspeksi lokal).
- **Tampilan**: Blade + Tailwind CSS (dikompilasi Vite), JavaScript minimal (tanpa SPA).
  Grafik memakai Chart.js yang di-bundle lewat Vite, bukan CDN, agar CSP ketat tetap bisa.
- **Pola**: MVC + Form Request untuk validasi + Policy untuk otorisasi per record +
  service class untuk transaksi lintas tabel (`BookingService`, `BillingService`,
  `PaymentService`).
- **Tenant isolation**: global scope Eloquent berdasarkan `kos_id` untuk model yang
  memiliki `kos_id`, ditambah Policy sebagai lapisan kedua. Pengurus tidak pernah
  mengirim `kos_id` dari form; nilainya diambil dari sesi pengguna.
- **Penyimpanan file**: disk privat `local` (`storage/app/private/bukti`), nama file
  diacak, disajikan lewat controller yang memeriksa kepemilikan.
- **Antrian & cache**: `database` driver untuk cache dan queue (tidak ada worker khusus;
  tanpa job berat).
- **Zona waktu**: `Asia/Jakarta`, format tanggal `d/m/Y`.

Struktur direktori utama:

```
app/
  Http/Controllers/{Auth,Public,Pengurus,Penghuni,SuperAdmin}/
  Http/Middleware/{EnsureRole,SecurityHeaders}.php
  Http/Requests/
  Models/
  Policies/
  Services/
  Support/TenantScope.php
resources/views/{auth,public,pengurus,penghuni,superadmin,layouts}/
routes/web.php
database/migrations, seeders, factories
tests/Feature/{Auth,TenantIsolation,Booking,Billing,Security}/
docs/
```

## 8. Penerapan keamanan

| Kewajiban tugas | Implementasi | Lokasi | Cara dibuktikan |
|---|---|---|---|
| Hash password | `Hash::make` bcrypt cost 12; tidak ada password plaintext di DB/log | `AuthController`, `User::casts` | Screenshot kolom password di phpMyAdmin + uji login |
| Validasi & sanitasi input | Form Request di semua form; aturan ketat (email, panjang, enum, numeric); Eloquent binding; tidak ada `DB::raw` dengan input | `app/Http/Requests` | Uji payload SQLi di login/filter dan XSS di nama kamar/catatan |
| Anti XSS | Escaping Blade `{{ }}` di semua output; tidak memakai `{!! !!}` untuk input pengguna; CSP | `resources/views` | Simpan `<script>alert(1)</script>` lalu lihat render |
| Sesi aman | Driver database, `HttpOnly`, `Secure` (produksi), `SameSite=Lax`, regenerasi ID saat login, invalidasi saat logout, masa berlaku 30 menit idle / 12 jam absolut | `config/session.php`, `AuthController` | curl lihat atribut cookie, uji logout lalu akses halaman |
| Kontrol akses peran | Middleware `role:` pada grup route + Policy per model | `routes/web.php`, `app/Policies` | Penghuni akses `/pengurus/*` → 403 |
| Isolasi tenant (IDOR) | Global scope `kos_id` + Policy + validasi kepemilikan di service | Model, Policy | Pengurus A membuka ID kamar/tagihan kos B → 404/403 |
| Secret | Semua kredensial di `.env`; `.env.example` tanpa nilai; `.gitignore`; riwayat git bersih | `.env`, `.gitignore` | `git log -S` / gitleaks; screenshot `.env.example` |
| Rate limiting | Login 5/menit per email+IP, register 3/menit, unggah bukti 10/menit, route umum 60/menit | `RouteServiceProvider`/`bootstrap/app.php` | Uji 6 login salah → 429 |
| CSRF | Middleware `VerifyCsrfToken` (default) + `@csrf`; token pada semua form | `bootstrap/app.php`, views | POST tanpa token → 419 |
| Security headers | `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`, `Permissions-Policy`, `Content-Security-Policy` tanpa `unsafe-inline`, HSTS di HTTPS | `SecurityHeaders` middleware | securityheaders.com |
| Upload aman | Validasi MIME + ekstensi + ukuran; nama acak; storage privat; tidak dieksekusi | `PaymentService` | Unggah `.php`/`.exe` → ditolak |
| Log aktivitas | `audit_logs` untuk login, verifikasi, perubahan status | `AuditLogger` | Tabel log saat demo |
| Dependency | `composer audit` + `npm audit` sebelum submit | CI manual | Screenshot hasil |
| HTTPS | Redirect paksa + `trustProxies`; sertifikat dari hosting | `AppServiceProvider`/middleware | Ikon gembok + detail sertifikat |

Catatan: CSP harus disesuaikan dengan aset hasil build Vite (hash/nonce), bukan
`unsafe-inline`. Jika terlalu rumit, CSP dilonggarkan ke `style-src 'self' 'unsafe-inline'`
dan didokumentasikan sebagai kompromi — jangan sampai halaman rusak saat demo.

## 9. Laporan dan ekspor

- **Dashboard pengurus** (`/pengurus`): kartu tingkat hunian (terisi/total kamar),
  pendapatan bulan ini (pembayaran diterima), tagihan belum lunas, jumlah pengajuan
  menunggu, tabel 5 pembayaran terbaru.
- **Laporan** (`/pengurus/laporan`): filter rentang bulan; tabel rekap pendapatan per
  bulan (basis tanggal verifikasi); grafik batang; rekap keterisian per kamar (hari
  terisi / hari dalam bulan, dihitung dari rentang `tanggal_mulai`–`berakhir_pada`);
  daftar tagihan belum lunas dengan umur tunggakan (hari sejak jatuh tempo).
- **Ekspor**: CSV (streamed) dan PDF (dompdf) untuk rekap bulanan dan daftar tagihan.
  Nama file menyertakan slug kos dan periode.
- **Super admin** (`/admin`): jumlah kos aktif, total kamar, total penghuni aktif,
  pendapatan platform tidak ditampilkan (bukan urusan super admin).

## 10. Halaman

Publik: `/` (penjelasan singkat + masuk), `/k/{slug}` (profil kos, kamar kosong,
fasilitas, form pengajuan), `/login`, `/register`.

Pengurus (`/pengurus`, middleware `role:pengurus`): dashboard, profil kos, kamar
(index/form), pengajuan (index/detail), penghuni (index/detail), tagihan (index/form/
detail), pembayaran (index/verifikasi), metode pembayaran, laporan, log aktivitas.

Penghuni (`/penghuni`): dashboard, pengajuan saya, tagihan saya, detail tagihan +
unggah bukti, riwayat pembayaran, profil.

Super admin (`/admin`): daftar kos (form), daftar pengurus (form + reset password),
statistik ringkas.

## 11. Deployment

Lokal:

- Laragon, PHP 8.3, MySQL 8, database `kelolakos`, akses `http://kelolakos.test`.
- `.env` lokal tidak di-commit. `.env.example` berisi nama variabel saja.

Produksi (hosting dipilih kemudian, syarat minimum):

- PHP ≥ 8.3 (untuk keamanan, hindari CVE lama), Laravel ≥ 11, MySQL 8, document root diarahkan ke `public/`.
- Domain `kelolakos.site` diarahkan ke hosting (A record atau nameserver). Jika memakai
  subdomain gratis, domain tetap dipakai untuk halaman utama.
- Langkah: clone repo → `composer install --no-dev --optimize-autoloader` →
  `npm ci && npm run build` (atau unggah hasil build) → `.env` produksi →
  `php artisan key:generate` → `migrate --force --seed` → `storage:link` →
  `config:cache route:cache view:cache` → aktifkan HTTPS redirect.
- Backup: ekspor SQL lewat phpMyAdmin sebelum demo.

Rencana cadangan bila hosting belum siap H-3: alwaysdata (PHP + MySQL + SSH) sebagai
hot standby; DNS domain diarahkan ke sana. Keputusan final diambil paling lambat 12
November.

## 12. Rencana kerja (4–16 November)

| Tanggal | Pekerjaan |
|---|---|
| 4–5 | Repo, scaffold Laravel, auth tiga peran, layout, middleware keamanan dasar |
| 6–7 | CRUD kos/kamar (super admin & pengurus), halaman publik kos |
| 8–9 | Alur pengajuan, persetujuan, transisi kamar, akun penghuni |
| 10–11 | Tagihan, unggah bukti, verifikasi, dashboard, laporan + ekspor |
| 12 | Super admin, log aktivitas, penyempurnaan UI |
| 13 | Hardening: header, CSP, rate limit, audit dependency, perbaikan temuan |
| 14 | Self-testing lengkap (8–10 skenario) + bukti sebelum/sesudah |
| 15 | Deploy ke produksi, HTTPS, uji dari luar, README, screenshot |
| 16 | Laporan PDF, pemeriksaan akhir, submit |

Buffer: tidak ada hari kosong; jika tertinggal, yang dipotong adalah fitur tambahan
(4.2), bukan keamanan atau laporan.

## 13. Risiko

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Hosting belum dipilih sampai akhir | Tidak ada URL publik (20%) | Putuskan 12 Nov; alwaysdata sebagai cadangan |
| CSP merusak tampilan | Demo gagal | Uji CSP di lokal, longgarkan bertahap |
| 12 hari terlalu pendek | Fitur setengah jadi | Kerjakan jalur wajib lebih dulu; fitur tambahan terakhir |
| Data uji terlihat palsu | Nilai orisinalitas turun | Seeder realistis: 2 kos, 12 kamar, 6 penghuni, 3 bulan tagihan |
| Bug otorisasi lolos | Nilai keamanan turun | Tes fitur otomatis untuk isolasi tenant, bukan hanya manual |
| Lupa dokumentasi AI | Laporan tidak lengkap | Catat prompt & keputusan di `docs/ai-usage.md` setiap sesi |

## 14. Keputusan yang sudah diambil (bisa dikoreksi saat review)

- Nama aplikasi: **KelolaKos**; UI Bahasa Indonesia.
- Laravel + Blade + Tailwind, bukan SPA/API terpisah.
- Multi-tenant dengan kolom `kos_id` dan global scope, bukan database per kos.
- Satu pengurus = satu kos; super admin tidak menyentuh operasional.
- Pembayaran manual dengan bukti unggah, tanpa gateway.
- Tidak ada direktori publik lintas kos; halaman kos diakses lewat link.
- Tidak ada pendaftaran mandiri untuk pengurus; akun dibuat super admin.
- Akun super admin dibuat lewat perintah artisan, bukan seeder berpassword.
- Status tagihan: `belum_bayar`, `menunggu_verifikasi`, `lunas`, `dibatalkan`; penolakan
  hanya pada bukti pembayaran, bukan pada tagihan.
- Periode tagihan = bulan kalender, tanpa prorata, nominal memakai harga snapshot.
- Beberapa pengajuan boleh mengantre untuk kamar yang sama; yang disetujui lebih dulu
  menang dan sisanya otomatis ditolak.
- Pembayaran harus pas, tidak ada cicilan; setiap unggahan tersimpan sebagai riwayat.
- Pendapatan dihitung saat verifikasi (basis kas), konsisten di dashboard dan laporan.
- `pemesanan.berakhir_pada` ditambahkan agar laporan keterisian bisa dihitung.

## 15. Yang diserahkan ke builder

- Rincian komponen UI dan tata letak tiap halaman.
- Pemilihan library grafik dan dompdf (sudah disarankan di atas).
- Nama kelas service, struktur test, dan isi seeder.
- Detail konfigurasi hosting setelah penyedia dipilih.
- Cara menyusun screenshot dan format tabel laporan.
