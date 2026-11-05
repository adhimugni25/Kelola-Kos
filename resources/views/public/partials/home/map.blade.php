@php use App\Support\Rupiah; @endphp

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
                    <div class="pin-slot" style="left: {{ $kota['x'] }}%; top: calc(10.1% + {{ $kota['y'] }}% * 0.798);">
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

            <aside class="map-side" aria-live="polite" style="display: none;">
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
                            {{ Rupiah::ringkas($kota['harga_min']) }} – {{ Rupiah::ringkas($kota['harga_max']) }}
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
