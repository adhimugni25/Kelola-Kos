<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <div class="brand-mark">K</div>
            <div>
                <div class="footer-name">KelolaKos</div>
                <div class="footer-tagline">Kelola kos lebih rapi.</div>
            </div>
        </div>

        <nav class="footer-nav" aria-label="Navigasi footer">
            <a href="#jaringan">Jaringan</a>
            <a href="#kamar">Kamar</a>
            <a href="#pengurus">Pengurus</a>
            <a href="#faq">FAQ</a>
        </nav>

        <div class="footer-meta">
            <span>© 2026 KelolaKos</span>
            @if (config('kelolakos.kontak_admin'))
                <span>·</span>
                <a href="mailto:{{ config('kelolakos.kontak_admin') }}">{{ config('kelolakos.kontak_admin') }}</a>
            @endif
        </div>
    </div>
</footer>
