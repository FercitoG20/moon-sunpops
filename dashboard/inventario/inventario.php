<?php
require_once '../conexion.php';

// Consulta para traer el stock real cruzado con tus precios y nombres
// Usamos LEFT JOIN por si acaso hay un código en inventario que aún no terminas de configurar en tu catálogo
$query = "
    SELECT 
        i.codigo, 
        p.nombre, 
        p.categoria, 
        p.costo as precio_venta,
        i.stock, 
        i.ultima_act 
    FROM inventario i 
    INNER JOIN paletasmoonsunpops p ON i.codigo = p.codigo 
    ORDER BY i.stock ASC, p.nombre ASC
";
$productos = $conexion->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Metricas rápidas para los círculos de arriba
$total_piezas = $conexion->query("SELECT SUM(stock) FROM inventario")->fetchColumn() ?: 0;
$agotados = $conexion->query("SELECT COUNT(*) FROM inventario WHERE stock <= 0")->fetchColumn() ?: 0;
?>

<link rel="stylesheet" href="inventario/inventario.css">

<div class="main-container">
    <div class="header-premium">
        <div class="logo-area">
            <h1>STOCK <span>Real</span></h1>
            <p>Lo que tienes disponible en el congelador hoy</p>
        </div>
        <div class="header-stats">
            <div class="stat-pill">
                <span class="pill-label">Total Piezas:</span>
                <span class="pill-value"><?=$total_piezas?></span>
            </div>
            <div class="stat-pill pill-danger">
                <span class="pill-label">Agotados:</span>
                <span class="pill-value"><?=$agotados?></span>
            </div>
        </div>
    </div>

    <div class="inventory-card">
        <div class="top-tools">
            <div class="search-wrapper">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="busquedaStock" placeholder="Buscar por sabor o código..." autocomplete="off">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-modern" id="tablaStock">
                <thead>
                    <tr>
                        <th>Producto / Sabor</th>
                        <th>Categoría</th>
                        <th>Precio Venta</th>
                        <th style="text-align:center;">Stock</th>
                        <th>Estado</th>
                        <th>Último Mov.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($productos as $item): 
                        $stock = $item['stock'];
                        // Lógica de colores para el stock
                        $clase_stock = 'stock-ok';
                        $status_text = 'Disponible';
                        $status_icon = 'fa-check-circle';

                        if($stock <= 0) {
                            $clase_stock = 'stock-empty';
                            $status_text = 'Agotado';
                            $status_icon = 'fa-circle-xmark';
                        } elseif ($stock <= 10) {
                            $clase_stock = 'stock-low';
                            $status_text = 'Bajo';
                            $status_icon = 'fa-triangle-exclamation';
                        }
                    ?>
                    <tr class="stock-row">
                        <td>
                            <div class="prod-info">
                                <span class="p-name"><?=$item['nombre']?></span>
                                <span class="p-code"><?=$item['codigo']?></span>
                            </div>
                        </td>
                        <td><span class="badge-cat"><?=$item['categoria']?></span></td>
                        <td class="p-price">$<?=number_format($item['precio_venta'], 2)?></td>
                        <td class="p-qty <?=$clase_stock?>">
                            <strong><?=$stock?></strong> <small>pz</small>
                        </td>
                        <td>
                            <div class="status-badge <?=$clase_stock?>">
                                <i class="fa-solid <?=$status_icon?>"></i> <?=$status_text?>
                            </div>
                        </td>
                        <td class="p-time">
                            <?=date('d/m H:i', strtotime($item['ultima_act']))?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="inventario/inventario.js"></script>