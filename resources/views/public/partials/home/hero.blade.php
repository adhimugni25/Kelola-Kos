@php use App\Support\Rupiah; @endphp

<section class="hero-section" id="beranda">
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-kicker">Kos & penghuni · {{ $kotaUtama ?? 'Indonesia' }}</div>
            <h1 class="hero-title">
                Cari kamar.<br>
                Kelola kos.<br>
                <em>Satu tempat.</em>
            </h1>
            <p class="hero-lead">Kamar kosong, tagihan, dan bukti bayar dalam satu alur.</p>

            <div class="availability-panel">
                <div class="avail-figure">
                    <span class="avail-number">{{ $jumlahKosong }}</span>
                    <span class="avail-label">kamar kosong</span>
                </div>
                <div class="avail-meta">
                    <span>{{ $kotaUtama ?? 'Indonesia' }}</span>
                    <span>{{ Rupiah::ringkas($hargaMin) }} – {{ Rupiah::ringkas($hargaMax) }}</span>
                </div>
                <div class="avail-actions">
                    <a href="#kamar" class="btn-gold">Lihat kamar</a>
                    <a href="#pengurus" class="link-secondary">Untuk pengurus →</a>
                </div>
            </div>

            <div class="hero-stats">
                <div class="stat-item">
                    <span class="stat-num">{{ $jumlahKota }}</span>
                    <span class="stat-lbl">kota</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num">{{ $jumlahKos }}</span>
                    <span class="stat-lbl">kos</span>
                </div>
                <div class="stat-item">
                    <span class="stat-num">{{ $jumlahKamar }}</span>
                    <span class="stat-lbl">kamar</span>
                </div>
            </div>
        </div>

        <div class="hero-visual">
            @if ($kamarKosong->isNotEmpty())
                @php $featured = $kamarKosong->first(); @endphp
                <div class="featured-room">
                    @if ($featured->foto_path)
                        <img src="{{ Storage::disk('public')->url($featured->foto_path) }}" alt="Kamar {{ $featured->nomor }}" class="room-photo">
                    @else
                        <div class="room-placeholder">Foto kamar belum tersedia</div>
                    @endif
                    <div class="room-info">
                        <div class="room-header">
                            <span class="room-number">{{ $featured->nomor }}</span>
                            <span class="room-type">{{ $featured->tipe }}</span>
                        </div>
                        <div class="room-kos">{{ $featured->kos->nama }}</div>
                        <div class="room-price" aria-label="{{ Rupiah::penuh($featured->harga_bulanan) }} per bulan">
                            {{ Rupiah::ringkas($featured->harga_bulanan) }}<span>/bulan</span>
                        </div>
                    </div>
                </div>
            @else
                <div class="empty-featured">
                    <p>Kamar kosong sedang tidak tersedia</p>
                </div>
            @endif
        </div>
    </div>
</section>
