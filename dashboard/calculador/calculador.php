<?php
require_once '../conexion.php'; 

$fecha_activa = date('Y-m-d');
$titulo_activo = '';
$mensaje_error = '';
$mensaje_exito = '';

if (isset($_GET['crear_pedido'])) {
    $fecha_activa = $_GET['fecha_creacion'];
    $nuevo_titulo = trim($_GET['nuevo_titulo']);
    $stmt = $conexion->prepare("SELECT id FROM calculo WHERE titulo = ? LIMIT 1");
    $stmt->execute([$nuevo_titulo]);
    if ($stmt->rowCount() > 0) { $mensaje_error = "El pedido '$nuevo_titulo' ya existe."; } 
    else { $titulo_activo = $nuevo_titulo; $mensaje_exito = "¡Lote '$nuevo_titulo' iniciado!"; }
}
elseif (isset($_GET['buscar_pedido'])) {
    if (!empty($_GET['filtro_lista'])) {
        $titulo_buscado = trim($_GET['filtro_lista']);
        $stmt = $conexion->prepare("SELECT titulo, fecha FROM calculo WHERE titulo = ? LIMIT 1");
        $stmt->execute([$titulo_buscado]);
        if ($stmt->rowCount() > 0) { $row = $stmt->fetch(PDO::FETCH_ASSOC); $titulo_activo = $row['titulo']; $fecha_activa = $row['fecha']; }
    } 
    elseif (!empty($_GET['filtro_manual'])) {
        $busqueda = trim($_GET['filtro_manual']);
        $stmt = $conexion->prepare("SELECT titulo, fecha FROM calculo WHERE titulo LIKE ? LIMIT 1");
        $stmt->execute(["%$busqueda%"]);
        if ($stmt->rowCount() > 0) { $row = $stmt->fetch(PDO::FETCH_ASSOC); $titulo_activo = $row['titulo']; $fecha_activa = $row['fecha']; }
    } 
    elseif (!empty($_GET['fecha_filtro'])) { $fecha_activa = $_GET['fecha_filtro']; $titulo_activo = ""; }
}
elseif (isset($_GET['titulo_activo']) && $_GET['titulo_activo'] != "") { $titulo_activo = $_GET['titulo_activo']; $fecha_activa = $_GET['fecha_activa'] ?? date('Y-m-d'); } 

if(isset($_POST['agregar']) && $titulo_activo != "") {
    $codigo = $_POST['codigo'];
    $cantidad = (int)$_POST['cantidad'];
    $preciov = (float)$_POST['preciov'];
    $stmt = $conexion->prepare("SELECT id FROM calculo WHERE codigo = ? AND titulo = ?");
    $stmt->execute([$codigo, $titulo_activo]);
    if($stmt->rowCount() == 0) {
        $stmt_p = $conexion->prepare("SELECT * FROM paletaslunitas WHERE codigo = ?");
        $stmt_p->execute([$codigo]);
        $p = $stmt_p->fetch(PDO::FETCH_ASSOC);
        if($p) {
            $sql = "INSERT INTO calculo (fecha, titulo, codigo, categoria, sabor, cantidad, costopz, inversion, preciov, ganaciau, ganacial, ingresob) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $conexion->prepare($sql)->execute([$fecha_activa, $titulo_activo, $codigo, $p['categoria'], $p['nombre'], $cantidad, $p['costo'], ($cantidad * $p['costo']), $preciov, ($preciov - $p['costo']), (($preciov - $p['costo']) * $cantidad), ($cantidad * $preciov)]);
            echo "<script>window.location.href='dashboard.php?view=calculador&fecha_activa=$fecha_activa&titulo_activo=" . urlencode($titulo_activo) . "';</script>"; exit();
        }
    }
}

