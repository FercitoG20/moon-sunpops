<?php
require_once '../conexion.php'; 

$mensaje_exito = '';
$mensaje_error = '';

if (isset($_POST['agregar_categoria'])) {
    $nueva_categoria = trim($_POST['nombre_categoria']);
    try {
        $conexion->prepare("INSERT INTO categorias (nombre) VALUES (?)")->execute([$nueva_categoria]);
        $mensaje_exito = "¡Categoría '$nueva_categoria' lista para el catálogo!";
    } catch (PDOException $e) {
        $mensaje_error = "Esa categoría ya existe.";
    }
}

if (isset($_GET['eliminar_categoria'])) {
    $cat_del = $_GET['eliminar_categoria'];
    try {
        $conexion->prepare("DELETE FROM categorias WHERE nombre = ?")->execute([$cat_del]);
        $mensaje_exito = "Categoría eliminada con éxito.";
    } catch (PDOException $e) {
        $mensaje_error = "No se puede eliminar: hay productos usándola.";
    }
}

if (isset($_POST['agregar_paleta'])) {
    $codigo = trim($_POST['codigo']);
    $nombre = trim($_POST['nombre']);
    $categoria = trim($_POST['categoria']);
    $costo_lunitas = (float)$_POST['costo_lunitas'];
    $precio_moonsun = (float)$_POST['precio_moonsun'];

    try {
        $check = $conexion->prepare("SELECT id FROM paletaslunitas WHERE codigo = ? OR nombre = ?");
        $check->execute([$codigo, $nombre]);
        
        if ($check->rowCount() > 0) {
            $mensaje_error = "¡Atención! El Código o el Nombre ya están registrados.";
        } else {
            $conexion->prepare("INSERT INTO paletaslunitas (codigo, nombre, categoria, costo) VALUES (?, ?, ?, ?)")->execute([$codigo, $nombre, $categoria, $costo_lunitas]);
            $conexion->prepare("INSERT INTO paletasmoonsunpops (codigo, nombre, categoria, costo) VALUES (?, ?, ?, ?)")->execute([$codigo, $nombre, $categoria, $precio_moonsun]);
            $mensaje_exito = "¡Sabor '$nombre' registrado exitosamente!";
        }
    } catch (PDOException $e) { $mensaje_error = "Error: " . $e->getMessage(); }
}

if (isset($_POST['guardar_edicion_inline'])) {
    $codigo_edit = $_POST['codigo_edit'];
    $nuevo_costo = (float)$_POST['nuevo_costo'];
    $nuevo_precio = (float)$_POST['nuevo_precio'];
    try {
        $conexion->prepare("UPDATE paletaslunitas SET costo = ? WHERE codigo = ?")->execute([$nuevo_costo, $codigo_edit]);
        $conexion->prepare("UPDATE paletasmoonsunpops SET costo = ? WHERE codigo = ?")->execute([$nuevo_precio, $codigo_edit]);
        $mensaje_exito = "¡Precios actualizados para $codigo_edit!";
    } catch (PDOException $e) { $mensaje_error = "Error al actualizar precios."; }
}

if (isset($_GET['eliminar_producto'])) {
    $codigo_del = $_GET['eliminar_producto'];
    try {
        $conexion->prepare("DELETE FROM paletasmoonsunpops WHERE codigo = ?")->execute([$codigo_del]);
        $conexion->prepare("DELETE FROM paletaslunitas WHERE codigo = ?")->execute([$codigo_del]);
        $mensaje_exito = "Producto removido del catálogo.";
    } catch (PDOException $e) { $mensaje_error = "Error al eliminar."; }
}

