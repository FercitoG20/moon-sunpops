document.addEventListener('DOMContentLoaded', () => {
    
    const ctx = document.getElementById('ventasChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
            datasets: [{
                label: 'Ventas Registradas ($)',
                data: [1250, 1900, 1400, 2100, 2800, 4200, 3800],
                backgroundColor: '#E8A598',
                borderRadius: 6,
                hoverBackgroundColor: '#D69487'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#F4F1EB'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });

    const calcForm = document.getElementById('calcForm');
    const calcResult = document.getElementById('calcResult');

    calcForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        const costo = parseFloat(document.getElementById('costo').value);
        const precio = parseFloat(document.getElementById('precio').value);
        const cantidad = parseFloat(document.getElementById('cantidad').value);
        
        const ingresoTotal = precio * cantidad;
        const costoTotal = costo * cantidad;
        const gananciaNeta = ingresoTotal - costoTotal;

        calcResult.style.display = 'block';
        
        if (gananciaNeta > 0) {
            calcResult.style.borderLeft = "4px solid #4CAF50";
            calcResult.innerHTML = `Ganancia Neta: <span style="color:#4CAF50">+$${gananciaNeta.toFixed(2)}</span>`;
        } else if (gananciaNeta < 0) {
            calcResult.style.borderLeft = "4px solid #F44336";
            calcResult.innerHTML = `Pérdida: <span style="color:#F44336">-$${Math.abs(gananciaNeta).toFixed(2)}</span>`;
        } else {
            calcResult.style.borderLeft = "4px solid #8A817C";
            calcResult.innerHTML = `Sin ganancia ni pérdida: $0.00`;
        }
    });
});