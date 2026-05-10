<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../conexion.php';

try {
    if (!isset($conexion)) {
        throw new Exception("No se encontró la variable \$conexion. Revisa tu archivo conexion.php");
    }

    // Usamos el precio_unitario y subtotal que ya están guardados en tu detalle_ventas
    $sql = "SELECT 
                v.id as ticket_id, 
                v.fecha, 
                dv.codigo, 
                dv.cantidad, 
                dv.precio_unitario as precio,
                dv.subtotal,
                IFNULL(p.nombre, 'Producto sin nombre') as nombre_producto
            FROM ventas v
            INNER JOIN detalle_ventas dv ON v.id = dv.venta_id
            LEFT JOIN paletasmoonsunpops p ON dv.codigo = p.codigo
            ORDER BY v.id DESC, v.fecha DESC";

    $query_ventas = $conexion->query($sql);

    if ($query_ventas === false) {
        $error = $conexion->errorInfo();
        throw new Exception("Error en SQL: " . $error[2]);
    }

    $historial_ventas = $query_ventas->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<div style='margin: 20px; padding: 20px; background-color: #f8d7da; color: #721c24; border-radius: 8px; border: 1px solid #f5c6cb;'>";
    echo "<strong>¡Ups! Ocurrió un problema:</strong><br><br>" . $e->getMessage();
    echo "</div>";
    $historial_ventas = [];
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link rel="stylesheet" href="ventas-modificadas/ventas-modificadas.css">

<div class="main-container">
    <div class="header-premium">
        <h1>GESTIÓN de <span>Ventas</span></h1>
        <p>Historial detallado. Anula productos específicos de un ticket.</p>
    </div>

    <div class="card full-width">
        <div class="card-header"><i class="fa-solid fa-list-ul"></i> Desglose de Productos Vendidos</div>
        <div class="table-responsive">
            <table class="premium-table">
                <thead>
                    <tr>
                        <th>Ticket ID</th>
                        <th>Fecha y Hora</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($historial_ventas)): ?>
                        <tr><td colspan="7" style="text-align:center;">No hay productos registrados aún.</td></tr>
                    <?php else: ?>
                        <?php foreach($historial_ventas as $item): ?>
                        <tr>
                            <td><strong>#<?= str_pad($item['ticket_id'], 5, "0", STR_PAD_LEFT) ?></strong></td>
                            <td>
                                <div class="date-badge">
                                    <i class="fa-regular fa-calendar"></i>
                                    <?= date('d M Y - h:i A', strtotime($item['fecha'])) ?>
                                </div>
                            </td>
                            <td>
                                <strong><?= $item['nombre_producto'] ?></strong><br>
                                <small style="color: #888;">Cód: <?= $item['codigo'] ?></small>
                            </td>
                            <td class="text-xl"><b><?= $item['cantidad'] ?>x</b></td>
                            
                            <td style="color: #666;">$<?= number_format($item['precio'], 2) ?></td>
                            <td class="text-green text-xl"><strong>$<?= number_format($item['subtotal'], 2) ?></strong></td>
                            
                            <td>
                                <button class="btn-danger" onclick="solicitarAnulacion(<?= $item['ticket_id'] ?>, '<?= $item['codigo'] ?>', '<?= htmlspecialchars($item['nombre_producto'], ENT_QUOTES) ?>')">
                                    <i class="fa-solid fa-trash-can"></i> Quitar Producto
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="ventas-modificadas/ventas-modificadas.js"></script>