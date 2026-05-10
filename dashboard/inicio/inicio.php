<?php
require_once '../conexion.php';

// 1. OBLIGAR A PHP A USAR LA HORA DE MÉXICO PARA EVITAR DESFASES DE DÍAS
date_default_timezone_set('America/Mexico_City');

// Inicializar el carrito en sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$mensaje_exito = '';
$mensaje_error = '';

// Recibir errores por URL (ej. cuando llegas al límite de stock pulsando "+")
if (isset($_GET['err']) && $_GET['err'] == 'limite') {
    $mensaje_error = "¡Límite alcanzado! No tienes más piezas en el congelador.";
}

// --- LÓGICA DE ACCIONES DEL CARRITO (+, -, ELIMINAR, VACIAR) ---
if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];
    $codigo_act = $_GET['codigo'] ?? '';

    if ($accion == 'vaciar') {
        $_SESSION['carrito'] = [];
        echo "<script>window.location.href='dashboard.php?view=inicio';</script>"; exit;
    } elseif ($accion == 'eliminar' && isset($_SESSION['carrito'][$codigo_act])) {
        unset($_SESSION['carrito'][$codigo_act]);
        echo "<script>window.location.href='dashboard.php?view=inicio';</script>"; exit;
    } elseif ($accion == 'sumar' && isset($_SESSION['carrito'][$codigo_act])) {
        if ($_SESSION['carrito'][$codigo_act]['cantidad'] < $_SESSION['carrito'][$codigo_act]['stock_max']) {
            $_SESSION['carrito'][$codigo_act]['cantidad']++;
            echo "<script>window.location.href='dashboard.php?view=inicio';</script>"; exit;
        } else {
            echo "<script>window.location.href='dashboard.php?view=inicio&err=limite';</script>"; exit;
        }
    } elseif ($accion == 'restar' && isset($_SESSION['carrito'][$codigo_act])) {
        if ($_SESSION['carrito'][$codigo_act]['cantidad'] > 1) {
            $_SESSION['carrito'][$codigo_act]['cantidad']--;
        }
        echo "<script>window.location.href='dashboard.php?view=inicio';</script>"; exit;
    }
}

