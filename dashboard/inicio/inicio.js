document.addEventListener('DOMContentLoaded', () => {
    // Al cargar la página, el cursor se pone directo en el escáner automáticamente
    const inputScanner = document.getElementById('inputScanner');
    
    if(inputScanner) {
        // Asegurar que inicie seleccionado
        setTimeout(() => inputScanner.focus(), 100);
        
        // Si por alguna razón el usuario da click fuera, forzamos que vuelva al input 
        // para que no se pierda la lectura del escáner (muy útil en POS)
        document.addEventListener('click', (e) => {
            // No forzar el foco si el usuario está interactuando con botones de sumar, restar, eliminar o cobrar
            const isClickingButton = e.target.closest('button') || e.target.closest('a') || e.target.closest('input');
            
            if(!isClickingButton) {
                inputScanner.focus();
            }
        });
    }
});