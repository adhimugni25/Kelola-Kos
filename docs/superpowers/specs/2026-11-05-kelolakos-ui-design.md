# KelolaKos — Desain UI/UX (revisi)

Tanggal: 5 November 2026
Status: draf untuk disetujui
Konteks: penyegaran tampilan sebelum login, lalu penerapan sistem yang sama ke halaman aplikasi
Referensi produk: `docs/superpowers/specs/2026-11-04-kelolakos-design.md`

---

## 1. Ringkasan

Dokumen ini mengikat arah visual dan interaksi baru KelolaKos. Fokus tahap pertama adalah
halaman publik `/` yang harus melayani dua audiens sekaligus: calon penghuni yang mencari
kamar, dan pengurus yang ingin melihat nilai aplikasi. Tahap kedua memakai sistem yang sama
untuk dashboard, tabel, form, dan empty state.

Perubahan utama dari tampilan sebelumnya:

1. Palet monokrom hitam-putih dengan gradien dan transparansi berlapis; emas hanya sebagai
   aksen, bukan warna dominan.
2. Teks dipangkas. Satu paragraf pendukung maksimal satu kalimat.
3. Peta Indonesia stilasi dengan titik emas untuk kota yang punya kos aktif. Peta dapat
   diklik dan difokuskan dengan keyboard.
4. Angka dan kartu di halaman publik diambil dari database bila memungkinkan. Data contoh
   selalu diberi label.
5. Navigasi responsif dengan menu tengah pada desktop dan drawer pada mobile.

Yang tidak termasuk tahap pertama:

- Direktori atau marketplace lintas kos dengan pencarian dan filter.
- Testimoni, rating, logo mitra, atau angka pertumbuhan yang tidak punya sumber.
- Payment gateway, notifikasi email/WhatsApp, dan fitur bisnis baru.
- Foto kamar palsu. Bila foto belum ada, tampilkan placeholder netral.

---

## 2. Prinsip desain

1. **Monokrom dulu, emas sebagai sinyal.** Emas menandai hal yang perlu diperhatikan:
   titik peta, harga, status tersedia, dan aksi utama. Emas tidak dipakai untuk latar besar.
2. **Sedikit teks, banyak isyarat.** Setiap bagian punya satu pesan. Detail dipindahkan ke
   halaman kos atau dashboard.
3. **Jujur pada data.** Jumlah kota, kos, kamar, dan harga dihitung dari database. Pratinjau
   dashboard diberi label "Contoh tampilan". Tidak ada testimoni karangan.
4. **Satu sistem, dua audiens.** Calon penghuni melihat kamar dan harga; pengurus melihat
   alur kerja dan dashboard. Keduanya berbagi token, tipografi, dan komponen yang sama.
5. **Aman dan aksesibel.** Tidak ada inline script atau event handler. Interaksi peta memakai
   HTML dan CSS, dengan enhancement JavaScript yang di-bundle. Target sentuh minimal 44 px.

---

## 3. Token desain

### 3.1 Primitif

| Token | Nilai | Pemakaian |
|---|---|---|
| `--ink` | `#0C0A09` | Teks utama, latar tombol gelap |
| `--ink-2` | `#1C1917` | Teks sekunder di latar terang |
| `--ink-3` | `#292524` | Gradien gelap, permukaan dashboard |
| `--paper` | `#FFFFFF` | Permukaan utama |
| `--paper-2` | `#FAFAF9` | Permukaan hangat, latar halaman |
| `--stone-1` | `#F5F5F4` | Latar tag, baris tabel |
| `--stone-2` | `#E7E5E4` | Border halus, track progress |
| `--stone-3` | `#D6D3D1` | Border lebih tegas, status perbaikan |
| `--stone-4` | `#A8A29E` | Ikon nonaktif, teks paling redup |
| `--muted` | `#71717A` | Teks pendukung |
| `--muted-2` | `#57534E` | Teks pendukung di permukaan hangat |
| `--gold` | `#A16207` | Aksen utama, teks emas di latar terang |
| `--gold-ink` | `#8A5306` | Teks emas yang perlu kontras lebih tinggi |
| `--gold-2` | `#D4AF37` | Aksen dekoratif di latar gelap saja |
| `--gold-soft` | `#F7EBD1` | Latar pill harga/status |
| `--gold-line` | `rgba(161, 98, 7, .26)` | Border pill emas |

