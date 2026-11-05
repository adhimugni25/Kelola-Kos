<style>
    @supports selector(:has(*)) {
        @foreach ($petaKota as $kota)
            .map-panel:has(#p-{{ $kota['slug'] }}:checked) .pin[for="p-{{ $kota['slug'] }}"] .pin-dot { transform: scale(1.32); background: linear-gradient(145deg, #F3E5AB, #D4AF37 55%, #A16207); box-shadow: 0 0 0 2px rgba(255,255,255,.95), 0 0 0 5px rgba(212,175,55,.28), 0 6px 18px rgba(161,98,7,.35); }
            .map-panel:has(#p-{{ $kota['slug'] }}:checked) .mc-group[data-kota="{{ $kota['slug'] }}"] { display: block; }
            @foreach ($kota['kos'] as $index => $kos)
                .mc-group[data-kota="{{ $kota['slug'] }}"]:has(#c-{{ $kota['slug'] }}-{{ $index }}:checked) .mc-card[data-index="{{ $index }}"] { opacity: 1; transform: translateX(0) rotateY(0); position: relative; }
                .mc-group[data-kota="{{ $kota['slug'] }}"]:has(#c-{{ $kota['slug'] }}-{{ $index }}:checked) .mc-dot[for="c-{{ $kota['slug'] }}-{{ $index }}"]::after { background: var(--color-gold-2); transform: scale(1.25); }
                .mc-group[data-kota="{{ $kota['slug'] }}"]:has(#c-{{ $kota['slug'] }}-{{ $index }}:focus-visible) .mc-dot[for="c-{{ $kota['slug'] }}-{{ $index }}"] { outline: 2px solid var(--color-gold); outline-offset: 1px; border-radius: 50%; }
            @endforeach
        @endforeach
    }

    @supports not selector(:has(*)) {
        .mc-group:first-child { display: block; }
        .mc-card:first-child { opacity: 1; transform: translateX(0) rotateY(0); position: relative; }
    }
</style>