if(isset($_GET['accion']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $accion = $_GET['accion'];
    if($accion == 'eliminar') { $conexion->prepare("DELETE FROM calculo WHERE id = ?")->execute([$id]); } 
    elseif($accion == 'sumar') { $conexion->prepare("UPDATE calculo SET cantidad = cantidad + 1, inversion = cantidad * costopz, ganacial = ganaciau * cantidad, ingresob = cantidad * preciov WHERE id = ?")->execute([$id]); } 
    elseif($accion == 'restar') { $conexion->prepare("UPDATE calculo SET cantidad = cantidad - 1, inversion = cantidad * costopz, ganacial = ganaciau * cantidad, ingresob = cantidad * preciov WHERE id = ? AND cantidad > 1")->execute([$id]); }
    $ruta = "dashboard.php?view=calculador&fecha_activa=$fecha_activa" . ($titulo_activo != "" ? "&titulo_activo=" . urlencode($titulo_activo) : "");
    echo "<script>window.location.href='$ruta';</script>"; exit();
}

$stmt_datos = ($titulo_activo != "") ? $conexion->prepare("SELECT * FROM calculo WHERE titulo = ? ORDER BY id DESC") : $conexion->prepare("SELECT * FROM calculo WHERE fecha = ? ORDER BY id DESC");
$stmt_datos->execute([($titulo_activo != "" ? $titulo_activo : $fecha_activa)]);
$datos = $stmt_datos->fetchAll(PDO::FETCH_ASSOC);
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<link rel="stylesheet" href="calculador/calculador.css">

<div class="premium-calc-container">
    <div class="header-main">
        <h2><i class="fa-solid fa-calculator"></i> Calculador de Pedidos <span>Moon & Sun Pops</span></h2>
    </div>

    <?php if($mensaje_error): ?><div class="alerta alerta-error"><?=$mensaje_error?></div><?php endif; ?>
    <?php if($mensaje_exito): ?><div class="alerta alerta-exito"><?=$mensaje_exito?></div><?php endif; ?>

    <div class="top-grid">
        <div class="premium-card">
            <div class="card-title-choco"><i class="fa-solid fa-file-circle-plus"></i> Nuevo Pedido</div>
            <form method="GET" action="dashboard.php" class="form-choco">
                <input type="hidden" name="view" value="calculador">
                <div class="input-box grow"><label>Título del Lote</label><input type="text" name="nuevo_titulo" required placeholder="Ej: Pedido Semanal"></div>
                <div class="input-box"><label>Fecha</label><input type="date" name="fecha_creacion" value="<?=date('Y-m-d')?>"></div>
                <button type="submit" name="crear_pedido" class="btn-choco">Crear</button>
            </form>
        </div>

        <div class="premium-card">
            <div class="card-title-choco"><i class="fa-solid fa-magnifying-glass"></i> Buscar Historial</div>
            <form method="GET" action="dashboard.php" class="form-choco">
                <input type="hidden" name="view" value="calculador">
                <div class="input-box grow">
                    <label>Seleccionar Lote</label>
                    <select name="filtro_lista">
                        <option value="">-- Recientes --</option>
                        <?php
                        $list = $conexion->query("SELECT DISTINCT titulo FROM calculo ORDER BY id DESC LIMIT 10");
                        while($opt = $list->fetch(PDO::FETCH_ASSOC)) { echo "<option value='".htmlspecialchars($opt['titulo'])."'>{$opt['titulo']}</option>"; }
                        ?>
                    </select>
                </div>
                <button type="submit" name="buscar_pedido" class="btn-gold">Cargar</button>
            </form>
        </div>
    </div>

    <?php if($titulo_activo != ""): ?>
        <div class="status-bar-premium">
            <div class="status-info">
                <i class="fa-solid fa-ice-cream"></i> Editando: <strong id="pdfTitulo"><?=htmlspecialchars($titulo_activo)?></strong>
                <span class="badge-date" id="pdfFecha"><?=$fecha_activa?></span>
            </div>
            <button id="btnExportarPDF" class="btn-pdf-premium"><i class="fa-solid fa-file-pdf"></i> GENERAR ORDEN PDF</button>
        </div>

        <div class="premium-card entry-section">
            <form method="POST" action="dashboard.php?view=calculador&fecha_activa=<?=$fecha_activa?>&titulo_activo=<?=urlencode($titulo_activo)?>" class="form-choco">
                <div class="input-box grow">
                    <label>Sabor de Paleta</label>
                    <select name="codigo" id="selectorPaleta" required>
                        <option value="">Selecciona un sabor...</option>
                        <?php
                        $pal = $conexion->query("SELECT L.codigo, L.nombre, L.costo, M.costo AS venta FROM paletaslunitas L INNER JOIN paletasmoonsunpops M ON L.codigo = M.codigo ORDER BY L.nombre ASC");
                        while($p = $pal->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='{$p['codigo']}' data-costo='{$p['costo']}' data-venta='{$p['venta']}'>{$p['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="input-box width-small"><label>Costo</label><input type="text" id="costoProveedor" class="readonly-gold" readonly></div>
                <div class="input-box width-small"><label>Cant.</label><input type="number" name="cantidad" id="inputCantidad" required min="1" value="1"></div>
                <div class="input-box width-small"><label>P. Venta</label><input type="number" step="0.01" name="preciov" id="inputVenta" class="readonly-gold" readonly></div>
                <button type="submit" name="agregar" class="btn-choco">AGREGAR</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="table-container-premium">
        <table class="table-choco" id="tablaPedidos">
            <thead>
                <tr>
                    <th class="mobile-hide">Fecha</th>
                    <th class="mobile-hide">Pedido</th>
                    <th>Sabor</th>
                    <th>Categoría</th>
                    <th>Cant.</th>
                    <th>Costo</th>
                    <th>Inversión</th>
                    <th>Venta</th>
                    <th class="mobile-hide">Gan. U</th>
                    <th>Gan. Total</th>
                    <th>Ingreso</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $t_cant = 0; $t_inv = 0; $t_gan = 0; $t_ing = 0;
                foreach($datos as $row): 
                    $t_cant += $row['cantidad']; $t_inv += $row['inversion']; $t_gan += $row['ganacial']; $t_ing += $row['ingresob'];
                ?>
                <tr class="pdf-data-row">
                    <td class="mobile-hide text-muted"><?=$row['fecha']?></td>
                    <td class="mobile-hide text-choco-bold"><?=htmlspecialchars($row['titulo'])?></td>
                    <td class="pdf-sabor sabor-name"><?=htmlspecialchars($row['sabor'])?></td>
                    <td><span class="badge-cat-premium"><?=htmlspecialchars($row['categoria'])?></span></td>
                    <td class="pdf-cantidad text-bold"><?=$row['cantidad']?></td>
                    <td class="pdf-costo text-muted">$<?=number_format($row['costopz'], 2)?></td>
                    <td class="pdf-inversion text-red-bold">$<?=number_format($row['inversion'], 2)?></td>
                    <td class="text-muted">$<?=number_format($row['preciov'], 2)?></td>
                    <td class="mobile-hide text-green">$<?=number_format($row['ganaciau'], 2)?></td>
                    <td class="text-green-bold">$<?=number_format($row['ganacial'], 2)?></td>
                    <td class="text-gold-bold">$<?=number_format($row['ingresob'], 2)?></td>
                    <td>
                        <div class="action-flex">
                            <a href="dashboard.php?view=calculador&fecha_activa=<?=$fecha_activa?>&titulo_activo=<?=urlencode($titulo_activo ?? '')?>&accion=sumar&id=<?=$row['id']?>" class="btn-action-round btn-plus">+</a>
                            <a href="dashboard.php?view=calculador&fecha_activa=<?=$fecha_activa?>&titulo_activo=<?=urlencode($titulo_activo ?? '')?>&accion=restar&id=<?=$row['id']?>" class="btn-action-round btn-minus">-</a>
                            <a href="dashboard.php?view=calculador&fecha_activa=<?=$fecha_activa?>&titulo_activo=<?=urlencode($titulo_activo ?? '')?>&accion=eliminar&id=<?=$row['id']?>" class="btn-action-round btn-del"><i class="fa-solid fa-trash-can"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <?php if($datos): ?>
            <tfoot>
                <tr class="footer-premium">
                    <td colspan="4" class="text-right">TOTALES:</td>
                    <td><?=$t_cant?> pz</td>
                    <td></td>
                    <td class="text-red">$<?=number_format($t_inv, 2)?></td>
                    <td></td><td class="mobile-hide"></td>
                    <td class="text-green">$<?=number_format($t_gan, 2)?></td>
                    <td class="text-gold">$<?=number_format($t_ing, 2)?></td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<script src="calculador/calculador.js"></script>