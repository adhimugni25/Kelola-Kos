# KelolaKos Homepage Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ganti halaman publik `/` dengan desain monokrom + emas, peta Indonesia interaktif, data kos nyata, dan navigasi responsif yang aman terhadap CSP.

**Architecture:** Layout publik baru (`layouts.public`) supaya halaman pemasaran tidak terikat container aplikasi. `HomeController` menyiapkan agregat dan data peta; `KotaPeta` memetakan nama kota ke koordinat stilasi; `Rupiah` memformat harga. Interaksi peta memakai radio + label + CSS `:has()` dengan selector per kota yang dihasilkan Blade, jadi tetap bekerja tanpa JavaScript. Drawer mobile memakai JavaScript yang sudah di-bundle Vite.

**Tech Stack:** Laravel 13, PHP 8.3+, Blade, Tailwind CSS 4, Vite 8, PHPUnit 12, SQLite in-memory.

**Spec:** `docs/superpowers/specs/2026-11-05-kelolakos-ui-design.md`

**Scope:** Hanya tahap 1 (homepage, layout publik, navigasi, footer). Halaman dashboard, tabel, dan form aplikasi dikerjakan di plan tahap 2.

## Global Constraints

- Semua teks UI Bahasa Indonesia.
- Tidak ada inline `<script>`, `onclick`, atau event handler HTML. JavaScript hanya lewat `resources/js/app.js`.
- CSP saat ini mengizinkan `style-src 'self' 'unsafe-inline'`; atribut `style` untuk koordinat peta boleh dipakai.
- Tidak memakai Google Fonts atau CDN. Font: Inter/system untuk body, Georgia untuk display, ui-monospace untuk label.
- Ukuran body minimal 14 px; label micro minimal 11 px; target sentuh minimal 44 × 44 px.
- Uang memakai `decimal` dan format Indonesia: `Rp1.050.000` (penuh), `Rp1,05 jt` (ringkas).
- Data publik hanya dari `kos` aktif. Jangan menampilkan kos nonaktif, tagihan asli, atau data pribadi.
- Angka pratinjau dashboard diberi label "Contoh tampilan · bukan data produksi".
- Semua perubahan PHP diakhiri `vendor/bin/pint --dirty --format agent`.
- Setiap task diakhiri commit dengan pesan yang tertulis di task, lalu `git push origin main`.

## File Structure

```
app/
  Http/Controllers/Public/HomeController.php      agregat homepage
  Support/KotaPeta.php                            koordinat + grouping kota
  Support/Rupiah.php                              format harga
config/
  kelolakos.php                                   kontak admin opsional
resources/
  css/app.css                                     token + komponen publik
  js/app.js                                       drawer mobile
  views/
    layouts/public.blade.php                      shell publik + nav + footer
    public/landing.blade.php                      susunan section
    public/partials/home/
      hero.blade.php
      map.blade.php
      map-styles.blade.php                        selector :has per kota
      indonesia-map.blade.php                     SVG stilasi
      rooms.blade.php
      dashboard-preview.blade.php
      faq.blade.php
      cta.blade.php
    public/partials/site-footer.blade.php
tests/
  Feature/Public/HomePageTest.php
  Unit/KotaPetaTest.php
  Unit/RupiahTest.php
```

---

## Review Focus

1. **Pengurus login membuka homepage.** Global scope `TenantScope` pada `Kamar`/`Pemesanan` bisa membuat angka dan kamar kos hanya milik kos pengurus. Homepage harus tetap menampilkan seluruh jaringan.
2. **Kos di kota yang tidak ada di tabel koordinat.** Kota tidak boleh menghasilkan pin rusak; namanya tetap muncul di daftar teks.
3. **Database kosong atau tidak ada kamar kosong.** Tidak boleh ada `null->first()`, pembagian nol, atau section kosong tanpa penjelasan.
4. **Kamar tanpa foto.** Harus muncul placeholder netral, bukan gambar rusak atau foto palsu.
5. **Harga desimal dan rentang harga.** `1050000.00` harus menjadi `Rp1,05 jt` / `Rp1.050.000`, dan rentang tidak boleh terbalik saat salah satu batas null.

---

### Task 1: Token, layout publik, dan drawer navigasi

**Files:**
- Modify: `resources/css/app.css`
- Create: `resources/views/layouts/public.blade.php`
- Modify: `resources/views/public/landing.blade.php`
- Modify: `resources/js/app.js`
- Test: `tests/Feature/Public/HomePageTest.php`

**Interfaces:**
- Consumes: route `home`, `login`, `register`, `logout`; helper `auth()->user()->isSuperAdmin()`, `isPengurus()`.
- Produces: layout `layouts.public` dengan `@yield('title')`, `@yield('content')`, `@stack('styles')`; token Tailwind `ink`, `ink-2`, `ink-3`, `paper`, `paper-2`, `stone-1..4`, `muted`, `muted-2`, `gold`, `gold-ink`, `gold-2`, `gold-soft`, `font-display`, `rounded-kos-xl/lg/md`, `shadow-kos-card/float`; kelas `.public-shell`, `.site-header`, `.brand-mark`, `.nav-pill`, `.nav-link`, `.pill-dark`, `.burger`, `.site-drawer`, `.skip-link`.

- [x] **Step 1: Write the failing layout test**

Tambahkan ke `tests/Feature/Public/HomePageTest.php`:

```php
<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_has_skip_link_and_guest_navigation(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Lewati ke konten')
            ->assertSee('Beranda')
            ->assertSee('Jaringan')
            ->assertSee('Kamar')
            ->assertSee('Pengurus')
            ->assertSee('FAQ')
            ->assertSee('Masuk')
            ->assertSee('Daftar');
    }

    public function test_public_navigation_has_accessible_mobile_drawer(): void
    {
        $this->get('/')
            ->assertSee('data-menu-toggle', false)
            ->assertSee('aria-controls="menu-publik"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('id="menu-publik" hidden', false);
    }
}
```

