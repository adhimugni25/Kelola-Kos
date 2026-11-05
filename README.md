# KelolaKos

Aplikasi manajemen kos-kosan multi-tenant berbasis Laravel. Satu instalasi melayani banyak
kos; setiap pengurus hanya melihat data kosnya sendiri, penghuni hanya melihat tagihan dan
sewa miliknya, dan super admin mengelola kos serta akun pengurus.

Proyek ini dibuat untuk tugas mata kuliah Keamanan Siber dengan penekanan pada alur bisnis
yang lengkap, isolasi data antar-tenant, dan penerapan kontrol keamanan yang dapat diuji.

## Fitur

### Publik
- Landing page dan halaman profil kos `/k/{slug}`.
- Daftar kamar kosong beserta harga dan fasilitas.
- Registrasi dan login untuk calon penghuni.

### Penghuni
- Mengajukan sewa kamar dan membatalkan pengajuan yang masih menunggu.
- Melihat tagihan, rincian biaya, dan status pembayaran.
- Mengunggah bukti transfer (JPG/PNG/PDF, maksimal 2 MB) ke penyimpanan privat.
- Melihat riwayat pembayaran dan alasan penolakan dari pengurus.

### Pengurus
- Dashboard keterisian kamar, pendapatan bulan berjalan, tagihan belum lunas, dan
  pembayaran yang menunggu verifikasi.
- CRUD kamar dengan unggah foto dan isolasi data per kos.
- Menyetujui/menolak pengajuan sewa, menyelesaikan masa sewa, dan mengelola tagihan.
- Membuat tagihan bulanan secara massal (idempoten) atau manual per penyewa.
- Memverifikasi atau menolak bukti pembayaran; keputusan tercatat di `audit_logs`.
- Mengelola rekening/e-wallet yang ditampilkan ke penghuni.
- Laporan pendapatan berbasis kas dengan filter tanggal, grafik Chart.js, ekspor CSV, dan PDF.
- Impor CSV untuk kamar dan penghuni aktif beserta template unduhan.

### Super admin
- CRUD data kos dan akun pengurus.
- Mengaktifkan/menonaktifkan akun serta mereset kata sandi pengurus.
- Statistik ringkas platform.

## Teknologi

- PHP 8.3+ (diuji pada PHP 8.5) dan Laravel 13.
- MySQL 8 untuk pengembangan; SQLite in-memory untuk pengujian.
- Tailwind CSS 4 dan Vite.
- Chart.js (di-bundle lewat Vite, bukan CDN) dan dompdf.
- PHPUnit 12.

## Kebutuhan sistem

- PHP 8.3 atau lebih baru dengan ekstensi `pdo`, `mbstring`, `openssl`, `fileinfo`, `gd`.
- Composer 2.
- Node.js 20+ dan npm.
- MySQL 8 (atau MariaDB yang kompatibel).
- Laragon opsional untuk pengembangan di Windows.

## Instalasi lokal

```bash
git clone https://github.com/adhimugni25/Kelola-Kos.git
cd Kelola-Kos

composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate

# Buat database MySQL bernama kelolakos, lalu sesuaikan DB_* di .env
php artisan migrate --seed
php artisan storage:link

npm install
npm run build
```

Jalankan aplikasi:

```bash
php artisan serve
# atau melalui Laragon: http://kelola-kos.test
```

Alternatif, `composer run setup` menjalankan instalasi dasar (tanpa seeding).

## Akun demo

Seeder membuat dua kos, 12 kamar, enam penghuni, dan tiga bulan riwayat tagihan. Seluruh
akun demo memakai kata sandi `Password123!` dan **hanya untuk lingkungan lokal/demo**.

| Peran | Email |
|---|---|
| Pengurus Kos Melati | `pengurus.melati@kelolakos.test` |
| Pengurus Kos Anggrek | `pengurus.anggrek@kelolakos.test` |
| Penghuni | `budi.santoso@kelolakos.test` |
| Penghuni | `siti.aminah@kelolakos.test` |
| Penghuni | `dewi.lestari@kelolakos.test` |

Super admin tidak dibuat oleh seeder. Buat lewat perintah interaktif agar kata sandinya
tidak tersimpan di kode:

```bash
php artisan user:create-super-admin admin@kelolakos.test
```

## Variabel lingkungan

Salin `.env.example` lalu isi nilainya. Variabel penting (nilai rahasia tidak pernah
di-commit):