Transparansi memakai tangga tetap: **6%, 12%, 28%, 45%, 72%, 100%**. Tidak ada nilai
transparansi acak di komponen.

| Token | Nilai |
|---|---|
| `--glass-06` | `rgba(255,255,255,.06)` |
| `--glass-12` | `rgba(255,255,255,.12)` |
| `--glass-28` | `rgba(255,255,255,.28)` |
| `--glass-45` | `rgba(255,255,255,.45)` |
| `--glass-72` | `rgba(255,255,255,.72)` |
| `--glass-100` | `#FFFFFF` |
| `--line-d` | `rgba(12,10,9,.09)` |
| `--line-l` | `rgba(255,255,255,.16)` |

### 3.2 Semantik

| Token semantik | Mengacu ke | Dipakai untuk |
|---|---|---|
| `--surface-page` | gradien `paper-2` ke `stone-2` | Latar halaman publik |
| `--surface-card` | `paper` / `paper-2` | Kartu kamar, FAQ, panel |
| `--surface-inverse` | `ink-3` ke `ink` | Dashboard preview, CTA |
| `--text-primary` | `ink` | Judul dan isi utama |
| `--text-secondary` | `muted-2` | Keterangan |
| `--text-muted` | `muted` | Label kecil |
| `--text-on-dark` | `paper` | Teks di permukaan gelap |
| `--text-on-dark-muted` | `#D4D4D8` | Paragraf di permukaan gelap |
| `--border-subtle` | `line-d` | Pemisah di latar terang |
| `--border-on-dark` | `line-l` | Pemisah di latar gelap |
| `--accent` | `gold` | Titik peta, harga, aksi utama |
| `--accent-strong` | `gold-ink` | Teks emas di latar terang |
| `--accent-soft` | `gold-soft` | Latar pill |
| `--focus-ring` | `gold` | Outline fokus 2 px |

### 3.3 Komponen

| Token komponen | Nilai |
|---|---|
| `--nav-bg` | `glass-72` + `backdrop-filter: blur(18px)` |
| `--nav-pill-active-bg` | `ink` |
| `--card-bg` | `linear-gradient(180deg, #FFFFFF, #FAFAF9)` |
| `--card-border` | `rgba(12,10,9,.09)` |
| `--card-shadow` | `0 10px 24px rgba(12,10,9,.06)` |
| `--pin-fill` | `linear-gradient(145deg, #E9CE7E, #A16207 80%)` |
| `--button-primary-bg` | `linear-gradient(145deg, #27272A, #09090B)` |
| `--button-gold-bg` | `linear-gradient(145deg, #E3C368, #A16207 78%)` |
| `--button-gold-text` | `#1A1206` |
| `--radius-xl` | `24px` |
| `--radius-lg` | `16px` |
| `--radius-md` | `12px` |

### 3.4 Tailwind 4

Token didaftarkan di `resources/css/app.css` lewat `@theme`, lalu dipakai sebagai utility
(`bg-ink`, `text-muted`, `border-line`, `rounded-xl`). Nilai mentah tidak ditulis ulang di
Blade. Pengecualian hanya koordinat peta dan lebar bar yang memang dinamis.

---

## 4. Tipografi

| Peran | Font | Ukuran | Catatan |
|---|---|---|---|
| Display | Georgia, `Times New Roman`, serif | `clamp(38px, 5vw, 56px)` | Hanya H1 hero |
| Judul bagian | Georgia | 26–32 px | Letter-spacing `-.8px` |
| Judul kartu | Inter/system | 14–16 px, 650 | Tidak memakai serif |
| Body | Inter, system-ui, Segoe UI | 14 px / 1.6 | Minimum 14 px |
| Small | Inter | 12 px / 1.5 | Keterangan, lokasi |
| Micro | ui-monospace | 11 px, uppercase, tracking `.16em` | Kicker, label kolom |
| Angka | Georgia | 19–24 px | Statistik dan harga |

