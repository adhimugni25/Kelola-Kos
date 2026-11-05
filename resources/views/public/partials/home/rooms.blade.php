@php use App\Support\Rupiah; @endphp

<section class="section-shell" id="kamar">
    <div class="section-head">
        <div>
            <div class="kicker">{{ $jumlahKota === 1 ? "Kamar di $kotaUtama" : "Kamar tersedia" }}</div>
            <h2>{{ $jumlahKosong > 0 ? "$jumlahKosong kamar kosong" : "Kamar kosong" }}</h2>
        </div>
    </div>

    <div class="avail-bar-wrap">
        @php
            $total = $ringkasanKamar['terisi'] + $ringkasanKamar['perbaikan'] + $ringkasanKamar['kosong'];
            $pctTerisi = $total > 0 ? ($ringkasanKamar['terisi'] / $total) * 100 : 0;
            $pctPerbaikan = $total > 0 ? ($ringkasanKamar['perbaikan'] / $total) * 100 : 0;
            $pctKosong = $total > 0 ? ($ringkasanKamar['kosong'] / $total) * 100 : 0;
        @endphp
        <div class="avail-bar">
            @if ($total > 0)
                <span style="width: {{ $pctTerisi }}%" class="bar-terisi"></span>
                <span style="width: {{ $pctPerbaikan }}%" class="bar-perbaikan"></span>
                <span style="width: {{ $pctKosong }}%" class="bar-kosong"></span>
            @endif
        </div>
        <div class="avail-legend">
            <span><i class="dot-terisi"></i>{{ $ringkasanKamar['terisi'] }} terisi</span>
            <span><i class="dot-perbaikan"></i>{{ $ringkasanKamar['perbaikan'] }} perbaikan</span>
            <span><i class="dot-kosong"></i>{{ $ringkasanKamar['kosong'] }} kosong</span>
        </div>
    </div>

    <div class="rooms-grid">
        @forelse ($kamarKosong as $kamar)
            <article class="room-card">
                @if ($kamar->foto_path)
                    <img src="{{ Storage::disk('public')->url($kamar->foto_path) }}" alt="Kamar {{ $kamar->nomor }}" class="room-img">
                @else
                    <div class="room-img-placeholder">Foto kamar belum tersedia</div>
                @endif
                <div class="room-card-body">
                    <div class="room-badge">Tersedia</div>
                    <h3 class="room-title">{{ $kamar->nomor }} · {{ $kamar->tipe }}</h3>
                    <p class="room-kos">{{ $kamar->kos->nama }}</p>
                    <p class="room-location">{{ $kamar->kos->kota }}</p>
                    @if ($kamar->fasilitas)
                        <div class="room-tags">
                            @foreach (array_slice(explode(',', $kamar->fasilitas), 0, 3) as $tag)
                                <span>{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="room-footer">
                        <div class="room-price">{{ Rupiah::penuh($kamar->harga_bulanan) }}<span>/bulan</span></div>
                        <a href="{{ route('public.kos', $kamar->kos->slug) }}" class="room-link">Ajukan →</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rooms-empty">
                <h3>Belum ada kamar kosong</h3>
                <p>Kamar sedang tidak tersedia. <a href="#jaringan">Lihat jaringan kos</a></p>
            </div>
        @endforelse
    </div>
</section>
