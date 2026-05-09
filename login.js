document.addEventListener('DOMContentLoaded', () => {
    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');
    togglePassword.addEventListener('click', function (e) {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
    const loginForm = document.getElementById('loginForm');
    const btnLogin = document.querySelector('.btn-login');
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const originalText = btnLogin.innerHTML;
        btnLogin.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Accediendo...';
        btnLogin.style.opacity = '0.8';
        setTimeout(() => {
            btnLogin.innerHTML = originalText;
            btnLogin.style.opacity = '1';
            alert('¡Diseño listo! Aquí iría la redirección al dashboard.php');
        }, 1500);
    });

});