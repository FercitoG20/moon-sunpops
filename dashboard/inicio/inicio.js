document.addEventListener('DOMContentLoaded', () => {
    // Al cargar la página, el cursor se pone directo en el escáner automáticamente
    const inputScanner = document.getElementById('inputScanner');
    
    if(inputScanner) {
        inputScanner.focus();
        
        // Si por alguna razón el usuario da click fuera, forzamos que vuelva al input 
        // para que no se pierda la lectura del escáner (muy útil en POS)
        document.addEventListener('click', (e) => {
            // Solo regresamos el foco si no está clickeando botones de borrar o cobrar
            if(!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('input')) {
                inputScanner.focus();
            }
        });
    }
});