// --- LÓGICA DEL ESCÁNER (AGREGAR AL CARRITO O COBRAR) ---
if (isset($_POST['escanear_codigo'])) {
    $codigo = trim($_POST['codigo_escaneado']);

    // Si el código escaneado es la palabra mágica "COBRAR"
    if (strtoupper($codigo) === 'COBRAR') {
        if (!empty($_SESSION['carrito'])) {
            $_POST['completar_venta'] = true; // Forzamos el gatillo de la venta
        } else {
            $mensaje_error = "El carrito está vacío, escanea productos antes de cobrar.";
        }
    } 
    // Si es un código de barras normal
    else {
        // Buscar la paleta Y SU STOCK ACTUAL en la base de datos
        $stmt = $conexion->prepare("
            SELECT p.nombre, p.costo, COALESCE(i.stock, 0) as stock 
            FROM paletasmoonsunpops p 
            LEFT JOIN inventario i ON p.codigo = i.codigo 
            WHERE p.codigo = ? LIMIT 1
        ");
        $stmt->execute([$codigo]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($producto) {
            $cantidad_actual_en_carrito = isset($_SESSION['carrito'][$codigo]) ? $_SESSION['carrito'][$codigo]['cantidad'] : 0;

            // VALIDACIÓN DE STOCK
            if ($producto['stock'] <= 0) {
                $mensaje_error = "¡Agotado! Ya no hay stock disponible de " . $producto['nombre'] . ".";
            } elseif (($cantidad_actual_en_carrito + 1) > $producto['stock']) {
                $mensaje_error = "¡Límite alcanzado! Solo tienes " . $producto['stock'] . " piezas de " . $producto['nombre'] . " en el congelador.";
            } else {
                // Si hay stock suficiente, le sumamos 1 o lo creamos
                if (isset($_SESSION['carrito'][$codigo])) {
                    $_SESSION['carrito'][$codigo]['cantidad'] += 1;
                    
                    // TRUCO PARA LA PILA: Lo sacamos y lo volvemos a meter para que actualice su posición al final del array
                    $item_temp = $_SESSION['carrito'][$codigo];
                    unset($_SESSION['carrito'][$codigo]);
                    $_SESSION['carrito'][$codigo] = $item_temp;
                } else {
                    $_SESSION['carrito'][$codigo] = [
                        'nombre' => $producto['nombre'],
                        'precio' => $producto['costo'],
                        'cantidad' => 1,
                        'stock_max' => $producto['stock'] // Guardamos el límite para los botones + y -
                    ];
                }
            }
        } else {
            $mensaje_error = "Código no encontrado en el catálogo.";
        }
    }
}

// --- LÓGICA PARA COMPLETAR VENTA ---
if (isset($_POST['completar_venta']) && !empty($_SESSION['carrito'])) {
    try {
        $conexion->beginTransaction();
        
        $total_venta = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total_venta += ($item['precio'] * $item['cantidad']);
        }

        // 2. Insertar la Venta General (FORZANDO LA FECHA Y HORA EXACTA DE MÉXICO)
        $fecha_actual = date('Y-m-d H:i:s');
        $stmt_venta = $conexion->prepare("INSERT INTO ventas (total, fecha) VALUES (?, ?)");
        $stmt_venta->execute([$total_venta, $fecha_actual]);
        $id_venta = $conexion->lastInsertId();

        // 3. Insertar los Detalles y Descontar Inventario
        $stmt_detalle = $conexion->prepare("INSERT INTO detalle_ventas (venta_id, codigo, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt_inventario = $conexion->prepare("UPDATE inventario SET stock = stock - ? WHERE codigo = ?");

        foreach ($_SESSION['carrito'] as $codigo => $item) {
            $subtotal = $item['precio'] * $item['cantidad'];
            $stmt_detalle->execute([$id_venta, $codigo, $item['cantidad'], $item['precio'], $subtotal]);
            
            // Descontar del inventario
            $stmt_inventario->execute([$item['cantidad'], $codigo]);
        }

        $conexion->commit();
        $_SESSION['carrito'] = []; // Vaciar carrito tras la venta
        $mensaje_exito = "¡Venta por $".number_format($total_venta, 2)." registrada con éxito!";
    } catch (Exception $e) {
        $conexion->rollBack();
        $mensaje_error = "Error al procesar la venta: " . $e->getMessage();
    }
}

// --- CONSULTAS PARA LAS TARJETAS (MÉTRICAS DE HOY) ---
// Ahora $hoy siempre será la fecha correcta en México
$hoy = date('Y-m-d');

// 1. Ventas de Hoy ($)
$q_ventas_hoy = $conexion->query("SELECT COALESCE(SUM(total), 0) as total FROM ventas WHERE DATE(fecha) = '$hoy'")->fetch(PDO::FETCH_ASSOC);
$ventas_hoy = $q_ventas_hoy['total'];

// 2. Paletas Vendidas Hoy
$q_paletas_hoy = $conexion->query("SELECT COALESCE(SUM(dv.cantidad), 0) as total_p FROM detalle_ventas dv INNER JOIN ventas v ON dv.venta_id = v.id WHERE DATE(v.fecha) = '$hoy'")->fetch(PDO::FETCH_ASSOC);
$paletas_hoy = $q_paletas_hoy['total_p'];

// 3. Sabor Más Vendido
$q_top_sabor = $conexion->query("
    SELECT p.nombre, SUM(dv.cantidad) as total_vendido 
    FROM detalle_ventas dv 
    JOIN ventas v ON dv.venta_id = v.id 
    JOIN paletasmoonsunpops p ON dv.codigo = p.codigo 
    WHERE DATE(v.fecha) = '$hoy' 
    GROUP BY p.codigo 
    ORDER BY total_vendido DESC LIMIT 1
")->fetch(PDO::FETCH_ASSOC);
$sabor_top = $q_top_sabor ? $q_top_sabor['nombre'] : 'Sin ventas aún';
?>

<link rel="stylesheet" href="inicio/inicio.css">

<div class="main-container">
    
    <?php if($mensaje_error): ?>
        <div class="alerta alerta-error"><i class="fa-solid fa-circle-xmark"></i> <?php echo $mensaje_error; ?></div>
    <?php endif; ?>
    <?php if($mensaje_exito): ?>
        <div class="alerta alerta-exito"><i class="fa-solid fa-rocket"></i> <?php echo $mensaje_exito; ?></div>
    <?php endif; ?>

    <div class="metrics-grid">
        <div class="metric-card">
            <div class="metric-icon gold"><i class="fa-solid fa-sack-dollar"></i></div>
            <div class="metric-info">
                <p>Ventas Hoy</p>
                <h3>$<?=number_format($ventas_hoy, 2)?></h3>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon purple"><i class="fa-solid fa-ice-cream"></i></div>
            <div class="metric-info">
                <p>Paletas Vendidas</p>
                <h3><?=$paletas_hoy?> pz</h3>
            </div>
        </div>
        <div class="metric-card">
            <div class="metric-icon red"><i class="fa-solid fa-crown"></i></div>
            <div class="metric-info">
                <p>Estrella de Hoy</p>
                <h3 style="font-size: 1.1rem; padding-top: 5px;"><?=$sabor_top?></h3>
            </div>
        </div>
    </div>

    <div class="pos-layout">
        <div class="pos-scanner card">
            <div class="card-header"><i class="fa-solid fa-barcode"></i> Escáner de Productos</div>
            <form method="POST" action="dashboard.php?view=inicio" class="scanner-form">
                <p class="scanner-help">Escanea el código, tecléalo o escanea "COBRAR"</p>
                <div class="scanner-input-wrap">
                    <i class="fa-solid fa-qrcode"></i>
                    <input type="text" name="codigo_escaneado" id="inputScanner" placeholder="Código de barras..." autofocus autocomplete="off" required>
                </div>
                <button type="submit" name="escanear_codigo" style="display:none;">Escanear</button>
            </form>
            <div class="scanner-animation">
                <div class="laser"></div>
            </div>
        </div>

        <div class="pos-cart card">
            <div class="card-header"><i class="fa-solid fa-cart-shopping"></i> Venta Actual</div>
            <div class="cart-items">
                <?php 
                $gran_total = 0;
                if(empty($_SESSION['carrito'])): 
                ?>
                    <div class="empty-cart">
                        <i class="fa-solid fa-basket-shopping"></i>
                        <p>El carrito está vacío</p>
                    </div>
                <?php else: ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Cant.</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Subtotal</th>
                                <th style="text-align: center;"><i class="fa-solid fa-bolt"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // TRUCO PARA LA VISTA: Invertimos el array preservando las claves para que lo último aparezca arriba
                            foreach(array_reverse($_SESSION['carrito'], true) as $codigo => $item): 
                                $sub = $item['cantidad'] * $item['precio'];
                                $gran_total += $sub;
                            ?>
                            <tr>
                                <td class="c-cant">
                                    <div class="cant-control">
                                        <a href="dashboard.php?view=inicio&accion=restar&codigo=<?=$codigo?>" class="btn-cant"><i class="fa-solid fa-minus"></i></a>
                                        <span class="cant-number"><?=$item['cantidad']?></span>
                                        <a href="dashboard.php?view=inicio&accion=sumar&codigo=<?=$codigo?>" class="btn-cant"><i class="fa-solid fa-plus"></i></a>
                                    </div>
                                </td>
                                <td><?=$item['nombre']?></td>
                                <td>$<?=number_format($item['precio'], 2)?></td>
                                <td class="c-sub"><strong>$<?=number_format($sub, 2)?></strong></td>
                                <td style="text-align: center;">
                                    <a href="dashboard.php?view=inicio&accion=eliminar&codigo=<?=$codigo?>" class="btn-del-item"><i class="fa-solid fa-xmark"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            
            <div class="cart-footer">
                <div class="cart-total">
                    <span>TOTAL A COBRAR:</span>
                    <span class="total-amount">$<?=number_format($gran_total, 2)?></span>
                </div>
                <div class="cart-actions">
                    <a href="dashboard.php?view=inicio&accion=vaciar" class="btn-clean"><i class="fa-solid fa-trash-can"></i> Vaciar</a>
                    <form method="POST" action="dashboard.php?view=inicio" style="flex:1;">
                        <button type="submit" name="completar_venta" class="btn-checkout" <?=empty($_SESSION['carrito'])?'disabled':''?>>
                            <i class="fa-solid fa-check-double"></i> COBRAR
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="inicio/inicio.js"></script>