- [x] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php`
Expected: FAIL karena `Lewati ke konten` dan penanda drawer belum ada.

- [x] **Step 3: Add the token layer to `resources/css/app.css`**

Tambahkan setelah `@source`:

```css
@theme {
    --font-sans: 'Inter', ui-sans-serif, system-ui, 'Segoe UI', sans-serif;
    --font-display: Georgia, 'Times New Roman', serif;
    --font-mono: ui-monospace, 'Cascadia Mono', Consolas, monospace;

    --color-ink: #0C0A09;
    --color-ink-2: #1C1917;
    --color-ink-3: #292524;
    --color-paper: #FFFFFF;
    --color-paper-2: #FAFAF9;
    --color-stone-1: #F5F5F4;
    --color-stone-2: #E7E5E4;
    --color-stone-3: #D6D3D1;
    --color-stone-4: #A8A29E;
    --color-muted: #71717A;
    --color-muted-2: #57534E;
    --color-gold: #A16207;
    --color-gold-ink: #8A5306;
    --color-gold-2: #D4AF37;
    --color-gold-soft: #F7EBD1;

    --radius-kos-xl: 24px;
    --radius-kos-lg: 16px;
    --radius-kos-md: 12px;

    --shadow-kos-card: 0 10px 24px rgba(12, 10, 9, .06);
    --shadow-kos-float: 0 22px 48px rgba(12, 10, 9, .16);
}
```

Tambahkan juga `@layer base` untuk `.public-shell` (latar gradien `paper-2` ke `stone-2`), `section[id] { scroll-margin-top: 72px; }`, dan `.public-shell :focus-visible { outline: 2px solid var(--color-gold); outline-offset: 2px; }`. Jangan mengubah latar `body` global karena layout aplikasi masih memakai `bg-slate-50`.

Untuk bentuk visual komponen (gradien header, blur, pill nav, tombol `pill-dark`, burger, skip link), mockup yang sudah disetujui adalah acuan: `.superpowers/brainstorm/366-1793860739/content/homepage-map.html` bagian `<style>`. Ambil nilai visualnya, tetapi tetap pakai nama kelas dan token di plan ini; jangan menyalin struktur demo atau data contoh dari mockup.

- [x] **Step 4: Create `resources/views/layouts/public.blade.php`**

Struktur wajib:

```blade
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'KelolaKos')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased">
    <div class="public-shell">
        <a class="skip-link" href="#konten">Lewati ke konten</a>

        <header class="site-header">
            <div class="site-header-inner">
                <a class="brand" href="{{ route('home') }}">
                    <span class="brand-mark">K</span>KelolaKos
                </a>

                <nav class="nav-pill" aria-label="Navigasi utama">
                    <a class="nav-link is-active" aria-current="page" href="#beranda">Beranda</a>
                    <a class="nav-link" href="#jaringan">Jaringan</a>
                    <a class="nav-link" href="#kamar">Kamar</a>
                    <a class="nav-link" href="#pengurus">Pengurus</a>
                    <a class="nav-link" href="#faq">FAQ</a>
                </nav>

                <div class="nav-actions">
                    @auth
                        @php
                            $dashboardRoute = auth()->user()->isSuperAdmin()
                                ? route('admin.dashboard')
                                : (auth()->user()->isPengurus() ? route('pengurus.dashboard') : route('penghuni.dashboard'));
                        @endphp
                        <a class="nav-masuk" href="{{ $dashboardRoute }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="pill-dark" type="submit">Keluar</button>
                        </form>
                    @else
                        <a class="nav-masuk" href="{{ route('login') }}">Masuk</a>
                        <a class="pill-dark" href="{{ route('register') }}">Daftar</a>
                    @endauth

                    <button class="burger" type="button" data-menu-toggle
                            aria-label="Buka menu" aria-controls="menu-publik" aria-expanded="false">
                        <i></i><i></i><i></i>
                    </button>
                </div>
            </div>

            <div class="site-drawer" id="menu-publik" hidden>
                <a href="#beranda">Beranda</a>
                <a href="#jaringan">Jaringan</a>
                <a href="#kamar">Kamar</a>
                <a href="#pengurus">Pengurus</a>
                <a href="#faq">FAQ</a>
                <a href="{{ route('login') }}">Masuk</a>
                <p>Akun pengurus dibuat oleh admin.</p>
            </div>
        </header>

        <main id="konten">
            @if (session('status'))
                <div class="alert alert-sukses">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-gagal">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
```

Ganti isi `resources/views/public/landing.blade.php` sementara supaya memakai layout baru dan hanya berisi `<div id="beranda"></div>`; section lengkap ditambahkan di task berikutnya.

Di dalam drawer, pakai cabang `@auth` yang sama seperti nav: pengguna yang sudah masuk melihat tautan Dashboard, bukan tautan Masuk. Jangan menampilkan tombol Keluar di dalam drawer karena tombol itu membutuhkan form `@csrf`; cukup arahkan ke Dashboard.

- [x] **Step 5: Add drawer JavaScript to `resources/js/app.js`**

Tambahkan sebelum kode Chart.js:

```js
const tombolMenu = document.querySelector('[data-menu-toggle]');
const menuPublik = document.getElementById('menu-publik');

if (tombolMenu !== null && menuPublik !== null) {
    const tutupMenu = () => {
        menuPublik.hidden = true;
        tombolMenu.setAttribute('aria-expanded', 'false');
    };

    tombolMenu.addEventListener('click', () => {
        const akanDibuka = tombolMenu.getAttribute('aria-expanded') !== 'true';
        menuPublik.hidden = !akanDibuka;
        tombolMenu.setAttribute('aria-expanded', String(akanDibuka));

        if (akanDibuka) {
            menuPublik.querySelector('a')?.focus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !menuPublik.hidden) {
            tutupMenu();
            tombolMenu.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!menuPublik.hidden
            && !menuPublik.contains(event.target)
            && !tombolMenu.contains(event.target)) {
            tutupMenu();
        }
    });
}
```

- [x] **Step 6: Run the tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php`
Expected: PASS (2 tests).

- [x] **Step 7: Commit**

```bash
git add resources/css/app.css resources/js/app.js resources/views/layouts/public.blade.php resources/views/public/landing.blade.php tests/Feature/Public/HomePageTest.php
git commit -m "feat: add public design tokens, layout, and responsive navigation"
```

---

### Task 2: Rupiah formatting helper

**Files:**
- Create: `app/Support/Rupiah.php`
- Test: `tests/Unit/RupiahTest.php`

**Interfaces:**
- Produces: `Rupiah::penuh(float|int|string|null $nilai): string` dan `Rupiah::ringkas(float|int|string|null $nilai): string`. Nilai null menghasilkan `—`.

- [x] **Step 1: Write the failing unit test**

```php
<?php

namespace Tests\Unit;

use App\Support\Rupiah;
use PHPUnit\Framework\TestCase;

class RupiahTest extends TestCase
{
    public function test_formats_full_rupiah(): void
    {
        $this->assertSame('Rp1.050.000', Rupiah::penuh(1050000));
        $this->assertSame('Rp0', Rupiah::penuh(0));
        $this->assertSame('—', Rupiah::penuh(null));
    }

    public function test_formats_compact_rupiah(): void
    {
        $this->assertSame('Rp1,05 jt', Rupiah::ringkas(1050000));
        $this->assertSame('Rp950 rb', Rupiah::ringkas(950000));
        $this->assertSame('Rp1,2 jt', Rupiah::ringkas(1200000));
        $this->assertSame('Rp1 jt', Rupiah::ringkas(1000000));
        $this->assertSame('—', Rupiah::ringkas(null));
    }
}
```

- [x] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Unit/RupiahTest.php`
Expected: FAIL karena kelas `App\Support\Rupiah` belum ada.

- [x] **Step 3: Implement `app/Support/Rupiah.php`**

```php
<?php

