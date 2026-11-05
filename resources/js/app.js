import Chart from 'chart.js/auto';

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

document.querySelectorAll('form[data-konfirmasi]').forEach((formulir) => {
    formulir.addEventListener('submit', (peristiwa) => {
        if (!window.confirm(formulir.dataset.konfirmasi)) {
            peristiwa.preventDefault();
        }
    });
});

const panelPeta = document.querySelector('[data-peta]');

if (panelPeta !== null) {
    const grupKartu = [...panelPeta.querySelectorAll('.mc-group')];
    const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)');
    let timerPergeseran = null;
    let dijeda = false;

    const grupTampil = () => grupKartu.find((grup) => getComputedStyle(grup).display !== 'none') ?? null;

    const aturPanah = () => {
        const grup = grupTampil();
        const jumlah = grup === null ? 0 : grup.querySelectorAll('.mc-input').length;

        panelPeta.querySelectorAll('.mc-arrow, .mc-pause').forEach((tombol) => {
            tombol.hidden = jumlah < 2;
        });
    };

    const hentikanPergeseran = () => {
        if (timerPergeseran !== null) {
            window.clearInterval(timerPergeseran);
            timerPergeseran = null;
        }
    };

    const geserKartu = (arah) => {
        const grup = grupTampil();

        if (grup === null) {
            return;
        }

        const pilihan = [...grup.querySelectorAll('.mc-input')];

        if (pilihan.length < 2) {
            return;
        }

        const aktif = Math.max(0, pilihan.findIndex((input) => input.checked));
        const berikut = pilihan[(aktif + arah + pilihan.length) % pilihan.length];

        berikut.checked = true;
        berikut.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const mulaiPergeseran = () => {
        hentikanPergeseran();

        if (!dijeda && !kurangiGerak.matches) {
            timerPergeseran = window.setInterval(() => geserKartu(1), 6000);
        }
    };

    panelPeta.addEventListener('change', () => {
        aturPanah();
        mulaiPergeseran();
    });

    panelPeta.addEventListener('click', (peristiwa) => {
        const tombolJeda = peristiwa.target.closest('[data-mc-pause]');

        if (tombolJeda !== null) {
            dijeda = !dijeda;
            tombolJeda.setAttribute('aria-pressed', String(dijeda));
            tombolJeda.setAttribute('aria-label', dijeda ? 'Lanjutkan pergeseran otomatis' : 'Jeda pergeseran otomatis');
            mulaiPergeseran();

            return;
        }

        const tombol = peristiwa.target.closest('.mc-arrow');

        if (tombol !== null) {
            geserKartu(tombol.hasAttribute('data-mc-prev') ? -1 : 1);
        }
    });

    panelPeta.addEventListener('pointerenter', hentikanPergeseran);
    panelPeta.addEventListener('pointerleave', mulaiPergeseran);
    panelPeta.addEventListener('focusin', hentikanPergeseran);
    panelPeta.addEventListener('focusout', (peristiwa) => {
        if (!panelPeta.contains(peristiwa.relatedTarget)) {
            mulaiPergeseran();
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            hentikanPergeseran();
        } else {
            mulaiPergeseran();
        }
    });

    kurangiGerak.addEventListener('change', mulaiPergeseran);

    aturPanah();
    mulaiPergeseran();
}

const kanvas = document.getElementById('grafik-pendapatan');
const sumberData = document.getElementById('data-pendapatan');

if (kanvas !== null && sumberData !== null) {
    const data = JSON.parse(sumberData.textContent);

    new Chart(kanvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Pendapatan (Rp)',
                    data: data.nilai,
                    backgroundColor: 'rgba(5, 150, 105, 0.7)',
                    borderColor: 'rgba(5, 150, 105, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (nilai) => 'Rp ' + new Intl.NumberFormat('id-ID').format(nilai),
                    },
                },
            },
        },
    });
}