Aturan:

- Tidak memakai Google Fonts karena CSP `font-src 'self' data:`. Inter boleh di-self-host
  sebagai woff2; selama belum ada, fallback system sudah cukup.
- Teks lebih kecil dari 12 px hanya boleh untuk label uppercase pendek, bukan isi.
- Harga ringkas di kartu memakai `Rp1,05 jt`; harga lengkap `Rp1.050.000` muncul di halaman
  kos dan `aria-label`.
- Angka memakai format Indonesia: titik ribuan, koma desimal.

---

## 5. Layout dan breakpoint

| Rentang | Perilaku |
|---|---|
| ≥ 1200 px | Container 1080 px, hero 2 kolom, peta 2 kolom, kamar 3 kolom |
| 900–1199 px | Container penuh dengan gutter 22 px, kamar 3 kolom, peta tetap 2 kolom |
| 620–899 px | Nav pill disembunyikan, drawer aktif, hero 1 kolom, peta 1 kolom, kamar 2 kolom |
| < 620 px | Gutter 18 px, semua kartu 1 kolom, pencarian/panel menumpuk, tooltip peta diganti label |

Aturan layout:

- Grid hero: `1.06fr .94fr`; peta: `1.52fr .88fr`; dashboard: `1fr 1.12fr`.
- Jarak antar bagian 30 px desktop, 26 px mobile. Tidak ada bagian dengan padding vertikal
  lebih dari 40 px agar halaman tidak terasa kosong.
- Sudut: kartu besar 16 px, panel 24 px, pill 999 px.
- Bayangan hanya di permukaan yang memang mengambang. Jangan memberi bayangan ke semua kartu.
- Tidak boleh ada horizontal scroll pada 375 px.

---

## 6. Navigasi

### Desktop

- Grid tiga kolom: brand kiri, pill navigasi di tengah, aksi di kanan.
- Item: Beranda, Jaringan, Kamar, Pengurus, FAQ. Item aktif memakai latar `ink` dan teks putih.
- Aksi: "Masuk" (teks) dan "Daftar" (pill gelap). "Daftar" mengarah ke registrasi penghuni.
- Header sticky dengan latar `glass-72`, border bawah `line-d`, dan blur. Tinggi 56–64 px.
- Fokus keyboard terlihat dengan outline emas 2 px.

### Mobile (< 900 px)

- Brand kiri, "Daftar" dan tombol burger di kanan.
- Drawer muncul di bawah header, berisi lima item nav, "Masuk", dan catatan kecil
  "Akun pengurus dibuat oleh admin."
- Perilaku drawer ada di `resources/js/app.js`: `aria-expanded`, `aria-controls`, tutup
  dengan Escape, klik di luar, dan klik item. Fokus kembali ke tombol setelah ditutup.
- Drawer tidak memakai animasi lebih dari 180 ms dan menghormati `prefers-reduced-motion`.

---

## 7. Anatomi halaman publik

| Bagian | Pesan utama | Sumber data |
|---|---|---|
| Hero | Cari kamar atau kelola kos, satu tempat | Agregat `kos` dan `kamar` |
| Jaringan | Kos sudah tersebar di beberapa kota | `kos` aktif per kota |
| Kamar | Kamar kosong yang bisa diajukan sekarang | `kamar` status `kosong` |
| Dashboard | Gambaran kerja pengurus | Ilustrasi statis, label "Contoh" |
| FAQ | Empat pertanyaan yang paling sering muncul | Konten statis |
| CTA | Mulai sebagai penghuni atau pengurus | Statis |
| Footer | Identitas, tautan, kontak | Statis |

### 7.1 Hero

Struktur:

1. Kicker monospace: "Kos & penghuni · {kota}" bila hanya satu kota, atau
   "Kos & penghuni · Indonesia" bila lebih dari satu.
