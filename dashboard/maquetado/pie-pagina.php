</div> 
    </main>

</div> <footer class="glass-panel">
    <style>
        footer { 
            padding: 1rem; 
            text-align: center; 
            color: var(--text-dark); 
            font-size: 0.9rem; 
            font-weight: 500;
            flex-shrink: 0; 
        }
        .footer-glow { color: var(--sun-gold); margin-right: 5px; }
        .footer-brand { color: var(--nebula-purple); font-family: 'Pacifico', cursive; font-size: 1.1rem; margin: 0 5px; }
    </style>
    
    <span class="footer-glow"><i class="fa-solid fa-star"></i></span> 
    &copy; <?php echo date('Y'); ?> 
    <span class="footer-brand">Moon & Sun Pops</span> 
</footer>

<script>
    const openMenu = document.getElementById('openMenu');
    const sideMenu = document.getElementById('sideMenu');

    function toggleMenu() {
        sideMenu.classList.toggle('oculto');
    }

    if(openMenu) openMenu.addEventListener('click', toggleMenu);

    window.history.forward();
    function noBack() { window.history.forward(); }
    window.onload = noBack;
    window.onpageshow = function(evt) { if (evt.persisted) noBack(); }
</script>
</body>
</html>