| Variabel | Keterangan |
|---|---|
| `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL` | Identitas dan mode aplikasi. Produksi: `APP_ENV=production`, `APP_DEBUG=false`. |
| `APP_TIMEZONE`, `APP_LOCALE`, `APP_FAKER_LOCALE` | Zona waktu dan lokalisasi (`Asia/Jakarta`, `id`, `id_ID`). |
| `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Koneksi database. |
| `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_SECURE_COOKIE`, `SESSION_SAME_SITE` | Sesi database, idle 30 menit, cookie `Secure` wajib aktif di HTTPS. |
| `CACHE_STORE`, `QUEUE_CONNECTION` | Penyimpanan cache dan antrean. |
| `FILESYSTEM_DISK` | Disk default; bukti pembayaran selalu memakai disk privat `bukti`. |
| `MAIL_*` | Pengiriman email (opsional pada demo). |
| `VITE_APP_NAME` | Nama aplikasi untuk aset frontend. |

## Menjalankan pengujian

```bash
php artisan test --compact
```

Pengujian memakai SQLite in-memory sehingga tidak menyentuh database pengembangan. Untuk
menjalankan sebagian test:

```bash
php artisan test --filter=SecurityHardeningTest --compact
php artisan test tests/Feature/Import --compact
```

Perintah pendukung:

```bash
vendor/bin/pint --dirty --format agent   # format kode PHP
composer audit                           # audit dependensi PHP
npm audit                                # audit dependensi JavaScript
```

## Struktur singkat

```
app/
  Http/Controllers/        controller per peran dan fitur
  Http/Middleware/         SecurityHeaders, EnsureRole, BatasiMasaSesi
  Http/Requests/           validasi form
  Models/                  model Eloquent + TenantScope
  Policies/                otorisasi per model
  Services/                BookingService, BillingService, PaymentService, ReportService, ImportService
  Support/                 TenantScope dan ImportResult
database/
  migrations/              skema basis data
  factories/               data uji
  seeders/                 data demo realistis
resources/views/           tampilan Blade (bahasa Indonesia)
tests/Feature/             test fitur per domain
docs/superpowers/          spesifikasi dan rencana kerja
docs/ai-usage.md           ringkasan penggunaan AI
```

## Keamanan yang diterapkan

- Kata sandi di-hash dengan bcrypt (cost 12) melalui cast `hashed`.
- Validasi input memakai Form Request; query memakai Eloquent binding, bukan `DB::raw`
  dengan input pengguna.
- Output Blade memakai `{{ }}`. Satu-satunya `{!! !!}` adalah JSON grafik yang sudah
  di-encode dengan `JSON_HEX_TAG` dan tidak berisi input pengguna mentah.
- CSP tanpa `unsafe-inline` pada `script-src`; HSTS dikirim hanya pada permintaan HTTPS.
- CSRF aktif pada seluruh form; POST tanpa token menghasilkan 419.
- Rate limit: login 5/menit per email+IP, registrasi 3/menit, unggah bukti 10/menit,
  impor CSV 3/jam.
- Isolasi tenant melalui global scope `kos_id`, Policy, dan pengecekan kepemilikan di
  controller/service.
- Bukti pembayaran disimpan di disk privat dan hanya dapat diakses pemilik atau pengurus
  kos terkait.
- Unggah file divalidasi MIME, ekstensi, dan ukuran; nama file diacak.
- Sesi: `HttpOnly`, `SameSite=Lax`, idle 30 menit, batas absolut 12 jam.
- Audit log untuk login, verifikasi pembayaran, dan impor data.
- `composer audit` dan `npm audit` bersih tanpa kerentanan yang diketahui.

Catatan CSP: `style-src` masih memuat `'unsafe-inline'` sebagai kompromi agar komponen
antarmuka dan Chart.js tidak rusak saat demo; `script-src` tetap ketat.

## Alur demo yang disarankan

1. Login sebagai super admin, buat kos dan akun pengurus.
2. Login sebagai pengurus, tambahkan kamar (atau impor CSV).
3. Buka halaman publik kos dan ajukan sewa sebagai calon penghuni.
4. Setujui pengajuan; tagihan pertama dibuat otomatis oleh `BookingService`.
5. Buat tagihan bulan berjalan; penghuni mengunggah bukti transfer.
6. Verifikasi pembayaran; tagihan berubah menjadi lunas dan audit log tercatat.
7. Buka dashboard dan laporan; unduh CSV/PDF.

## Deployment

Checklist produksi:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Konfigurasi produksi pada .env
# APP_ENV=production, APP_DEBUG=false, APP_URL=https://domain-anda
# SESSION_SECURE_COOKIE=true, DB_* sesuai server

php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan user:create-super-admin admin@domain-anda
```

Setelah deploy:

- Arahkan document root ke folder `public/`.
- Aktifkan HTTPS dan redirect HTTP → HTTPS (termasuk konfigurasi trusted proxy bila di
  balik reverse proxy).
- Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis oleh web server.
- Uji `securityheaders.com` (target A) dan SSL Labs (target A).
- Jalankan `composer audit` dan `npm audit`.
- Ekspor database sebelum demo sebagai cadangan.

## Dokumentasi tambahan

- Spesifikasi: [docs/superpowers/specs/2026-11-04-kelolakos-design.md](docs/superpowers/specs/2026-11-04-kelolakos-design.md)
- Rencana implementasi: [docs/superpowers/plans/2026-11-04-kelolakos-mvp.md](docs/superpowers/plans/2026-11-04-kelolakos-mvp.md)
- Penggunaan AI: [docs/ai-usage.md](docs/ai-usage.md)