2. H1 tiga baris: "Cari kamar. / Kelola kos. / Satu tempat." Kata "Satu tempat." memakai
   italic dan gradien emas.
3. Lead satu baris: "Kamar kosong, tagihan, dan bukti bayar dalam satu alur."
4. **Panel ketersediaan**, bukan form pencarian. Isi: jumlah kamar kosong, kota, rentang
   harga, tombol emas "Lihat kamar" (anchor ke `#kamar`), dan tautan "Untuk pengurus"
   (anchor ke `#pengurus`).
5. Statistik dinamis: jumlah kota, kos, dan kamar. Angka mengikuti database.
6. Visual kanan: kartu kamar nyata yang sedang kosong, plus kartu kecil status ketersediaan.
   Bila kamar belum punya foto, gunakan placeholder netral bertuliskan "Foto kamar belum
   tersedia", bukan ilustrasi yang terlihat seperti foto asli.

Keputusan penting: mockup menampilkan kolom "Kota" dan "Harga maks" seperti form pencarian.
Kolom itu **tidak diimplementasikan sebagai form** karena direktori lintas kos belum ada.
Panel ketersediaan menggantikannya agar tidak ada kontrol yang terlihat bisa dipakai tetapi
tidak melakukan apa-apa. Bila direktori dibuat di tahap berikutnya, panel ini dapat berubah
menjadi form tanpa mengubah tata letak.

### 7.2 Jaringan kos (peta)

- Judul dinamis: "Sudah bergabung di {n} kota".
- Kicker: "Jaringan kos". Petunjuk kanan: "Klik titik emas".
- Peta stilasi Indonesia, rasio `760 × 340`, latar kertas dengan grid halus dan mask radial.
- Setiap kota dengan minimal satu kos aktif mendapat satu titik emas.
- Hover/fokus menampilkan tooltip: nama kota dan jumlah kos.
- Klik, Enter, atau panah keyboard mengganti kartu informasi di kanan.
- Kartu informasi berisi: badge "Aktif", jumlah kos, nama kota, daftar maksimal tiga kos
  (masing-masing tautan ke `/k/{slug}`), jumlah kamar/kosong/penghuni, rentang harga, dan
  tautan "Lihat semua kamar" ke kos pertama.
- Di bawah kartu ada chip kota sebagai kontrol alternatif dan untuk pengguna layar sentuh.
- Catatan kaki: "Peta stilasi · data dari kos aktif." Bila data yang tampil adalah data
  contoh, tulis "Peta stilasi · data contoh."

Keadaan khusus:

- **0 kos aktif:** peta diganti empty state "Jaringan sedang tumbuh" dan CTA untuk pengurus.
- **1 kota:** peta tetap tampil dengan satu titik dan catatan "Kota berikutnya segera
  bergabung." Tidak menampilkan titik palsu.
- **Kota tanpa koordinat:** tidak digambar di peta; namanya muncul di daftar teks di bawah
  peta agar tetap bisa diakses.

### 7.3 Kamar kosong

- Judul: "Kamar kosong" dengan jumlah aktual, mis. "Empat kamar kosong".
- Bar ketersediaan: terisi / perbaikan / kosong, dengan legenda teks. Angka dihitung dari
  seluruh kos aktif.
- Tiga kartu kamar pertama, diurutkan dari harga terendah. Setiap kartu berisi: nama kos,
  nomor dan tipe kamar, kota/lokasi, harga per bulan, maksimal tiga fasilitas, status
  "Tersedia", dan tautan "Ajukan" ke halaman kos.
- Foto memakai `foto_path` bila ada; jika tidak, placeholder netral.
- Bila tidak ada kamar kosong: satu empty state dengan tautan ke semua kos aktif.

### 7.4 Pratinjau dashboard pengurus

- Blok gelap dengan aksen emas halus di sudut kanan atas.
- Kiri: kicker "Dashboard pengurus", judul "Angka penting, satu layar.", satu kalimat
  penjelasan, dan tiga tag: "Impor CSV", "Jejak audit", "Laporan PDF".
