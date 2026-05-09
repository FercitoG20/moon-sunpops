document.addEventListener('DOMContentLoaded', () => {
    const buscador = document.getElementById('busquedaStock');
    const filas = document.querySelectorAll('.stock-row');

    if (buscador) {
        buscador.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();

            filas.forEach(fila => {
                const nombre = fila.querySelector('.p-name').textContent.toLowerCase();
                const codigo = fila.querySelector('.p-code').textContent.toLowerCase();

                if (nombre.includes(term) || codigo.includes(term)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
});