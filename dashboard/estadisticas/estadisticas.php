<?php
require_once '../conexion.php';

// 1. MÉTRICAS GLOBALES
// Inversión Total (Todo lo que ha entrado al inventario)
$q_inv = $conexion->query("SELECT SUM(inversion) as total FROM calculo WHERE estado_stock = 'ingresado'")->fetch(PDO::FETCH_ASSOC);
$total_inversion = $q_inv['total'] ?? 0;

// Ventas Totales (Todo lo cobrado)
$q_ventas = $conexion->query("SELECT SUM(total) as total FROM ventas")->fetch(PDO::FETCH_ASSOC);
$total_ventas = $q_ventas['total'] ?? 0;

// Ganancia Bruta (Ventas - Inversión)
$ganancia_neta = $total_ventas - $total_inversion;

// 2. DATOS PARA GRÁFICA DE VENTAS POR DÍA (Últimos 7 días)
$dias = [];
$montos = [];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $dias[] = date('d M', strtotime($fecha));
    $q = $conexion->query("SELECT SUM(total) as total FROM ventas WHERE DATE(fecha) = '$fecha'")->fetch(PDO::FETCH_ASSOC);
    $montos[] = $q['total'] ?? 0;
}

// 3. TOP 5 PALETAS MÁS VENDIDAS
$top_nombres = [];
$top_cantidades = [];
$q_top = $conexion->query("
    SELECT p.nombre, SUM(dv.cantidad) as total 
    FROM detalle_ventas dv 
    JOIN paletasmoonsunpops p ON dv.codigo = p.codigo 
    GROUP BY p.codigo ORDER BY total DESC LIMIT 5
");
while($r = $q_top->fetch(PDO::FETCH_ASSOC)) {
    $top_nombres[] = $r['nombre'];
    $top_cantidades[] = $r['total'];
}
?>

<link rel="stylesheet" href="estadisticas/estadisticas.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="main-container">
    <div class="header-premium">
        <h1>PANEL de <span>Estadísticas</span></h1>
        <p>Análisis financiero de MoonSun Pops</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card gold">
            <div class="stat-icon"><i class="fa-solid fa-money-bill-trend-up"></i></div>
            <div class="stat-info">
                <p>Ventas Totales</p>
                <h3>$<?= number_format($total_ventas, 2) ?></h3>
            </div>
        </div>

        <div class="stat-card choco">
            <div class="stat-icon"><i class="fa-solid fa-hand-holding-dollar"></i></div>
            <div class="stat-info">
                <p>Inversión en Stock</p>
                <h3>$<?= number_format($total_inversion, 2) ?></h3>
            </div>
        </div>

        <div class="stat-card <?= $ganancia_neta >= 0 ? 'green' : 'red' ?>">
            <div class="stat-icon"><i class="fa-solid fa-chart-line"></i></div>
            <div class="stat-info">
                <p>Balance / Ganancia</p>
                <h3>$<?= number_format($ganancia_neta, 2) ?></h3>
            </div>
        </div>
    </div>

    <div class="charts-layout">
        <div class="chart-container card">
            <div class="card-header">Tendencia de Ventas (7 días)</div>
            <canvas id="ventasSemana"></canvas>
        </div>

        <div class="chart-container card">
            <div class="card-header">Top 5 Sabores Favoritos</div>
            <canvas id="topSabores"></canvas>
        </div>
    </div>
</div>

<script>
    // Pasar datos de PHP a JS
    const diasVentas = <?= json_encode($dias) ?>;
    const montosVentas = <?= json_encode($montos) ?>;
    const nombresTop = <?= json_encode($top_nombres) ?>;
    const cantidadesTop = <?= json_encode($top_cantidades) ?>;
</script>
<script src="estadisticas/estadisticas.js"></script>