- Kanan: panel kaca berisi tiga tile (terisi, tagihan bulan ini, verifikasi), grafik batang
  delapan bulan, dan label "Contoh tampilan · bukan data produksi".
- Angka di panel ini **statis** dan tidak membaca tagihan asli. Halaman publik tidak boleh
  membocorkan total pendapatan atau jumlah tagihan pengurus.
- Tombol "Masuk sebagai pengurus" mengarah ke login.

### 7.5 FAQ

Empat item, masing-masing satu pertanyaan dan maksimal satu kalimat jawaban:

1. "Perlu akun untuk melihat kamar?" — "Tidak. Akun hanya diperlukan saat mengajukan sewa."
2. "Bagaimana cara mengajukan sewa?" — "Pilih kamar, isi tanggal mulai dan durasi, lalu
   kirim ke pengurus."
3. "Format bukti bayar apa yang diterima?" — "JPG, PNG, atau PDF maksimal 2 MB, dengan
   jumlah sesuai tagihan."
4. "Laporan bisa diunduh?" — "Bisa. Rekap pendapatan tersedia dalam CSV dan PDF."

Memakai elemen `<details>` native. Ikon plus/minus hanya dekorasi CSS.

### 7.6 CTA dan footer

- CTA gelap: "Siap merapikan kos?" dengan kalimat "Masuk, tambah kamar, tagihan jalan
  sendiri." Dua tombol: emas "Masuk sebagai pengurus" dan outline "Buat akun penghuni".
- Tidak memakai "Daftar sebagai pengurus" karena pendaftaran pengurus tidak mandiri.
  Bila calon pengurus belum punya akun, arahkan ke kontak admin yang dapat dikonfigurasi.
- Footer satu baris di desktop: brand dan tagline, tautan bagian, kota/kontak. Baris kedua:
  "© 2026 KelolaKos" dan catatan data demo bila berlaku.
- Di mobile, footer menumpuk dan tautan tetap minimal 44 px tingginya.

---

## 8. Spesifikasi teknis peta

### 8.1 Markup dan interaksi

```html
<fieldset class="map-panel">
  <legend class="sr-only">Pilih kota untuk melihat kos</legend>

  <div class="map-canvas">
    <svg class="id-map" viewBox="0 0 760 340" aria-hidden="true">...</svg>

    <div class="pin-slot" style="left:42.8%;top:79.4%">
      <input class="pin-input" type="radio" name="kos-map" id="p-jogja" checked>
      <label class="pin" for="p-jogja">
        <span class="dot"></span>
        <span class="ring"></span>
        <span class="tip">Yogyakarta · <b>2 kos</b></span>
      </label>
    </div>
  </div>

  <aside class="map-side">
    <article class="map-info" data-kota="jogja">...</article>
    <div class="map-cities">...</div>
  </aside>
</fieldset>
```

- Input radio disembunyikan secara visual tetapi tetap fokusabel.
- `label` menjadi titik yang dapat diklik dan menjadi bagian dari urutan Tab.
- CSS `:has()` menampilkan panel informasi yang sesuai dan menyembunyikan sisanya.
- Baseline tanpa JavaScript tetap berfungsi di browser modern.
- Bila `:has()` tidak didukung, `app.js` menambahkan kelas `is-active` pada panel yang
  dipilih. Tidak ada konten yang bergantung pada JavaScript untuk tampil pertama kali.
- Tooltip tidak boleh menjadi satu-satunya sumber informasi; teks kota juga ada di kartu
  informasi dan `aria-label` label.

### 8.2 Koordinat

Koordinat disimpan di `app/Support/KotaPeta.php` sebagai persentase terhadap kanvas
`760 × 340`. Peta adalah stilasi, bukan proyeksi geografis.

