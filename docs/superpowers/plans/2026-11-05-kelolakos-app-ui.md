# KelolaKos — Redesign UI Halaman Aplikasi (Tahap 2) — Implementation Plan

Tanggal: 5 November 2026
Spec pengikat: `docs/superpowers/specs/2026-11-05-kelolakos-ui-design.md` (§9 Komponen halaman aplikasi, §10 Gerak, §11 Aksesibilitas, §13 Pemetaan)
Token: sudah ada di `resources/css/app.css` (`ink`, `stone-*`, `gold`, dll).

---

## Ringkasan implementasi

- Membuat kelas komponen bersama sesuai spec §9: tombol (primary/gold/outline/ghost/danger), form (label di atas, input 44px, fokus emas), tabel (sticky header, hover, angka rata kanan, badge status), empty state, grafik.
- Mengganti shell `layouts.app` agar satu bahasa dengan halaman publik (tanpa emerald), navigasi sesuai peran.
- Mengaplikasikan komponen ke 3 area peran secara bertahap: penghuni → pengurus → superadmin → auth.
- semua alat (tooling) di uji terlebih dahulu (TDD): test sudah mementing perubahan; CSS via suite tetap hijau + `npm run build`.

### Task 1: Komponen bersama (tombol, badge, tabel, form, empty state)

**Files:**
- Modify: `resources/css/app.css`
- Test: suite penuh tetap hijau + `npm run build`

- [x] Kelas `.btn-primary/.btn-gold/.btn-outline/.btn-ghost/.btn-danger`, tinggi ≥ 44px.
- [x] Kelas `.field`, `.field-input`, `.field-error` (label di atas, 44px, fokus emas 2px, `aria-describedby`).
- [x] Kelas `.app-table` (sticky header `paper-2`, hover `stone-1`, `.td-num` rata kanan, `.date`).
- [x] Badge `.badge-kosong/.badge-terisi/.badge-perbaikan/.badge-lunas/.badge-belum-bayar/.badge-menunggu/.badge-ditolak` (teks, bukan warna saja).
- [x] `.empty-state-app` (ikon garis 32px: `app-empty-icon`), satu aksi utama.
- [x] Kelas `.app-shell`, `.app-header`, `.app-nav`, `.app-user` (mirip shell publik).

### Task 2: Shell halaman aplikasi (`layouts.app`)

**Files:**
- Modify: `resources/views/layouts/app.blade.php`, `resources/css/app.css`
- Test: test feature peran yang ada tetap hijau.

- [ ] Header memakai `.app-header` + token, tanpa emerald.
- [ ] Label brand + role user + tombol Keluar.
- [ ] Alert sukses/gagal memakai token (bukan emerald).

### Task 3: Area penghuni

**Files:**
- `resources/views/penghuni/dashboard.blade.php`, `pemesanan/index.blade.php`, `pemesanan/create.blade.php`, `tagihan/index.blade.php`, `tagihan/show.blade.php`.

- [ ] Dashboard: kartu KPI + daftar tagihan.
- [ ] Pemesanan/tiga halaman: tabel spec + empty state, form spec.

### Task 4: Area pengurus — kamar & pemesanan

**Files:**
- `resources/views/pengurus/dashboard.blade.php`, `kamar/index.blade.php`, `kamar/create.blade.php`, `kamar/edit.blade.php`, `pemesanan/index.blade.php`, `pemesanan/show.blade.php`.

- [ ] Dashboard: tile 6/12 terisi + grafik.
- [ ] Kamar: tabel + badge status + edit form spec.

### Task 5: Area pengurus — tagihan & pembayaran

**Files:**
- `resources/views/pengurus/tagihan/*.blade.php`, `pembayaran/index.blade.php`, `metode-pembayaran/index.blade.php`.

### Task 6: Area pengurus — laporan, impor

**Files:**
- `resources/views/pengurus/laporan/index.blade.php`, `impor/index.blade.php`.

### Task 7: Superadmin

**Files:**
- `resources/views/superadmin/dashboard.blade.php`, `kos/*.blade.php`, `pengurus/*.blade.php`.

### Task 8: Halaman auth

**Files:**
- `resources/views/auth/*.blade.php`

- [ ] Form spec, pesan error `aria-describedby`.

### Task 9: Verifikasi akhir

- [ ] `php artisan test --compact` lulus.
- [ ] `vendor/bin/pint --dirty --format agent` bersih.
- [ ] `npm run build` sukses.
- [ ] Cek manual 1440/768/375.
- [ ] Checklist spec §9 + §11 lolos.

---

## Checklist penerimaan

- [ ] Token identik dengan halaman publik — satu palet, tanpa emerald.
- [ ] Semua tombol ≥ 44px, fokus terlihat, `prefers-reduced-motion` dihormati.
- [ ] Tabel tidak terpotong di mobile (overflow-x), badge status punya teks.
- [ ] Input punya label terlihat dan error `aria-describedby`.
- [ ] Tanpa emoji sebagai ikon (SVG inline monokrom).
