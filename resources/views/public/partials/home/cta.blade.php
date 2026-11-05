<section class="section-cta">
    <div class="cta-container">
        <h2>Siap merapikan kos?</h2>
        <p>Mulai kelola kamar, tagihan, dan pembayaran dari satu tempat.</p>
        <div class="cta-actions">
            @if (config('kelolakos.kontak_admin'))
                <a href="mailto:{{ config('kelolakos.kontak_admin') }}" class="btn-gold">Hubungi admin</a>
            @else
                <a href="{{ route('login') }}" class="btn-gold">Masuk sebagai pengurus</a>
            @endif
            <a href="{{ route('register') }}" class="btn-outline">Buat akun penghuni</a>
        </div>
    </div>
</section>
