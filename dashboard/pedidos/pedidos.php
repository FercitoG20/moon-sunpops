<?php
require_once '../conexion.php';
$mensaje_exito = ''; $mensaje_error = '';

// --- LÓGICA: REVERTIR PEDIDO (LIMPIEZA PROFUNDA Y EXACTA) ---
if (isset($_POST['revertir_pedido'])) {
    $titulo_pedido = $_POST['titulo_pedido'];
    try {
        $conexion->beginTransaction();
        $stmt = $conexion->prepare("SELECT codigo, cantidad FROM calculo WHERE titulo = ? AND estado_stock = 'ingresado'");
        $stmt->execute([$titulo_pedido]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($productos) > 0) {
            foreach ($productos as $p) {
                $cod = $p['codigo'];
                $cant_ped = $p['cantidad'];

                // 1. Buscar ventas de hoy de este producto para eliminarlas
                $hoy = date('Y-m-d');
                $stmt_v = $conexion->prepare("
                    SELECT dv.id, dv.venta_id, dv.cantidad, dv.subtotal 
                    FROM detalle_ventas dv
                    JOIN ventas v ON dv.venta_id = v.id
                    WHERE dv.codigo = ? AND DATE(v.fecha) = ?
                ");
                $stmt_v->execute([$cod, $hoy]);
                $ventas_hoy = $stmt_v->fetchAll(PDO::FETCH_ASSOC);

                foreach ($ventas_hoy as $v) {
                    // 👉 ESTA ES LA LÍNEA QUE FALTABA: 
                    // Regresar al stock lo que se había "vendido" para neutralizar
                    $conexion->prepare("UPDATE inventario SET stock = stock + ? WHERE codigo = ?")
                             ->execute([$v['cantidad'], $cod]);

                    // Restar el subtotal de la venta principal
                    $conexion->prepare("UPDATE ventas SET total = total - ? WHERE id = ?")
                             ->execute([$v['subtotal'], $v['venta_id']]);
                    
                    // Borrar el detalle de la venta
                    $conexion->prepare("DELETE FROM detalle_ventas WHERE id = ?")
                             ->execute([$v['id']]);
                }

                // 2. Ajustar Inventario: AHORA SÍ, restar lo que entró del pedido
                $conexion->prepare("UPDATE inventario SET stock = stock - ? WHERE codigo = ?")
                         ->execute([$cant_ped, $cod]);
            }

            // 3. Limpiar ventas vacías y regresar pedido a pendiente
            $conexion->query("DELETE FROM ventas WHERE total <= 0");
            $conexion->prepare("UPDATE calculo SET estado_stock = 'pendiente' WHERE titulo = ?")->execute([$titulo_pedido]);

            $conexion->commit();
            $mensaje_exito = "¡Reversión exitosa! Se ajustó el stock a 0 y se eliminaron las ventas de hoy.";
        }
    } catch (Exception $e) { 
        $conexion->rollBack(); 
        $mensaje_error = "Error: " . $e->getMessage(); 
    }
}

// --- LÓGICA: INGRESAR Y ELIMINAR ---
if (isset($_POST['ingresar_pedido'])) {
    $titulo = $_POST['titulo_pedido'];
    try {
        $conexion->beginTransaction();
        $stmt = $conexion->prepare("SELECT codigo, cantidad FROM calculo WHERE titulo = ? AND estado_stock = 'pendiente'");
        $stmt->execute([$titulo]);
        foreach ($stmt->fetchAll() as $p) {
            $conexion->prepare("INSERT INTO inventario (codigo, stock) VALUES (?, ?) ON DUPLICATE KEY UPDATE stock = stock + VALUES(stock)")->execute([$p['codigo'], $p['cantidad']]);
        }
        $conexion->prepare("UPDATE calculo SET estado_stock = 'ingresado' WHERE titulo = ?")->execute([$titulo]);
        $conexion->commit();
        $mensaje_exito = "Pedido ingresado al congelador.";
    } catch (Exception $e) { $conexion->rollBack(); }
}

if (isset($_POST['eliminar_pedido'])) {
    $conexion->prepare("DELETE FROM calculo WHERE titulo = ? AND estado_stock = 'pendiente'")->execute([$_POST['titulo_pedido']]);
    $mensaje_exito = "Pedido eliminado.";
}

$pedidos = $conexion->query("SELECT titulo, fecha, SUM(cantidad) as total_piezas, SUM(inversion) as costo_total, estado_stock FROM calculo GROUP BY titulo, fecha, estado_stock ORDER BY fecha DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="pedidos/pedidos.css">
<div class="main-container">
    <div class="header-premium">
        <h1>MIS <span>Pedidos</span></h1>
        <p>Control de Entradas y Ajustes de Inventario</p>
    </div>

    <?php if($mensaje_exito): ?> <div class="alerta alerta-exito"><?php echo $mensaje_exito; ?></div> <?php endif; ?>
    <?php if($mensaje_error): ?> <div class="alerta alerta-error"><?php echo $mensaje_error; ?></div> <?php endif; ?>

    <div class="pedidos-grid">
        <?php foreach($pedidos as $ped): $es_p = ($ped['estado_stock'] === 'pendiente'); ?>
            <div class="pedido-card <?= $es_p ? 'card-pendiente' : 'card-ingresado' ?>">
                <div class="pedido-header">
                    <h3><?=$ped['titulo']?></h3>
                    <span class="badge-estado"><?= $es_p ? 'Pendiente' : 'En Stock' ?></span>
                </div>
                <div class="pedido-body">
                    <div class="info-item"><span class="label">Piezas:</span><span class="value"><?=$ped['total_piezas']?></span></div>
                    <div class="info-item"><span class="label">Inversión:</span><span class="value">$<?=number_format($ped['costo_total'], 2)?></span></div>
                </div>
                <div class="pedido-footer">
                    <form method="POST">
                        <input type="hidden" name="titulo_pedido" value="<?=$ped['titulo']?>">
                        <?php if($es_p): ?>
                            <button type="submit" name="ingresar_pedido" class="btn-ingresar">Dar Entrada</button>
                            <button type="submit" name="eliminar_pedido" class="btn-eliminar" onclick="return confirm('¿Borrar pedido?')"><i class="fa-solid fa-trash"></i></button>
                        <?php else: ?>
                            <button type="submit" name="revertir_pedido" class="btn-revertir" onclick="return confirm('¡AVISO! Esto borrará las ventas de hoy de estos productos para cuadrar caja. ¿Continuar?')">Deshacer Todo</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>