$catalogo = $conexion->query("SELECT l.codigo, l.nombre, l.categoria, l.costo AS costo_lunitas, m.costo AS precio_venta  
                 FROM paletaslunitas l LEFT JOIN paletasmoonsunpops m ON l.codigo = m.codigo ORDER BY l.categoria, l.nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $conexion->query("SELECT nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="modificar/modificar.css">

<div class="main-container">
    <div class="header-premium">
        <div class="logo-area">
            <h1>MOON & SUN <span>Pops</span></h1>
            <p>Gestión de Catálogo y Precios</p>
        </div>
    </div>

    <?php if($mensaje_error): ?>
        <div class="alerta alerta-error"><i class="fa-solid fa-circle-xmark"></i> <?php echo $mensaje_error; ?></div>
    <?php endif; ?>
    <?php if($mensaje_exito): ?>
        <div class="alerta alerta-exito"><i class="fa-solid fa-circle-check"></i> <?php echo $mensaje_exito; ?></div>
    <?php endif; ?>

    <div class="grid-layout">
        <div class="card card-choco">
            <div class="card-header"><i class="fa-solid fa-plus-circle"></i> Nuevo Sabor</div>
            <form method="POST" action="dashboard.php?view=modificar" class="form-premium">
                <div class="input-row">
                    <div class="input-group">
                        <label>Código</label>
                        <input type="text" name="codigo" placeholder="P-001" required>
                    </div>
                    <div class="input-group grow">
                        <label>Nombre del Sabor</label>
                        <input type="text" name="nombre" placeholder="Ej. Chocolate Suizo" required>
                    </div>
                </div>
                <div class="input-row">
                    <div class="input-group grow">
                        <label>Categoría</label>
                        <select name="categoria" required>
                            <option value="">Seleccione...</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?=$cat['nombre']?>"><?=$cat['nombre']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Costo Lun.</label>
                        <input type="number" step="0.01" name="costo_lunitas" required>
                    </div>
                    <div class="input-group">
                        <label>P. Venta M&S</label>
                        <input type="number" step="0.01" name="precio_moonsun" required>
                    </div>
                </div>
                <button type="submit" name="agregar_paleta" class="btn-premium">REGISTRAR EN CATÁLOGO</button>
            </form>
        </div>

        <div class="card card-caramel">
            <div class="card-header"><i class="fa-solid fa-folder-tree"></i> Categorías</div>
            <form method="POST" action="dashboard.php?view=modificar" class="form-mini">
                <input type="text" name="nombre_categoria" placeholder="Nueva..." required>
                <button type="submit" name="agregar_categoria" class="btn-add">+</button>
            </form>
            <div class="tag-container">
                <?php foreach($categorias as $cat): ?>
                    <div class="tag-item">
                        <span><?=$cat['nombre']?></span>
                        <a href="dashboard.php?view=modificar&eliminar_categoria=<?=urlencode($cat['nombre'])?>" onclick="return confirm('¿Eliminar?')">×</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="table-card">
        <table class="table-premium">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Sabor</th>
                    <th>Categoría</th>
                    <th>Costo Lun.</th>
                    <th>Venta M&S</th>
                    <th>Margen</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($catalogo as $item): 
                    $c = htmlspecialchars($item['codigo']);
                    $margen = $item['precio_venta'] - $item['costo_lunitas'];
                ?>
                <tr>
                    <td class="td-code"><?=$c?></td>
                    <td class="td-sabor"><?=$item['nombre']?></td>
                    <td><span class="badge-premium"><?=$item['categoria']?></span></td>
                    <td class="td-price-red">
                        <span id="txt_costo_<?=$c?>">$<?=number_format($item['costo_lunitas'], 2)?></span>
                        <input type="number" step="0.01" name="nuevo_costo" id="inp_costo_<?=$c?>" 
                               value="<?=$item['costo_lunitas']?>" class="edit-input" form="form_<?=$c?>" style="display:none;">
                    </td>
                    <td class="td-price-gold">
                        <span id="txt_venta_<?=$c?>">$<?=number_format($item['precio_venta'], 2)?></span>
                        <input type="number" step="0.01" name="nuevo_precio" id="inp_venta_<?=$c?>" 
                               value="<?=$item['precio_venta']?>" class="edit-input" form="form_<?=$c?>" style="display:none;">
                    </td>
                    <td class="td-margin">$<?=number_format($margen, 2)?></td>
                    <td>
                        <form id="form_<?=$c?>" method="POST" action="dashboard.php?view=modificar" style="display:none;">
                            <input type="hidden" name="codigo_edit" value="<?=$c?>">
                            <input type="hidden" name="guardar_edicion_inline" value="1">
                        </form>
                        <div id="box_v_<?=$c?>" class="action-btns">
                            <button class="btn-action btn-edit" onclick="activarEdicion('<?=$c?>')"><i class="fa-solid fa-pen-nib"></i></button>
                            <a href="dashboard.php?view=modificar&eliminar_producto=<?=$c?>" class="btn-action btn-del" onclick="return confirm('¿Borrar sabor?')"><i class="fa-solid fa-trash-can"></i></a>
                        </div>
                        <div id="box_e_<?=$c?>" class="action-btns" style="display:none;">
                            <button type="submit" form="form_<?=$c?>" class="btn-action btn-save"><i class="fa-solid fa-check"></i></button>
                            <button class="btn-action btn-cancel" onclick="cancelarEdicion('<?=$c?>')"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="modificar/modificar.js"></script>