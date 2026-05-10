document.addEventListener('DOMContentLoaded', () => {
    
    // Configuración común para que se vean bien en móvil
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        }
    };

    // 1. Gráfica de Líneas
    const ctxVentas = document.getElementById('ventasSemana').getContext('2d');
    new Chart(ctxVentas, {
        type: 'line',
        data: {
            labels: diasVentas,
            datasets: [{
                data: montosVentas,
                borderColor: '#bc6c25',
                backgroundColor: 'rgba(188, 108, 37, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: commonOptions
    });

    // 2. Gráfica de Barras
    const ctxTop = document.getElementById('topSabores').getContext('2d');
    new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: nombresTop,
            datasets: [{
                data: cantidadesTop,
                backgroundColor: ['#2d1b14', '#bc6c25', '#dda15e', '#606c38', '#4a4e69'],
                borderRadius: 5
            }]
        },
        options: {
            ...commonOptions,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});