| Kota | x | y |
|---|---|---|
| Banda Aceh | 10.0 | 22.0 |
| Medan | 14.5 | 27.9 |
| Padang | 20.0 | 60.0 |
| Palembang | 28.0 | 63.0 |
| Jakarta | 32.2 | 77.1 |
| Bandung | 36.0 | 82.0 |
| Yogyakarta | 42.8 | 79.4 |
| Semarang | 45.0 | 80.0 |
| Surabaya | 51.3 | 78.2 |
| Malang | 49.0 | 81.5 |
| Denpasar | 54.5 | 82.9 |
| Pontianak | 38.0 | 58.0 |
| Banjarmasin | 48.0 | 70.0 |
| Balikpapan | 55.0 | 66.0 |
| Makassar | 62.9 | 65.9 |
| Manado | 65.0 | 48.0 |
| Jayapura | 88.0 | 55.0 |

Kota yang tidak ada di tabel tidak dipetakan. Nama kota dicocokkan case-insensitive dan
di-trim. Daftar ini boleh ditambah tanpa mengubah markup.

### 8.3 Data

`HomeController` mengirim koleksi `$petaKota` dengan bentuk:

```php
[
    'kota' => 'Yogyakarta',
    'x' => 42.8,
    'y' => 79.4,
    'jumlah_kos' => 2,
    'jumlah_kamar' => 12,
    'jumlah_kosong' => 4,
    'jumlah_penghuni' => 6,
    'harga_min' => 950000,
    'harga_max' => 1200000,
    'kos' => [
        ['nama' => 'Kos Melati', 'slug' => 'kos-melati'],
        ['nama' => 'Kos Anggrek', 'slug' => 'kos-anggrek'],
    ],
]
```

Query memakai `withCount` dan agregat, bukan loop query di Blade:

- `kos` aktif.
- `kamar` per kos dihitung berdasarkan status.
- `penghuni` dihitung dari pemesanan aktif.
- Harga minimum dan maksimum dari kamar kosong.

Hasil di-cache 5 menit dengan key `home.jaringan`; cache dibersihkan saat kos atau kamar
berubah bila event tersedia, atau cukup TTL untuk MVP.

### 8.4 Aksesibilitas peta

- Setiap label punya `aria-label` lengkap: "Yogyakarta, 2 kos, 4 kamar kosong".
- Radio dapat diganti dengan tombol panah saat fokus berada di grup.
- Outline fokus 2 px emas, offset 2 px.
- Target sentuh minimal 44 × 44 px; titik visual boleh tetap 10–12 px.
- Informasi kota tidak boleh hanya dibedakan lewat warna.
- `prefers-reduced-motion`: animasi ping dimatikan, transisi titik menjadi instan.
- Panel informasi diberi `aria-live="polite"` agar perubahan kota diumumkan.

### 8.5 Responsif

- ≥ 900 px: peta dan kartu informasi berdampingan.
- < 900 px: kartu informasi turun ke bawah peta; tinggi peta mengikuti rasio.
- < 620 px: tooltip hover disembunyikan (tidak ada hover), chip kota tetap terlihat, dan
  peta tidak lebih tinggi dari 240 px.
- Jangan memotong peta atau membuat scroll horizontal.

---

## 9. Komponen halaman aplikasi (tahap kedua)

Sistem ini dipakai ulang di dashboard, tabel, dan form. Spesifikasi minimum:

### Tombol

| Varian | Default | Hover | Aktif | Nonaktif |
|---|---|---|---|---|
| Primary | latar `ink`, teks putih | `ink-3` | `ink`, tanpa bayangan | `stone-2`, teks `stone-4` |
| Gold | gradien emas, teks `#1A1206` | emas lebih terang | emas gelap | `gold-soft`, teks `gold-ink` 50% |
| Outline | transparan, border `stone-3` | `stone-1` | `stone-2` | border `stone-2`, teks `stone-4` |
| Ghost | transparan | `stone-1` | `stone-2` | teks `stone-4` |
| Danger | `#B91C1C` | `#991B1B` | `#7F1D1D` | `#FCA5A5` |

Semua tombol minimal 44 px tinggi, radius 999 px untuk aksi utama dan 12 px untuk aksi
dalam tabel.

### Form