namespace App\Support;

final class Rupiah
{
    public static function penuh(float|int|string|null $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return '—';
        }

        return 'Rp'.number_format((float) $nilai, 0, ',', '.');
    }

    public static function ringkas(float|int|string|null $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return '—';
        }

        $angka = (float) $nilai;

        if ($angka >= 1_000_000) {
            return 'Rp'.rtrim(rtrim(number_format($angka / 1_000_000, 2, ',', '.'), '0'), ',').' jt';
        }

        if ($angka >= 1_000) {
            return 'Rp'.number_format($angka / 1_000, 0, ',', '.').' rb';
        }

        return self::penuh($angka);
    }
}
```

- [x] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact tests/Unit/RupiahTest.php`
Expected: PASS (2 tests).

- [x] **Step 5: Commit**

```bash
git add app/Support/Rupiah.php tests/Unit/RupiahTest.php
git commit -m "feat: add Indonesian rupiah formatting helper"
```

---

### Task 3: KotaPeta coordinates and city grouping

**Files:**
- Create: `app/Support/KotaPeta.php`
- Test: `tests/Unit/KotaPetaTest.php`

**Interfaces:**
- Produces:
  - `KotaPeta::normalisasi(?string $kota): string`
  - `KotaPeta::koordinat(?string $kota): ?array{x: float, y: float}`
  - `KotaPeta::rangkum(Collection<int, Kos> $daftarKos): Collection<int, array{kota: string, slug: string, x: float, y: float, jumlah_kos: int, jumlah_kamar: int, jumlah_kosong: int, jumlah_penghuni: int, harga_min: float|null, harga_max: float|null, kos: array<int, array{nama: string, slug: string}>, jumlah_kos_lainnya: int}>`
- Input `Kos` harus sudah punya atribut agregat `jumlah_kamar`, `jumlah_kosong`, `jumlah_penghuni`, `harga_min`, `harga_max`.

- [x] **Step 1: Write the failing coordinate test**

```php
<?php

namespace Tests\Unit;

use App\Models\Kos;
use App\Support\KotaPeta;
use PHPUnit\Framework\TestCase;

class KotaPetaTest extends TestCase
{
    public function test_maps_known_city_names_case_insensitively(): void
    {
        $this->assertSame(['x' => 42.8, 'y' => 79.4], KotaPeta::koordinat(' Yogyakarta '));
        $this->assertSame(['x' => 42.8, 'y' => 79.4], KotaPeta::koordinat('jogja'));
        $this->assertSame(['x' => 51.3, 'y' => 78.2], KotaPeta::koordinat('SURABAYA'));
    }

    public function test_returns_null_for_unknown_city(): void
    {
        $this->assertNull(KotaPeta::koordinat('Kota Fiktif'));
        $this->assertNull(KotaPeta::koordinat(null));
    }

    public function test_groups_cities_and_keeps_canonical_name(): void
    {
        $hasil = KotaPeta::rangkum(collect([
            $this->kos('Kos Melati', 'kos-melati', 'Jogja', 6, 2, 3, 950000, 1200000),
            $this->kos('Kos Anggrek', 'kos-anggrek', 'Yogyakarta', 6, 2, 3, 900000, 1100000),
            $this->kos('Kos Kenanga', 'kos-kenanga', 'Jakarta', 8, 2, 6, 1200000, 1800000),
            $this->kos('Kos Antah', 'kos-antah', 'Kota Fiktif', 4, 1, 3, 700000, 800000),
        ]));

        $this->assertCount(2, $hasil);
        $this->assertSame('Yogyakarta', $hasil[0]['kota']);
        $this->assertSame('yogyakarta', $hasil[0]['slug']);
        $this->assertSame(2, $hasil[0]['jumlah_kos']);
        $this->assertSame(12, $hasil[0]['jumlah_kamar']);
        $this->assertSame(4, $hasil[0]['jumlah_kosong']);
        $this->assertSame(6, $hasil[0]['jumlah_penghuni']);
        $this->assertSame(900000.0, $hasil[0]['harga_min']);
        $this->assertSame(1200000.0, $hasil[0]['harga_max']);
        $this->assertSame(2, count($hasil[0]['kos']));
    }

    public function test_keeps_null_price_range_when_city_has_no_available_room(): void
    {
        $hasil = KotaPeta::rangkum(collect([
            $this->kos('Kos Penuh', 'kos-penuh', 'Bandung', 6, 0, 6, null, null),
        ]));

        $this->assertCount(1, $hasil);
        $this->assertNull($hasil[0]['harga_min']);
        $this->assertNull($hasil[0]['harga_max']);
        $this->assertSame(0, $hasil[0]['jumlah_kosong']);
    }

    private function kos(
        string $nama,
        string $slug,
        string $kota,
        int $kamar,
        int $kosong,
        int $penghuni,
        ?float $hargaMin,
        ?float $hargaMax,
    ): Kos {
        $kos = new Kos(['nama' => $nama, 'slug' => $slug, 'kota' => $kota]);
        $kos->setAttribute('jumlah_kamar', $kamar);
        $kos->setAttribute('jumlah_kosong', $kosong);
        $kos->setAttribute('jumlah_penghuni', $penghuni);
        $kos->setAttribute('harga_min', $hargaMin);
        $kos->setAttribute('harga_max', $hargaMax);

        return $kos;
    }
}
```

- [x] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Unit/KotaPetaTest.php`
Expected: FAIL karena `App\Support\KotaPeta` belum ada.

- [x] **Step 3: Implement `app/Support/KotaPeta.php`**

Gunakan tabel koordinat dari spec bagian 8.2. Normalisasi: `strtolower(trim($kota))`, lalu alias `jogja`/`jogjakarta` → `yogyakarta`, `jakarta raya`/`dki jakarta` → `jakarta`, `denpasar bali` → `denpasar`. `rangkum()`:

1. Buang kos yang `koordinat($kos->kota) === null`.
2. `groupBy(fn (Kos $kos) => self::normalisasi($kos->kota))`.
3. Nama kota kanonik memakai `Str::title($kunci)`; `slug` memakai `Str::slug($kunci)`.
4. Jumlahkan `jumlah_kamar`, `jumlah_kosong`, `jumlah_penghuni`; min/max harga dari `pluck('harga_min')`/`pluck('harga_max')` yang tidak null.
5. `kos` berisi maksimal 3 `['nama' => ..., 'slug' => ...]`; `jumlah_kos_lainnya = max(0, total - 3)`.
6. Urutkan `jumlah_kos` desc lalu `kota` asc, dan `values()`.

- [x] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact tests/Unit/KotaPetaTest.php`
Expected: PASS (3 tests).

- [x] **Step 5: Commit**

```bash
git add app/Support/KotaPeta.php tests/Unit/KotaPetaTest.php
git commit -m "feat: add stylized Indonesia city map data"
```

