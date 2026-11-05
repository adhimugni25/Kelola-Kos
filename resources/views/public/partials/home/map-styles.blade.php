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
