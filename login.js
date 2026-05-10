document.addEventListener('DOMContentLoaded', () => {
    // 1. Funcionalidad de Mostrar/Ocultar Contraseña
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');
    
    togglePassword.addEventListener('click', function () {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    // 2. Animación del Botón al hacer submit (Permitiendo que el PHP funcione)
    const loginForm = document.getElementById('loginForm');
    const btnSubmit = document.getElementById('btnSubmit');

    loginForm.addEventListener('submit', () => {
        // Solo cambiamos el contenido visual del botón
        btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> <span>Verificando...</span>';
        btnSubmit.style.opacity = '0.9';
        btnSubmit.style.pointerEvents = 'none'; // Evita doble clic
        
        // NO usamos e.preventDefault() aquí, para que el formulario se envíe realmente a login.php
    });

    // 3. Prevenir usar el botón de "Atrás" después de cerrar sesión
    window.history.forward();
    function noBack() { window.history.forward(); }
    window.onload = noBack;
    window.onpageshow = function(evt) { if (evt.persisted) noBack(); }
    window.onunload = function() { void(0); }
});