---

### Task 4: Homepage aggregates and available rooms

**Files:**
- Modify: `app/Http/Controllers/Public/HomeController.php`
- Test: `tests/Feature/Public/HomePageTest.php`

**Interfaces:**
- Produces view data: `jumlahKota:int`, `jumlahKos:int`, `jumlahKamar:int`, `jumlahKosong:int`, `ringkasanKamar:array{terisi:int,perbaikan:int,kosong:int}`, `hargaMin:float|null`, `hargaMax:float|null`, `kamarKosong:Collection<int,Kamar>`, `kotaUtama:string|null`.
- `kamarKosong` berisi maksimal 3 kamar `kosong` dari kos aktif, urut harga lalu id.

- [x] **Step 1: Write the failing aggregate test**

Tambahkan import `Kamar`, `Kos`, `Pemesanan`, `User` dan test berikut:

```php
public function test_homepage_uses_real_aggregates_and_skips_inactive_kos(): void
{
    $melati = $this->buatKos('Kos Melati', 'Yogyakarta', [
        'A1' => ['terisi', 900000],
        'A2' => ['terisi', 1000000],
        'A3' => ['perbaikan', 1100000],
        'A4' => ['kosong', 950000],
        'A5' => ['kosong', 1050000],
    ]);
    $anggrek = $this->buatKos('Kos Anggrek', 'Yogyakarta', [
        'B1' => ['terisi', 900000],
        'B2' => ['kosong', 1200000],
    ]);
    $this->buatKos('Kos Kenanga', 'Jakarta', [
        'C1' => ['kosong', 800000],
    ]);
    $this->buatKos('Kos Tutup', 'Bandung', [
        'D1' => ['kosong', 700000],
    ], false);

    foreach ([[$melati, 'A1'], [$anggrek, 'B1']] as [$kos, $nomor]) {
        Pemesanan::factory()->aktif()->create([
            'kos_id' => $kos->id,
            'kamar_id' => $kos->kamar()->where('nomor', $nomor)->value('id'),
            'user_id' => User::factory()->create()->id,
        ]);
    }

    $response = $this->get('/');

    $response->assertOk();
    $response->assertViewHas('jumlahKota', 2);
    $response->assertViewHas('jumlahKos', 3);
    $response->assertViewHas('jumlahKamar', 8);
    $response->assertViewHas('jumlahKosong', 4);
    $response->assertViewHas('ringkasanKamar', ['terisi' => 3, 'perbaikan' => 1, 'kosong' => 4]);
    $response->assertViewHas(
        'kamarKosong',
        fn ($kamar) => $kamar->pluck('nomor')->all() === ['C1', 'A4', 'A5'],
    );
}

public function test_homepage_aggregates_include_all_kos_for_logged_in_pengurus(): void
{
    $melati = $this->buatKos('Kos Melati', 'Yogyakarta', [
        'A1' => ['terisi', 900000],
        'A2' => ['kosong', 950000],
    ]);
    $this->buatKos('Kos Anggrek', 'Yogyakarta', [
        'B1' => ['terisi', 900000],
        'B2' => ['kosong', 1200000],
    ]);

    $pengurus = User::factory()->pengurus($melati)->create();

    $this->actingAs($pengurus)->get('/')
        ->assertOk()
        ->assertViewHas('jumlahKos', 2)
        ->assertViewHas('jumlahKamar', 4)
        ->assertViewHas('kamarKosong', fn ($kamar) => $kamar->pluck('nomor')->contains('B2'));
}

public function test_homepage_handles_empty_database(): void
{
    $this->get('/')
        ->assertOk()
        ->assertViewHas('jumlahKos', 0)
        ->assertViewHas('jumlahKota', 0)
        ->assertViewHas('kamarKosong', fn ($kamar) => $kamar->isEmpty());
}

private function buatKos(string $nama, string $kota, array $kamar, bool $aktif = true): Kos
{
    $kos = Kos::factory()->create([
        'nama' => $nama,
        'kota' => $kota,
        'is_active' => $aktif,
    ]);

    foreach ($kamar as $nomor => [$status, $harga]) {
        Kamar::factory()->create([
            'kos_id' => $kos->id,
            'nomor' => $nomor,
            'status' => $status,
            'harga_bulanan' => $harga,
        ]);
    }

    return $kos;
}
```

- [x] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php`
Expected: FAIL karena `jumlahKota`, `jumlahKamar`, dan `kamarKosong` belum dikirim.

- [x] **Step 3: Implement the queries in `HomeController::index()`**

Gunakan `Kos::query()->where('is_active', true)` dengan `withCount`/`withMin`/`withMax`. Impor `Illuminate\Database\Eloquent\Builder`. Setiap closure agregat **wajib** memanggil `withoutGlobalScope('kos_id')` agar pengurus yang membuka homepage tetap melihat seluruh jaringan:

```php
$kosAktif = Kos::query()
    ->where('is_active', true)
    ->withCount([
        'kamar as jumlah_kamar' => fn (Builder $q) => $q->withoutGlobalScope('kos_id'),
        'kamar as jumlah_terisi' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'terisi'),
        'kamar as jumlah_perbaikan' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'perbaikan'),
        'kamar as jumlah_kosong' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'kosong'),
        'pemesanan as jumlah_penghuni' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'aktif'),
    ])
    ->withMin(['kamar as harga_min' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'kosong')], 'harga_bulanan')
    ->withMax(['kamar as harga_max' => fn (Builder $q) => $q->withoutGlobalScope('kos_id')->where('status', 'kosong')], 'harga_bulanan')
    ->get();
```

Query `kamarKosong` memakai `Kamar::withoutGlobalScope('kos_id')`, `where('status', 'kosong')`, `whereHas('kos', fn (Builder $q) => $q->where('is_active', true))`, `with('kos')`, `orderBy('harga_bulanan')`, `orderBy('id')`, `limit(3)`.

Hitung `ringkasanKamar` dari `sum()` atribut agregat:

```php
$ringkasanKamar = [
    'terisi' => (int) $kosAktif->sum('jumlah_terisi'),
    'perbaikan' => (int) $kosAktif->sum('jumlah_perbaikan'),
    'kosong' => (int) $kosAktif->sum('jumlah_kosong'),
];

$jumlahKota = $kosAktif->pluck('kota')->filter()->unique()->count();
$jumlahKamar = (int) $kosAktif->sum('jumlah_kamar');
$hargaMin = $kosAktif->pluck('harga_min')->filter()->min();
$hargaMax = $kosAktif->pluck('harga_max')->filter()->max();
$hargaMin = $hargaMin !== null ? (float) $hargaMin : null;
$hargaMax = $hargaMax !== null ? (float) $hargaMax : null;
```

`kotaUtama` = nama kota bila `jumlahKota === 1`, selain itu `'Indonesia'`. Kirim semua variabel ke view.

- [x] **Step 4: Run the tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php`
Expected: PASS (5 tests).
Run: `php artisan test --compact tests/Feature/Public/PublicKosTest.php`
Expected: PASS, memastikan halaman publik kos tidak rusak.

