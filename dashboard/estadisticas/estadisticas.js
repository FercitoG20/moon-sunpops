document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Gráfica de Líneas: Ventas de la Semana
    const ctxVentas = document.getElementById('ventasSemana').getContext('2d');
    new Chart(ctxVentas, {
        type: 'line',
        data: {
            labels: diasVentas,
            datasets: [{
                label: 'Venta Diaria ($)',
                data: montosVentas,
                borderColor: '#bc6c25',
                backgroundColor: 'rgba(188, 108, 37, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#bc6c25'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    // 2. Gráfica de Barras: Top Sabores
    const ctxTop = document.getElementById('topSabores').getContext('2d');
    new Chart(ctxTop, {
        type: 'bar',
        data: {
            labels: nombresTop,
            datasets: [{
                label: 'Piezas Vendidas',
                data: cantidadesTop,
                backgroundColor: [
                    '#2d1b14', '#bc6c25', '#dda15e', '#606c38', '#4a4e69'
                ],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
});