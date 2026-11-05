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