- [x] **Step 5: Commit**

```bash
git add app/Http/Controllers/Public/HomeController.php tests/Feature/Public/HomePageTest.php
git commit -m "feat: load homepage aggregates from active kos data"
```

---

### Task 5: Map data and city fallback

**Files:**
- Modify: `app/Http/Controllers/Public/HomeController.php`
- Test: `tests/Feature/Public/HomePageTest.php`

**Interfaces:**
- Consumes: `KotaPeta::rangkum()`, `KotaPeta::koordinat()`.
- Produces view data: `petaKota:Collection`, `kotaTanpaPeta:Collection<int,string>`, `kotaUtama:string`.
- `petaKota` sudah terurut: jumlah kos terbanyak lebih dulu, lalu nama kota.

- [x] **Step 1: Write the failing map-data test**

```php
public function test_homepage_builds_map_data_and_lists_unknown_cities(): void
{
    $this->buatKos('Kos Melati', 'Yogyakarta', ['A1' => ['kosong', 950000]]);
    $this->buatKos('Kos Anggrek', 'Yogyakarta', ['B1' => ['kosong', 1200000]]);
    $this->buatKos('Kos Kenanga', 'Jakarta', ['C1' => ['kosong', 800000]]);
    $this->buatKos('Kos Antah', 'Kota Fiktif', ['D1' => ['kosong', 700000]]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertViewHas('jumlahKota', 3);
    $response->assertViewHas('kotaUtama', 'Indonesia');
    $response->assertViewHas('petaKota', function ($peta): bool {
        return $peta->count() === 2
            && $peta->first()['kota'] === 'Yogyakarta'
            && $peta->first()['jumlah_kos'] === 2
            && $peta->first()['jumlah_kosong'] === 2;
    });
    $response->assertViewHas('kotaTanpaPeta', fn ($kota) => $kota->contains('Kota Fiktif'));
}
```

- [x] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php --filter=builds_map_data`
Expected: FAIL karena `petaKota` belum ada.

- [x] **Step 3: Add map data to the controller**

```php
$petaKota = KotaPeta::rangkum($kosAktif);

$kotaTanpaPeta = $kosAktif
    ->pluck('kota')
    ->filter()
    ->reject(fn (string $kota): bool => KotaPeta::koordinat($kota) !== null)
    ->unique(fn (string $kota): string => KotaPeta::normalisasi($kota))
    ->sort()
    ->values();

$kotaUtama = $jumlahKota === 1
    ? ($kosAktif->first()?->kota ?? 'Indonesia')
    : 'Indonesia';
```

Kirim ketiganya ke view. Jangan mengubah `jumlahKota`: kota tanpa koordinat tetap dihitung di statistik.

- [x] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php`
Expected: PASS (6 tests).

- [x] **Step 5: Commit**

```bash
git add app/Http/Controllers/Public/HomeController.php tests/Feature/Public/HomePageTest.php
git commit -m "feat: prepare interactive map data with unknown-city fallback"
```

---

### Task 6: Hero and availability panel

**Files:**
- Create: `resources/views/public/partials/home/hero.blade.php`
- Modify: `resources/views/public/landing.blade.php`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/Public/HomePageTest.php`

**Interfaces:**
- Consumes: `$jumlahKota`, `$jumlahKos`, `$jumlahKamar`, `$jumlahKosong`, `$kotaUtama`, `$kamarKosong`, `$hargaMin`, `$hargaMax`.
- Produces: section `id="beranda"`; anchor `#kamar`; anchor `#pengurus`.
- Blade memanggil helper dengan nama lengkap `\App\Support\Rupiah::ringkas(...)` / `::penuh(...)`, atau menambahkan `@php use App\Support\Rupiah; @endphp` di awal partial.

- [x] **Step 1: Write the failing hero test**

```php
public function test_homepage_hero_uses_real_data_instead_of_search_form(): void
{
    $this->buatKos('Kos Melati', 'Yogyakarta', [
        'A6' => ['kosong', 1050000],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Cari kamar.')
        ->assertSee('Satu tempat.')
        ->assertSee('Lihat kamar')
        ->assertSee('Untuk pengurus')
        ->assertSee('href="#kamar"', false)
        ->assertSee('href="#pengurus"', false)
        ->assertSee('Rp1,05 jt')
        ->assertSee('Rp1.050.000')
        ->assertDontSee('<input type="search"', false);
}
```

- [x] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php --filter=hero`
Expected: FAIL karena hero baru belum ada.

- [x] **Step 3: Implement the hero partial and include it**

`hero.blade.php` memuat:

- Kicker: `Kos & penghuni · {{ $kotaUtama ?? 'Indonesia' }}`.
- H1 tiga baris dengan `<em>Satu tempat.</em>`.
- Lead satu baris: "Kamar kosong, tagihan, dan bukti bayar dalam satu alur."
- Panel ketersediaan: angka `$jumlahKosong`, label "kamar kosong", kota, rentang `\App\Support\Rupiah::ringkas($hargaMin)`–`\App\Support\Rupiah::ringkas($hargaMax)`, tombol emas "Lihat kamar" ke `#kamar`, tautan "Untuk pengurus" ke `#pengurus`.
- Statistik: `$jumlahKota` kota, `$jumlahKos` kos, `$jumlahKamar` kamar.
- Visual kanan: ambil `$kamarKosong->first()`; tampilkan nomor, tipe, nama kos, harga penuh di `aria-label`, harga ringkas di pill, dan placeholder "Foto kamar belum tersedia" bila `foto_path` kosong.
- Bila `$kamarKosong` kosong, tampilkan kartu "Kamar kosong sedang tidak tersedia" tanpa memanggil `->first()->...`.

Tambahkan section ini ke `landing.blade.php` paling atas. Tambahkan CSS hero ke `@layer components` (grid `1.06fr .94fr`, breakpoint 900 px menjadi satu kolom, panel kaca, judul display, tombol emas). Jangan memakai form pencarian.

- [x] **Step 4: Run the test to verify it passes**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php --filter=hero`
Expected: PASS.

- [x] **Step 5: Commit**

```bash
git add resources/views/public/partials/home/hero.blade.php resources/views/public/landing.blade.php resources/css/app.css tests/Feature/Public/HomePageTest.php
git commit -m "feat: rebuild homepage hero with real availability data"
```

---

### Task 7: Interactive Indonesia map

**Files:**
- Create: `resources/views/public/partials/home/map.blade.php`
- Create: `resources/views/public/partials/home/map-styles.blade.php`
- Create: `resources/views/public/partials/home/indonesia-map.blade.php`
- Modify: `resources/views/public/landing.blade.php`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/Public/HomePageTest.php`

