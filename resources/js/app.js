import Chart from 'chart.js/auto';

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
