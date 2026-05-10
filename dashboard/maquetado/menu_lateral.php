<nav class="side-menu glass-panel" id="sideMenu">

    <a href="dashboard.php?view=inicio">
        <i class="fa-solid fa-house"></i> Inicio
    </a>

    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>

        <a href="dashboard.php?view=calculador">
            <i class="fa-solid fa-calculator"></i> Calculos
        </a>

        <a href="dashboard.php?view=pedidos">
            <i class="fa-solid fa-cart-shopping"></i> Pedidos
        </a>

        <a href="dashboard.php?view=modificar">
            <i class="fa-solid fa-ice-cream"></i> Catalogo
        </a>

        <a href="dashboard.php?view=inventario">
            <i class="fa-solid fa-boxes-stacked"></i> Inventario
        </a>

        <a href="dashboard.php?view=estadisticas">
            <i class="fa-solid fa-chart-pie"></i> Estadisticas
        </a>

        <a href="dashboard.php?view=ventas-modificadas">
            <i class="fa-solid fa-clock-rotate-left"></i> Historial
        </a>

    <?php else: ?>

        <a href="dashboard.php?view=ventas">
            <i class="fa-solid fa-cash-register"></i> Nueva Venta
        </a>

    <?php endif; ?>

    <div class="spacer"></div>

    <a href="../logout.php" class="btn-salir">
        <i class="fa-solid fa-right-from-bracket"></i> Salir
    </a>

</nav>