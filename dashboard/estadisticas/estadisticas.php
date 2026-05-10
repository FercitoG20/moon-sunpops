<?php
require_once '../conexion.php';

// 1. MÉTRICAS GLOBALES
$q_inv = $conexion->query("SELECT SUM(inversion) as total FROM calculo WHERE estado_stock = 'ingresado'")->fetch(PDO::FETCH_ASSOC);
$total_inversion = $q_inv['total'] ?? 0;

$q_ventas = $conexion->query("SELECT SUM(total) as total FROM ventas")->fetch(PDO::FETCH_ASSOC);
$total_ventas = $q_ventas['total'] ?? 0;

$ganancia_neta = $total_ventas - $total_inversion;

// 2. DATOS PARA GRÁFICA DE VENTAS POR DÍA (Últimos 7 días)
$dias = []; $montos = [];
for ($i = 6; $i >= 0; $i--) {
    $fecha = date('Y-m-d', strtotime("-$i days"));
    $dias[] = date('d M', strtotime($fecha));
    $q = $conexion->query("SELECT SUM(total) as total FROM ventas WHERE DATE(fecha) = '$fecha'")->fetch(PDO::FETCH_ASSOC);
    $montos[] = $q['total'] ?? 0;
}

// 3. TOP 5 PALETAS MÁS VENDIDAS
$top_nombres = []; $top_cantidades = [];
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

// 4. LÓGICA PEPS (FIFO) Y ORDENACIÓN PARA LA TABLA (EL ÚLTIMO ARRIBA)
$q_ventas_hist = $conexion->query("SELECT codigo, SUM(cantidad) as total_vendido FROM detalle_ventas GROUP BY codigo");
$ventas_por_codigo = [];
while($row = $q_ventas_hist->fetch(PDO::FETCH_ASSOC)) {
    $ventas_por_codigo[$row['codigo']] = $row['total_vendido'];
}

// El query sigue siendo ASC para que el cálculo FIFO sea correcto (primero entra, primero sale)
$q_pedidos = $conexion->query("SELECT * FROM calculo WHERE estado_stock = 'ingresado' ORDER BY fecha ASC, id ASC");
$stats_por_pedido = [];

while($lote = $q_pedidos->fetch(PDO::FETCH_ASSOC)) {
    $titulo = $lote['titulo'];
    $codigo = $lote['codigo'];
    $cantidad_comprada = $lote['cantidad'];

    if(!isset($stats_por_pedido[$titulo])) {
        $stats_por_pedido[$titulo] = [
            'inversion' => 0,
            'piezas_compradas' => 0,
            'piezas_vendidas' => 0,
            'ingreso_real' => 0
        ];
    }

    $stats_por_pedido[$titulo]['inversion'] += $lote['inversion'];
    $stats_por_pedido[$titulo]['piezas_compradas'] += $cantidad_comprada;

    $vendidos_de_este_lote = 0;
    if(isset($ventas_por_codigo[$codigo]) && $ventas_por_codigo[$codigo] > 0) {
        if($ventas_por_codigo[$codigo] >= $cantidad_comprada) {
            $vendidos_de_este_lote = $cantidad_comprada;
            $ventas_por_codigo[$codigo] -= $cantidad_comprada; 
        } else {
            $vendidos_de_este_lote = $ventas_por_codigo[$codigo];
            $ventas_por_codigo[$codigo] = 0; 
        }
    }

    $stats_por_pedido[$titulo]['piezas_vendidas'] += $vendidos_de_este_lote;
    $stats_por_pedido[$titulo]['ingreso_real'] += ($vendidos_de_este_lote * $lote['preciov']);
}

// AQUÍ ESTÁ EL TRUCO: Invertimos el array para que el último agregado sea el primero en la lista (LIFO para la vista)
$stats_por_pedido_invertido = array_reverse($stats_por_pedido, true);
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
                <p>Inversión</p>
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
            <div class="card-header"><i class="fa-solid fa-calendar-days"></i> Tendencia de Ventas</div>
            <div class="canvas-wrapper">
                <canvas id="ventasSemana"></canvas>
            </div>
        </div>

        <div class="chart-container card">
            <div class="card-header"><i class="fa-solid fa-star"></i> Top 5 Sabores</div>
            <div class="canvas-wrapper">
                <canvas id="topSabores"></canvas>
            </div>
        </div>
    </div>

    <div class="card full-width" style="margin-top: 25px;">
        <div class="card-header"><i class="fa-solid fa-layer-group"></i> Rendimiento por Pedido (Recientes primero)</div>
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Inversión</th>
                        <th>Venta (Pz)</th>
                        <th>Ingreso</th>
                        <th>Ganancia</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($stats_por_pedido_invertido)): ?>
                        <tr><td colspan="6" style="text-align:center;">No hay pedidos registrados</td></tr>
                    <?php else: ?>
                        <?php foreach($stats_por_pedido_invertido as $titulo => $datos): 
                            $ganancia_lote = $datos['ingreso_real'] - $datos['inversion'];
                            $porcentaje_vendido = ($datos['piezas_compradas'] > 0) ? round(($datos['piezas_vendidas'] / $datos['piezas_compradas']) * 100) : 0;
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($titulo) ?></strong></td>
                            <td>$<?= number_format($datos['inversion'], 2) ?></td>
                            <td style="min-width: 120px;">
                                <div class="progress-info">
                                    <span><?= $datos['piezas_vendidas'] ?> / <?= $datos['piezas_compradas'] ?></span>
                                    <small><?= $porcentaje_vendido ?>%</small>
                                </div>
                                <div class="progress-bar-bg">
                                    <div class="progress-bar-fill" style="width: <?= $porcentaje_vendido ?>%;"></div>
                                </div>
                            </td>
                            <td>$<?= number_format($datos['ingreso_real'], 2) ?></td>
                            <td class="<?= $ganancia_lote >= 0 ? 'text-green' : 'text-red' ?>">
                                $<?= number_format($ganancia_lote, 2) ?>
                            </td>
                            <td>
                                <?php if($porcentaje_vendido >= 100): ?>
                                    <span class="badge badge-success">Vendido</span>
                                <?php elseif($ganancia_lote >= 0): ?>
                                    <span class="badge badge-profit">Recuperado</span>
                                <?php else: ?>
                                    <span class="badge badge-pending">Proceso</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const diasVentas = <?= json_encode($dias) ?>;
    const montosVentas = <?= json_encode($montos) ?>;
    const nombresTop = <?= json_encode($top_nombres) ?>;
    const cantidadesTop = <?= json_encode($top_cantidades) ?>;
</script>
<script src="estadisticas/estadisticas.js"></script>