- Label di atas input, wajib terlihat; placeholder bukan pengganti label.
- Input tinggi 44 px, border `stone-2`, fokus outline emas 2 px.
- Pesan error di bawah input, terhubung dengan `aria-describedby`.
- Kelompokkan field dengan `fieldset` dan `legend` bila relevan.
- Tombol simpan di kanan bawah, batal sebagai ghost.

### Tabel

- Bungkus dengan `overflow-x-auto`; jangan memaksa tabel muat di mobile.
- Header sticky, latar `paper-2`, teks micro uppercase.
- Baris hover `stone-1`, border bawah `line-d`.
- Kolom angka rata kanan, tanggal `d/m/Y`.
- Status memakai badge dengan teks, bukan hanya warna.
- Aksi baris berupa tombol ikon dengan `aria-label` dan minimal 44 px.

### Badge status

| Status | Gaya |
|---|---|
| `kosong` / tersedia | latar `gold-soft`, border `gold-line`, teks `gold-ink` |
| `terisi` | latar `ink`, teks putih |
| `perbaikan` | latar `stone-1`, border `stone-3`, teks `muted-2` |
| `lunas` | latar `ink`, teks putih |
| `belum_bayar` | latar `paper`, border `stone-3`, teks `ink` |
| `menunggu_verifikasi` | latar `gold-soft`, teks `gold-ink` |
| `ditolak` / `dibatalkan` | latar `stone-1`, teks `muted`, garis coret opsional |

### Empty state

- Ikon garis monokrom 32 px.
- Judul satu kalimat, penjelasan satu kalimat, satu aksi utama.
- Jangan memakai ilustrasi besar yang memakan ruang.

### Grafik

- Chart.js di-bundle lewat Vite, bukan CDN.
- Batang monokrom; batang terakhir atau batang terpilih memakai emas.
- Sumbu dan grid memakai `stone-2`; label 11 px.
- Sediakan ringkasan teks di bawah grafik untuk pembaca layar.

---

## 10. Gerak

- Durasi 150–200 ms, easing `ease-out`.
- Hover kartu: naik 2–3 px, bayangan bertambah halus.
- Titik peta: skala 1.18 saat hover, ring ping 1.9 detik saat dipilih.
- Tidak ada animasi masuk otomatis yang menunda konten.
- `prefers-reduced-motion: reduce`: matikan ping, transisi, dan transform.

---

## 11. Aksesibilitas

- Kontras teks utama minimal 4.5:1. `muted` di atas putih sekitar 4.8:1; `gold-2` hanya
  untuk dekorasi di latar gelap, bukan teks kecil di latar terang.
- Fokus selalu terlihat; jangan menghapus outline tanpa pengganti.
- Skip link "Lewati ke konten" di awal halaman.
- Satu `h1` per halaman; heading tidak melompati level.
- Gunakan `header`, `nav`, `main`, `section`, `footer` dengan label yang jelas.
- Gambar dekoratif `alt=""`; foto kamar punya alt deskriptif.
- Form error diumumkan, bukan hanya diberi warna merah.
- Peta dapat digunakan tanpa mouse dan tanpa JavaScript.

---

## 12. Aturan konten

- Bahasa Indonesia, kalimat pendek, sapaan netral.
- Gunakan "kamar kosong", bukan "unit tersedia"; "penghuni", bukan "tenant".
- Harga ringkas: `Rp950 rb`, `Rp1,05 jt`. Harga lengkap di halaman detail.
- Tanggal: `5 November 2026` di teks, `05/11/2026` di tabel.
- Jangan menulis "terbaik", "nomor 1", atau angka pertumbuhan tanpa sumber.
- Jangan menampilkan testimoni, logo universitas, atau rating bintang.
- Data contoh harus diberi label "Contoh tampilan" atau "Data demo" di dekat angka.
- Nama kos dan harga di homepage harus berasal dari database, bukan hardcode.

---

## 13. Pemetaan implementasi