**Interfaces:**
- Consumes: `$petaKota`, `$kotaTanpaPeta`, `$jumlahKota`, `Rupiah`.
- Produces: section `id="jaringan"`; radio `id="p-{slug}"`; panel `data-kota="{slug}"`; chip `for="p-{slug}"`; fallback list `$kotaTanpaPeta`.

- [x] **Step 1: Write the failing map test**

```php
public function test_homepage_renders_interactive_map_with_city_pins(): void
{
    $this->buatKos('Kos Melati', 'Yogyakarta', [
        'A1' => ['terisi', 900000],
        'A2' => ['kosong', 950000],
    ]);
    $this->buatKos('Kos Anggrek', 'Yogyakarta', [
        'B1' => ['kosong', 1200000],
    ]);
    $this->buatKos('Kos Kenanga', 'Jakarta', [
        'C1' => ['kosong', 800000],
    ]);
    $this->buatKos('Kos Antah', 'Kota Fiktif', [
        'D1' => ['kosong', 700000],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Sudah bergabung di 3 kota')
        ->assertSee('id="p-yogyakarta"', false)
        ->assertSee('id="p-jakarta"', false)
        ->assertSee('aria-label="Yogyakarta, 2 kos, 2 kamar kosong"', false)
        ->assertSee('data-kota="yogyakarta"', false)
        ->assertSee('Kota Fiktif')
        ->assertDontSee('id="p-kota-fiktif"', false);
}

public function test_homepage_shows_map_empty_state_without_active_kos(): void
{
    $this->get('/')
        ->assertOk()
        ->assertSee('Jaringan sedang tumbuh');
}

public function test_homepage_lists_city_without_coordinate_instead_of_drawing_a_pin(): void
{
    $this->buatKos('Kos Antah', 'Kota Fiktif', [
        'D1' => ['kosong', 700000],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Jaringan sedang tumbuh')
        ->assertSee('Kota Fiktif')
        ->assertDontSee('id="p-kota-fiktif"', false);
}
```

- [x] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php --filter=map`
Expected: FAIL karena section peta belum ada.

- [x] **Step 3: Add the stylized SVG**

Ambil blok `<svg class="id-map" viewBox="0 0 760 340" ...>` dari mockup yang disetujui di `.superpowers/brainstorm/366-1793860739/content/homepage-map.html`, lalu simpan sebagai `indonesia-map.blade.php` tanpa tag `<style>`. Pertahankan `aria-hidden="true"`, `viewBox`, path pulau, dan label SUMATRA/JAWA/KALIMANTAN/SULAWESI/PAPUA. Naikkan opasitas garis menjadi minimal `.26` agar terlihat.

- [x] **Step 4: Implement `map-styles.blade.php` dan `map.blade.php`**

Masukkan blok `<style>` di bawah ke `map-styles.blade.php`, lalu panggil dari `landing.blade.php` di level atas (sebelum `@section('content')`):

```blade
@push('styles')
    @include('public.partials.home.map-styles')
