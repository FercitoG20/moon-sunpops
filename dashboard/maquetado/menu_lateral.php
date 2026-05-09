<nav class="side-menu glass-panel" id="sideMenu">
    <a href="dashboard.php?view=inicio"><i class="fa-solid fa-cloud"></i> Inicio</a>
    
    <?php if(isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin'): ?>
        <a href="dashboard.php?view=calculador"><i class="fa-solid fa-sun"></i> Pedidos y calculos</a>
        <a href="dashboard.php?view=modificar"><i class="fa-solid fa-ice-cream"></i> Catalogo</a>
    <?php else: ?>
        <a href="dashboard.php?view=ventas"><i class="fa-solid fa-meteor"></i> Nueva Venta</a>
    <?php endif; ?>
    
    <div class="spacer"></div>
    
    <a href="../logout.php" class="btn-salir"><i class="fa-solid fa-power-off"></i> Salir</a>
</nav>