| Berkas | Perubahan |
|---|---|
| `resources/css/app.css` | Token `@theme`, utility kaca, komponen nav/kartu/pill |
| `resources/views/layouts/app.blade.php` | Nav sticky, pill tengah, drawer mobile, skip link |
| `resources/views/layouts/guest.blade.php` | Latar dan kartu auth mengikuti token |
| `resources/views/public/landing.blade.php` | Susunan ulang bagian homepage |
| `resources/views/public/partials/home/*` | Hero, peta, kamar, dashboard, FAQ, CTA |
| `app/Http/Controllers/Public/HomeController.php` | Query agregat dan data peta |
| `app/Support/KotaPeta.php` | Tabel koordinat dan fallback |
| `resources/js/app.js` | Drawer nav, fallback `:has`, fokus |
| `app/Http/Middleware/SecurityHeaders.php` | Tidak berubah; CSP sudah mengizinkan style inline |
| `resources/views/pengurus/dashboard.blade.php` | Tahap kedua: kartu, tabel, grafik |
| `resources/views/penghuni/dashboard.blade.php` | Tahap kedua: isi dashboard yang belum ada |
| `resources/views/pengurus/kamar/index.blade.php` | Tahap kedua: tabel responsif |

Interaksi peta tidak memakai inline script. Koordinat boleh memakai atribut `style`
karena CSP saat ini mengizinkan `style-src 'unsafe-inline'`; tetap utamakan kelas bila
nilainya statis.

---

## 14. Kriteria penerimaan

1. Homepage tampil tanpa error pada lebar 375, 768, 1024, dan 1440 px; tidak ada scroll
   horizontal.
2. Tidak ada teks yang tumpang tindih atau terpotong di nav, hero, peta, kartu kamar,
   dashboard, FAQ, CTA, dan footer.
3. Jumlah kota, kos, kamar, dan harga di homepage sesuai data database.
4. Peta menampilkan satu titik per kota yang punya kos aktif; klik, Enter, dan panah keyboard
   mengganti kartu informasi.
5. Peta tetap menampilkan informasi default saat JavaScript dimatikan.
6. Tidak ada pelanggaran CSP di konsol browser; tidak ada inline script atau `onclick`.
7. Semua kontrol dapat dijangkau keyboard; fokus terlihat; target sentuh minimal 44 px.
8. `php artisan test --compact` lulus; test baru mencakup agregat homepage, keadaan tanpa
   kos, dan kota tanpa koordinat.
9. `vendor/bin/pint --dirty --format agent` bersih.
10. `npm run build` berhasil dan aset homepage memakai hasil build, bukan CDN.

---

## 15. Tahapan

**Tahap 1 — homepage dan kerangka bersama**

- Token, tipografi, nav, footer.
- Hero, peta, kamar, pratinjau dashboard, FAQ, CTA.
- Data agregat dan `KotaPeta`.
- Test homepage dan aksesibilitas dasar.

**Tahap 2 — halaman aplikasi**

- Dashboard pengurus dan penghuni.
- Tabel kamar, tagihan, pembayaran, pengajuan.
- Form, badge, empty state, alert.
- Penyelarasan halaman login/register.

**Tahap 3 — penyempurnaan**

- Audit kontras dan keyboard.
- Uji 375 px pada perangkat nyata.
- Screenshot untuk laporan.
- Dokumentasi token dan komponen.

---

## 16. Keputusan yang perlu dikonfirmasi

1. **Peta data contoh.** Seeder utama hanya berisi Yogyakarta, sehingga homepage lokal akan
   menampilkan satu titik. Untuk screenshot enam kota, tambahkan seeder terpisah
   `DemoJaringanSeeder` yang hanya dijalankan manual di lokal, bukan bagian dari seed default.
2. **Kontak calon pengurus.** Karena akun pengurus dibuat admin, CTA "Hubungi admin" perlu
   alamat email atau nomor WhatsApp. Bila belum ada, CTA diarahkan ke halaman login dengan
   catatan "Akun pengurus dibuat oleh admin."
3. **Panel ketersediaan menggantikan form pencarian.** Ini menjaga agar tidak ada kontrol
   yang tidak berfungsi. Bila direktori lintas kos ingin dibuat, itu keputusan scope baru.
