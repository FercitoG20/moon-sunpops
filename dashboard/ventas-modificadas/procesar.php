<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'eliminar_producto') {
    $venta_id = $_POST['venta_id'];
    $codigo = $_POST['codigo'];
    $password = $_POST['password'];

    if ($password !== '@Fercito2212') {
        echo json_encode(['status' => 'error', 'message' => 'Contraseña incorrecta. Acceso denegado.']);
        exit;
    }

    try {
        $conexion->beginTransaction();

        // 1. Obtener la cantidad y el SUBTOTAL exacto de ese producto
        $stmt_detalle = $conexion->prepare("SELECT cantidad, subtotal FROM detalle_ventas WHERE venta_id = ? AND codigo = ? LIMIT 1");
        $stmt_detalle->execute([$venta_id, $codigo]);
        $item = $stmt_detalle->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            // 2. Regresar el stock al inventario
            $stmt_update_inv = $conexion->prepare("UPDATE inventario SET stock = stock + ? WHERE codigo = ?");
            $stmt_update_inv->execute([$item['cantidad'], $codigo]);

            // 3. RESTAR el dinero del producto al total general del ticket (para tus Estadísticas)
            $stmt_update_total = $conexion->prepare("UPDATE ventas SET total = total - ? WHERE id = ?");
            $stmt_update_total->execute([$item['subtotal'], $venta_id]);

            // 4. Eliminar solo ese producto de la venta
            $stmt_delete_item = $conexion->prepare("DELETE FROM detalle_ventas WHERE venta_id = ? AND codigo = ?");
            $stmt_delete_item->execute([$venta_id, $codigo]);

            // 5. Verificar si el ticket se quedó vacío (si borraste todas las paletas)
            $stmt_check = $conexion->prepare("SELECT COUNT(*) FROM detalle_ventas WHERE venta_id = ?");
            $stmt_check->execute([$venta_id]);
            
            // Si ya no hay productos en ese ticket, borramos el ticket completo
            if ($stmt_check->fetchColumn() == 0) {
                $stmt_delete_venta = $conexion->prepare("DELETE FROM ventas WHERE id = ?");
                $stmt_delete_venta->execute([$venta_id]);
            }

            $conexion->commit();
            echo json_encode(['status' => 'success', 'message' => 'Stock regresado, venta ajustada y producto eliminado.']);
            exit;
        } else {
            $conexion->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'El producto no existe en este ticket.']);
            exit;
        }

    } catch (Exception $e) {
        $conexion->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error de BD: ' . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Petición no válida.']);
}
?>