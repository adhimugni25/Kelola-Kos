@php use App\Support\Rupiah; @endphp

<section class="section-shell" id="jaringan">
    <div class="section-head">
        <div>
            <div class="kicker">Jaringan kos</div>
            <h2>Sudah bergabung di {{ $jumlahKota }} kota</h2>
        </div>
        <span class="head-hint">Klik titik emas · kartu bergeser</span>
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
        <fieldset class="map-panel" data-peta>
            <legend class="sr-only">Peta jaringan kos mitra</legend>
            <div class="map-canvas">
                @include('public.partials.home.indonesia-map')

                @foreach ($petaKota as $kota)
                    <div class="pin-slot" style="left: {{ $kota['x'] }}%; top: {{ $kota['y'] }}%;">
                        <input class="pin-input" type="radio" name="kos-map"
                               id="p-{{ $kota['slug'] }}" value="{{ $kota['slug'] }}" @checked($loop->first)>
                        <label class="pin" for="p-{{ $kota['slug'] }}"
                               aria-label="{{ $kota['kota'] }}, {{ $kota['jumlah_kos'] }} kos, {{ $kota['jumlah_kosong'] }} kamar kosong">
                            <span class="pin-pulse"></span>
                            <span class="pin-dot"></span>
                            <span class="pin-ring"></span>
                            <span class="pin-tip">{{ $kota['kota'] }} · <b>{{ $kota['jumlah_kos'] }} kos</b></span>
                        </label>
                    </div>
                @endforeach
            </div>

            <div class="map-cards" aria-live="off">
                @foreach ($petaKota as $kota)
                    <div class="mc-group" data-kota="{{ $kota['slug'] }}">
                        @foreach ($kota['kos'] as $index => $kos)
                            <input class="mc-input" type="radio" name="kos-card-{{ $kota['slug'] }}"
                                   id="c-{{ $kota['slug'] }}-{{ $index }}" @checked($loop->first)>
                        @endforeach

                        <div class="mc-track">
                            @foreach ($kota['kos'] as $index => $kos)
                                <article class="mc-card" data-kota="{{ $kota['slug'] }}" data-index="{{ $index }}">
                                    <div class="mc-head">
                                        <span class="mc-badge">Mitra</span>
                                        <span class="mc-coord">{{ $kos['jumlah_kosong'] }} kamar kosong</span>
                                    </div>
                                    <h3>{{ $kos['nama'] }}</h3>
                                    <p class="mc-names">{{ $kota['kota'] }} · {{ $kos['jumlah_kamar'] }} kamar</p>
                                    <div class="mc-figs">
                                        <div><b>{{ $kos['jumlah_kamar'] }}</b><span>kamar</span></div>
                                        <div><b>{{ $kos['jumlah_kosong'] }}</b><span>kosong</span></div>
                                        <div><b>{{ $kos['jumlah_penghuni'] }}</b><span>penghuni</span></div>
                                    </div>
                                    @if ($kos['harga_min'] !== null)
                                        <div class="mc-price">
                                            {{ Rupiah::ringkas($kos['harga_min']) }} – {{ Rupiah::ringkas($kos['harga_max']) }}
                                            <span>/ bulan</span>
                                        </div>
                                    @else
                                        <div class="mc-price mc-price-empty">Belum ada kamar kosong</div>
                                    @endif
                                    <a class="mc-link" href="{{ route('public.kos', $kos['slug']) }}">Lihat kos →</a>
                                </article>
                            @endforeach
                        </div>

                        @if (count($kota['kos']) > 1)
                            <div class="mc-controls">
                                <button type="button" class="mc-arrow" data-mc-prev aria-label="Kos sebelumnya" hidden>
                                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                                </button>
                                <div class="mc-nav">
                                    @foreach ($kota['kos'] as $index => $kos)
                                        <label class="mc-dot" for="c-{{ $kota['slug'] }}-{{ $index }}"
                                               aria-label="Tampilkan {{ $kos['nama'] }}"></label>
                                    @endforeach
                                </div>
                                <button type="button" class="mc-arrow" data-mc-next aria-label="Kos berikutnya" hidden>
                                    <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                                </button>
                                <button type="button" class="mc-pause" data-mc-pause aria-pressed="false" aria-label="Jeda pergeseran otomatis" hidden>
                                    <svg class="mc-ico-pause" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" fill="currentColor"><rect x="7" y="5" width="3.5" height="14" rx="1"/><rect x="13.5" y="5" width="3.5" height="14" rx="1"/></svg>
                                    <svg class="mc-ico-play" viewBox="0 0 24 24" width="14" height="14" aria-hidden="true" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </fieldset>
    @endif
</section>