@endpush
```

Ini memastikan CSS masuk ke `@stack('styles')` di head, bukan terlambat karena dirender di dalam section. Bagian `<section>` di bawah masuk ke `map.blade.php`.

```blade
<style>
    .map-info { display: none; }
    @supports selector(:has(*)) {
        @foreach ($petaKota as $kota)
            .map-panel:has(#p-{{ $kota['slug'] }}:checked) .map-info[data-kota="{{ $kota['slug'] }}"] { display: block; }
            .map-panel:has(#p-{{ $kota['slug'] }}:checked) .pin[for="p-{{ $kota['slug'] }}"] .pin-dot { transform: scale(1.3); }
            .map-panel:has(#p-{{ $kota['slug'] }}:checked) .map-cities label[for="p-{{ $kota['slug'] }}"] { background: var(--color-gold-soft); border-color: rgba(161, 98, 7, .26); color: var(--color-gold-ink); }
        @endforeach
    }
    @supports not selector(:has(*)) {
        .map-info[data-default="true"] { display: block; }
    }
</style>
{{-- Batas berkas: mulai map.blade.php --}}
<section class="section-shell" id="jaringan">
    <div class="section-head">
        <div>
            <div class="kicker">Jaringan kos</div>
            <h2>Sudah bergabung di {{ $jumlahKota }} kota</h2>
        </div>
        <span class="head-hint">Klik titik emas</span>
    </div>

    @if ($petaKota->isEmpty())
        <div class="empty-state">
            <h3>Jaringan sedang tumbuh</h3>
            <p>Belum ada kota yang bisa ditampilkan di peta.</p>
            @if ($kotaTanpaPeta->isNotEmpty())
                <p>Kota terdaftar: {{ $kotaTanpaPeta->join(', ') }}</p>
            @endif
        </div>
    @else
        <fieldset class="map-panel">
            <legend class="sr-only">Pilih kota untuk melihat kos</legend>
            <div class="map-canvas">
                @include('public.partials.home.indonesia-map')

                @foreach ($petaKota as $kota)
                    <div class="pin-slot" style="left: {{ $kota['x'] }}%; top: {{ $kota['y'] }}%;">
                        <input class="pin-input" type="radio" name="kos-map"
                               id="p-{{ $kota['slug'] }}" @checked($loop->first)>
                        <label class="pin" for="p-{{ $kota['slug'] }}"
                               aria-label="{{ $kota['kota'] }}, {{ $kota['jumlah_kos'] }} kos, {{ $kota['jumlah_kosong'] }} kamar kosong">
                            <span class="pin-dot"></span>
                            <span class="pin-ring"></span>
                            <span class="pin-tip">{{ $kota['kota'] }} · <b>{{ $kota['jumlah_kos'] }} kos</b></span>
                        </label>
                    </div>
                @endforeach
            </div>

            <aside class="map-side" aria-live="polite">
                @foreach ($petaKota as $kota)
                    <article class="map-info" data-kota="{{ $kota['slug'] }}"
                             data-default="{{ $loop->first ? 'true' : 'false' }}">
                        <div class="mi-top">
                            <span class="mi-badge">Aktif</span>
                            <span class="mi-coord">{{ $kota['jumlah_kos'] }} kos</span>
                        </div>
                        <h3>{{ $kota['kota'] }}</h3>
                        <p class="mi-names">
                            {{ collect($kota['kos'])->pluck('nama')->join(' · ') }}
                            @if ($kota['jumlah_kos_lainnya'] > 0)
                                · +{{ $kota['jumlah_kos_lainnya'] }} kos lain
                            @endif
                        </p>
                        <div class="mi-figs">
                            <div><b>{{ $kota['jumlah_kamar'] }}</b><span>kamar</span></div>
                            <div><b>{{ $kota['jumlah_kosong'] }}</b><span>kosong</span></div>
                            <div><b>{{ $kota['jumlah_penghuni'] }}</b><span>penghuni</span></div>
                        </div>
                        <div class="mi-price">
                            {{ \App\Support\Rupiah::ringkas($kota['harga_min']) }} – {{ \App\Support\Rupiah::ringkas($kota['harga_max']) }}
                            <span>/ bulan</span>
                        </div>
                        @if (! empty($kota['kos']))
                            <a class="mi-link" href="{{ route('public.kos', $kota['kos'][0]['slug']) }}">Lihat kos →</a>
                        @endif
                    </article>
                @endforeach

                <div class="map-cities">
                    <span>Kota</span>
                    @foreach ($petaKota as $kota)
                        <label for="p-{{ $kota['slug'] }}">{{ $kota['kota'] }}</label>
                    @endforeach
                </div>

                @if ($kotaTanpaPeta->isNotEmpty())
                    <p class="map-demo">Belum dipetakan: {{ $kotaTanpaPeta->join(', ') }}</p>
                @endif
                <p class="map-demo">Peta stilasi · data dari kos aktif.</p>
            </aside>
        </fieldset>
    @endif
</section>
```

Tambahkan CSS peta dari mockup: kanvas rasio `760 / 340`, grid halus, pin 44 px hit area dengan dot 10 px, tooltip, kartu info, chip 3 kolom, breakpoint 900 px dan 620 px. Pastikan `prefers-reduced-motion` mematikan `ping`/transisi.

- [x] **Step 5: Run the tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php --filter=map`
Expected: PASS (2 tests).

- [x] **Step 6: Build and visually verify the map**

Run: `npm run build`
Expected: build sukses.
Jalankan server lokal dan buka `/` pada lebar 1440 px dan 375 px. Expected: titik emas terlihat, klik/panah keyboard mengganti kartu informasi, chip kota aktif, tidak ada scroll horizontal.

- [x] **Step 7: Commit**

```bash
git add resources/views/public/partials/home/map.blade.php resources/views/public/partials/home/map-styles.blade.php resources/views/public/partials/home/indonesia-map.blade.php resources/views/public/landing.blade.php resources/css/app.css tests/Feature/Public/HomePageTest.php
git commit -m "feat: add interactive stylized Indonesia kos map"
```

---

### Task 8: Available rooms section

**Files:**
- Create: `resources/views/public/partials/home/rooms.blade.php`
- Modify: `resources/views/public/landing.blade.php`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/Public/HomePageTest.php`

**Interfaces:**
- Consumes: `$kamarKosong`, `$ringkasanKamar`, `$jumlahKosong`, `Rupiah`.
- Produces: section `id="kamar"`; tautan `route('public.kos', $kamar->kos->slug)`.

- [x] **Step 1: Write the failing rooms test**

```php
public function test_homepage_lists_available_rooms_with_placeholder_photo(): void
{
    $this->buatKos('Kos Melati', 'Yogyakarta', [
        'A1' => ['terisi', 900000],
        'A5' => ['kosong', 950000],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('kamar kosong')
        ->assertSee('A5')
        ->assertSee('Kos Melati')
        ->assertSee('Tersedia')
        ->assertSee('Rp950.000')
        ->assertSee('Foto kamar belum tersedia')
        ->assertSee('/k/kos-melati', false)
        ->assertDontSee('A1');
}

public function test_homepage_shows_rooms_empty_state(): void
{
    $this->buatKos('Kos Melati', 'Yogyakarta', [
        'A1' => ['terisi', 900000],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Belum ada kamar kosong');
}
```

- [x] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php --filter=rooms`
Expected: FAIL karena section kamar belum ada.

- [x] **Step 3: Implement `rooms.blade.php`**

Section berisi:

- Kicker "Kamar di {{ $kotaUtama }}" bila satu kota, atau "Kamar tersedia".
- Judul `{{ $jumlahKosong }} kamar kosong` (atau "Kamar kosong" bila nol).
- Bar ketersediaan dengan tiga segmen memakai `$ringkasanKamar`; lebar segmen dihitung dari total. Bila total 0, jangan bagi nol: tampilkan track kosong.
- Legenda: `{{ $ringkasanKamar['terisi'] }} terisi`, `{{ $ringkasanKamar['perbaikan'] }} perbaikan`, `{{ $ringkasanKamar['kosong'] }} kosong`.
- `@forelse ($kamarKosong as $kamar)` tiga kartu: nama kos, nomor/tipe, kota, harga `\App\Support\Rupiah::penuh`, maksimal tiga tag fasilitas (`explode(',', $kamar->fasilitas)` + `array_slice`), status "Tersedia", tautan "Ajukan" ke halaman kos.
- Foto: `@if ($kamar->foto_path)` pakai `Storage::disk('public')->url(...)` dengan alt; `@else` placeholder "Foto kamar belum tersedia".
- `@empty` empty state "Belum ada kamar kosong" + tautan ke `#jaringan`.

Tambahkan ke `landing.blade.php` setelah peta. Tambahkan CSS kartu kamar, bar ketersediaan, tag, dan breakpoint 3 → 2 → 1 kolom.

- [x] **Step 4: Run the tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php --filter=rooms`
Expected: PASS (2 tests).

- [x] **Step 5: Commit**

```bash
git add resources/views/public/partials/home/rooms.blade.php resources/views/public/landing.blade.php resources/css/app.css tests/Feature/Public/HomePageTest.php
git commit -m "feat: add available rooms section from live kamar data"
```

---

### Task 9: Dashboard preview, FAQ, CTA, footer, and final polish

**Files:**
- Create: `resources/views/public/partials/home/dashboard-preview.blade.php`
- Create: `resources/views/public/partials/home/faq.blade.php`
- Create: `resources/views/public/partials/home/cta.blade.php`
- Create: `resources/views/public/partials/site-footer.blade.php`
- Create: `config/kelolakos.php`
- Modify: `resources/views/layouts/public.blade.php`
- Modify: `resources/views/public/landing.blade.php`
- Modify: `resources/css/app.css`
- Test: `tests/Feature/Public/HomePageTest.php`

**Interfaces:**
- Consumes: `config('kelolakos.kontak_admin')`, route `login`, `register`.
- Produces: section `id="pengurus"`, `id="faq"`; footer.

- [x] **Step 1: Write the failing content test**

```php
public function test_homepage_has_labeled_dashboard_preview_faq_and_honest_cta(): void
{
    config(['kelolakos.kontak_admin' => null]);

    $this->get('/')
        ->assertOk()
        ->assertSee('Angka penting, satu layar.')
        ->assertSee('Contoh tampilan')
        ->assertSee('bukan data produksi')
        ->assertSee('Perlu akun untuk melihat kamar?')
        ->assertSee('Bagaimana cara mengajukan sewa?')
        ->assertSee('Format bukti bayar apa yang diterima?')
        ->assertSee('Laporan bisa diunduh?')
        ->assertSee('Siap merapikan kos?')
        ->assertSee('Masuk sebagai pengurus')
        ->assertSee('Buat akun penghuni')
        ->assertDontSee('Daftar sebagai pengurus')
        ->assertDontSee('0812-0000-0000');
}

public function test_homepage_uses_configured_admin_contact_for_pengurus_cta(): void
{
    config(['kelolakos.kontak_admin' => 'admin@kelolakos.test']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Hubungi admin')
        ->assertSee('mailto:admin@kelolakos.test', false);
}
```

- [x] **Step 2: Run the tests to verify they fail**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php --filter='dashboard_preview|admin_contact'`
Expected: FAIL karena section dan config belum ada.

- [x] **Step 3: Implement the three sections and footer**

`dashboard-preview.blade.php`:
- Section `id="pengurus"`, blok gelap, kicker "Dashboard pengurus", judul "Angka penting, satu layar.", satu kalimat.
- Panel kanan: tile 6/12 terisi, Rp6,0 jt tagihan, 2 verifikasi; grafik batang CSS 8 batang dengan batang terakhir emas.
- Label wajib "Contoh tampilan · bukan data produksi".
- Tombol "Masuk sebagai pengurus" ke `route('login')`.

`faq.blade.php`:
- Section `id="faq"`, empat `<details>` dengan pertanyaan/jawaban dari spec 7.5. Ikon plus/minus CSS.

`cta.blade.php`:
- Blok gelap "Siap merapikan kos?".
- Tombol emas:
  - Bila `config('kelolakos.kontak_admin')` terisi: "Hubungi admin" + `mailto:`.
  - Selain itu: "Masuk sebagai pengurus" ke `route('login')`.
- Tombol outline "Buat akun penghuni" ke `route('register')`.

`config/kelolakos.php`:

```php
<?php

return [
    'kontak_admin' => env('KELOLAKOS_KONTAK_ADMIN'),
];
```

`site-footer.blade.php`: brand "KelolaKos" + "Kelola kos lebih rapi.", tautan Jaringan/Kamar/Pengurus/FAQ, "© 2026 KelolaKos", dan kontak hanya bila config terisi. Tidak ada nomor telepon contoh.

Sertakan footer di `layouts.public` setelah `<main>`. Tambahkan CSS section gelap, FAQ, CTA, dan footer.

- [x] **Step 4: Run the tests to verify they pass**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php`
Expected: PASS (semua test homepage).

- [x] **Step 5: Commit**

```bash
git add config/kelolakos.php resources/views/public/partials/home/dashboard-preview.blade.php resources/views/public/partials/home/faq.blade.php resources/views/public/partials/home/cta.blade.php resources/views/public/partials/site-footer.blade.php resources/views/layouts/public.blade.php resources/views/public/landing.blade.php resources/css/app.css tests/Feature/Public/HomePageTest.php
git commit -m "feat: complete homepage sections and public footer"
```

---

### Task 10: Responsive, accessibility, and final verification

**Files:**
- Modify: `resources/css/app.css`
- Modify: `resources/views/layouts/public.blade.php`
- Test: `tests/Feature/Public/HomePageTest.php`

**Interfaces:**
- Consumes: semua section.
- Produces: perilaku responsif final, fokus terlihat, reduced motion.

- [x] **Step 1: Write the failing accessibility test**

```php
public function test_homepage_has_accessible_labels_and_no_inline_handlers(): void
{
    $this->buatKos('Kos Melati', 'Yogyakarta', [
        'A5' => ['kosong', 950000],
    ]);

    $this->get('/')
        ->assertOk()
        ->assertSee('aria-label="Buka menu"', false)
        ->assertSee('aria-label="Yogyakarta, 1 kos, 1 kamar kosong"', false)
        ->assertDontSee('onclick=', false)
        ->assertDontSee('onsubmit=', false)
        ->assertDontSee('<script>', false);
}
```

- [x] **Step 2: Run the test to verify it fails or reveals a gap**

Run: `php artisan test --compact tests/Feature/Public/HomePageTest.php --filter=accessible_labels`
Expected: PASS bila label sudah benar; FAIL bila atribut belum lengkap. Perbaiki markup sampai PASS.

- [x] **Step 3: Add responsive and accessibility CSS**

Tambahkan:

- `@media (max-width: 900px)`: nav pill disembunyikan, burger tampil, drawer tampil saat `hidden` dihapus, hero/peta/dashboard satu kolom, kamar 2 kolom.
- `@media (max-width: 620px)`: gutter 18 px, kamar 1 kolom, panel menumpuk, tooltip peta disembunyikan, footer menumpuk.
- `.skip-link` tersembunyi sampai `:focus`.
- `.sr-only` untuk legend dan teks pembaca layar.
- `@media (prefers-reduced-motion: reduce)`: matikan `animation`, `transition`, dan `scroll-behavior`.
- Pastikan target sentuh pin minimal 44 × 44 px meskipun dot visual 10 px.
- `section[id]` scroll margin 72 px.

- [x] **Step 4: Run the full test suite**

Run: `php artisan test --compact`
Expected: seluruh test PASS (baseline 111 test + test baru).

- [x] **Step 5: Run Pint and build**

Run: `vendor/bin/pint --dirty --format agent`
Expected: tidak ada error.
Run: `npm run build`
Expected: build sukses.

- [x] **Step 6: Visual verification at three widths**

Jalankan server lokal, lalu periksa `/` pada 1440, 768, dan 375 px. Expected:

- Nav tidak tumpang tindih; "Daftar" terbaca.
- Hero tidak memotong judul; panel ketersediaan tidak menabrak visual.
- Peta menampilkan titik emas; klik/panah keyboard mengganti kartu; chip aktif.
- Kartu kamar tidak terpotong; harga dan status terbaca.
- Tidak ada scroll horizontal.
- Footer tidak memuat kontak palsu.

- [x] **Step 7: Commit**

```bash
git add resources/css/app.css resources/views/layouts/public.blade.php tests/Feature/Public/HomePageTest.php
git commit -m "fix: polish homepage responsiveness and accessibility"
```

---

## Final Acceptance Checklist

- [x] `php artisan test --compact` lulus.
- [x] `vendor/bin/pint --dirty --format agent` bersih.
- [x] `npm run build` sukses.
- [x] Homepage memakai data `kos`/`kamar` aktif, bukan angka hardcode.
- [x] Pengurus yang login tetap melihat agregat seluruh jaringan.
- [x] Kota tanpa koordinat tidak menghasilkan pin, tetapi namanya tetap tampil.
- [x] Tidak ada kamar kosong / kos aktif: empty state muncul.
- [x] Kamar tanpa foto memakai placeholder, bukan gambar rusak.
- [x] Peta bisa diganti dengan klik dan keyboard; `prefers-reduced-motion` dihormati.
- [x] Tidak ada inline script, `onclick`, atau `onsubmit`.
- [x] Tidak ada testimoni, rating, atau nomor telepon karangan.
