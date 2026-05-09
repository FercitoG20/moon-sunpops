<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "moon&sunpops";
$conn = new mysqli($host, $user, $pass, $db);

$fecha_activa = date('Y-m-d');
$titulo_activo = '';
$mensaje_error = '';
$mensaje_exito = '';

if (isset($_GET['crear_pedido'])) {
    $fecha_activa = $_GET['fecha_creacion'];
    $nuevo_titulo = trim($_GET['nuevo_titulo']);
    $safe_titulo = $conn->real_escape_string($nuevo_titulo);

    $check = $conn->query("SELECT id FROM calculo WHERE titulo = '$safe_titulo' LIMIT 1");
    
    if ($check->num_rows > 0) {
        $mensaje_error = "¡Error! El pedido '$nuevo_titulo' ya existe. Usa los filtros de la derecha para abrirlo.";
    } else {
        $titulo_activo = $nuevo_titulo;
        $mensaje_exito = "¡Pedido '$nuevo_titulo' iniciado! Comienza a agregar productos.";
    }
}

elseif (isset($_GET['buscar_pedido'])) {

    if (!empty($_GET['filtro_lista'])) {
        $titulo_buscado = $conn->real_escape_string(trim($_GET['filtro_lista']));
        $match = $conn->query("SELECT titulo, fecha FROM calculo WHERE titulo = '$titulo_buscado' LIMIT 1");
        
        if ($match->num_rows > 0) {
            $row = $match->fetch_assoc();
            $titulo_activo = $row['titulo'];
            $fecha_activa = $row['fecha'];
            $mensaje_exito = "Abriendo pedido: " . $titulo_activo;
        }
    } 
    elseif (!empty($_GET['filtro_manual'])) {
        $busqueda = $conn->real_escape_string(trim($_GET['filtro_manual']));
        $match = $conn->query("SELECT titulo, fecha FROM calculo WHERE titulo LIKE '%$busqueda%' LIMIT 1");
        
        if ($match->num_rows > 0) {
            $row = $match->fetch_assoc();
            $titulo_activo = $row['titulo'];
            $fecha_activa = $row['fecha'];
            $mensaje_exito = "Coincidencia encontrada. Abriendo pedido: " . $titulo_activo;
        } else {
            $mensaje_error = "No se encontraron pedidos que contengan la palabra '$busqueda'.";
        }
    } 
    elseif (!empty($_GET['fecha_filtro'])) {
        $fecha_activa = $_GET['fecha_filtro'];
        $titulo_activo = ""; // Se limpia el título para ver todo lo del día
        $mensaje_exito = "Mostrando el resumen global de todos los pedidos del día: " . $fecha_activa;
    }
}

elseif (isset($_GET['titulo_activo']) && $_GET['titulo_activo'] != "") {
    $titulo_activo = $_GET['titulo_activo'];
    $fecha_activa = $_GET['fecha_activa'] ?? date('Y-m-d');
} 
elseif (isset($_GET['fecha_activa'])) {
    $fecha_activa = $_GET['fecha_activa'];
}

if(isset($_POST['agregar']) && $titulo_activo != "") {
    $codigo = $_POST['codigo'];
    $cantidad = $_POST['cantidad'];
    $preciov = $_POST['preciov'];
    
    $titulo_insert = $conn->real_escape_string($titulo_activo);
    $check = $conn->query("SELECT id FROM calculo WHERE codigo = '$codigo' AND titulo = '$titulo_insert'");
    
    if($check->num_rows > 0) {
        $mensaje_error = "¡Ese sabor ya está en este pedido! Usa el botón + en la tabla para aumentar.";
    } else {
        $res = $conn->query("SELECT * FROM paletaslunitas WHERE codigo = '$codigo'");
        $p = $res->fetch_assoc();

        $costopz = $p['costo'];
        $nombre = $p['nombre'];
        $cat = $p['categoria'];

        $inversion = $cantidad * $costopz;
        $ganaciau = $preciov - $costopz;
        $ganacial = $ganaciau * $cantidad;
        $ingresob = $cantidad * $preciov;

        $sql = "INSERT INTO calculo (fecha, titulo, codigo, categoria, sabor, cantidad, costopz, inversion, preciov, ganaciau, ganacial, ingresob) 
                VALUES ('$fecha_activa', '$titulo_insert', '$codigo', '$cat', '$nombre', $cantidad, $costopz, $inversion, $preciov, $ganaciau, $ganacial, $ingresob)";
        $conn->query($sql);
        
        header("Location: ?fecha_activa=$fecha_activa&titulo_activo=" . urlencode($titulo_activo));
        exit();
    }
}
if(isset($_GET['accion']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $accion = $_GET['accion'];

    if($accion == 'eliminar') {
        $conn->query("DELETE FROM calculo WHERE id = $id");
    } 
    elseif($accion == 'sumar') {
        $conn->query("UPDATE calculo SET cantidad = cantidad + 1, inversion = cantidad * costopz, ganacial = ganaciau * cantidad, ingresob = cantidad * preciov WHERE id = $id");
    } 
    elseif($accion == 'restar') {
        $conn->query("UPDATE calculo SET cantidad = cantidad - 1, inversion = cantidad * costopz, ganacial = ganaciau * cantidad, ingresob = cantidad * preciov WHERE id = $id AND cantidad > 1");
    }
    
    $ruta = "?fecha_activa=$fecha_activa";
    if($titulo_activo != "") $ruta .= "&titulo_activo=" . urlencode($titulo_activo);
    header("Location: $ruta");
    exit();
}

$query_sql = "SELECT * FROM calculo WHERE 1=1";
if ($titulo_activo != "") {
    $query_sql .= " AND titulo = '".$conn->real_escape_string($titulo_activo)."'";
} else {
    $query_sql .= " AND fecha = '$fecha_activa'";
}
$query_sql .= " ORDER BY id DESC";
$datos = $conn->query($query_sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor Avanzado de Pedidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style> .card-header { font-size: 1.1rem; } </style>
</head>
<body class="bg-light pb-5">
<div class="container-fluid mt-4 px-4">
    
    <h2 class="mb-4 text-dark fw-bold border-bottom border-3 border-dark pb-2">Gestor Avanzado de Pedidos Lunitas</h2>

    <?php if($mensaje_error != ""): ?>
        <div class="alert alert-danger fw-bold text-center shadow-sm"><?php echo $mensaje_error; ?></div>
    <?php endif; ?>
    <?php if($mensaje_exito != ""): ?>
        <div class="alert alert-success fw-bold text-center shadow-sm"><?php echo $mensaje_exito; ?></div>
    <?php endif; ?>

    <div class="row mb-4 align-items-stretch">
        
        <div class="col-lg-4 mb-3 mb-lg-0">
            <div class="card shadow-sm border-success h-100">
                <div class="card-header bg-success text-white fw-bold">➕ 1. Crear Nuevo Pedido</div>
                <div class="card-body bg-white">
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">A) Nombre del Pedido (Obligatorio):</label>
                            <input type="text" name="nuevo_titulo" class="form-control border-success fw-bold" placeholder="Ej: Pedido Sabado..." required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">B) Asignar Fecha:</label>
                            <input type="date" name="fecha_creacion" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <button type="submit" name="crear_pedido" class="btn btn-success w-100 fw-bold">Crear e Iniciar Captura</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-header bg-primary text-white fw-bold">🔍 2. Búsqueda Inteligente (No necesitas llenar todos)</div>
                <div class="card-body bg-white">
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Por Nombre (Lista):</label>
                                <select name="filtro_lista" class="form-select border-primary">
                                    <option value="">-- Selecciona un pedido --</option>
                                    <?php
                                    $list_query = $conn->query("SELECT DISTINCT titulo FROM calculo ORDER BY titulo ASC");
                                    while($opt = $list_query->fetch_assoc()) {
                                        echo "<option value='".htmlspecialchars($opt['titulo'])."'>{$opt['titulo']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">Por Nombre (Escribir):</label>
                                <input type="text" name="filtro_manual" class="form-control border-primary" placeholder="Escribe para buscar..." autocomplete="off">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted">O ver resumen por Fecha:</label>
                                <input type="date" name="fecha_filtro" class="form-control border-primary">
                            </div>
                        </div>
                        
                        <div class="mt-4 d-flex justify-content-end gap-2">
                            <a href="?" class="btn btn-outline-secondary fw-bold">Limpiar Todo</a>
                            <button type="submit" name="buscar_pedido" class="btn btn-primary fw-bold px-5">Buscar Lote</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> <?php if($titulo_activo != ""): ?>
        <div class="mb-2 px-2 d-flex justify-content-between align-items-end">
            <h5 class="text-muted m-0">Editando el pedido: <span class="badge bg-dark fs-5 shadow-sm"><?php echo htmlspecialchars($titulo_activo); ?></span></h5>
            <span class="badge bg-secondary">Fecha del lote: <?php echo $fecha_activa; ?></span>
        </div>

        <div class="card card-body mb-4 shadow border-0 bg-white border-top border-4 border-dark">
            <form method="POST" action="?fecha_activa=<?php echo $fecha_activa; ?>&titulo_activo=<?php echo urlencode($titulo_activo); ?>" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted small fw-bold">Seleccionar Paleta</label>
                    <select name="codigo" id="selectorPaleta" class="form-select border-dark fw-bold text-primary" onchange="actualizarPrecios()" required autofocus>
                        <option value="">Selecciona sabor...</option>
                        <?php
                        $sql_paletas = "SELECT L.codigo, L.nombre, L.costo AS costo_lunitas, M.costo AS precio_moon 
                                        FROM paletaslunitas L 
                                        INNER JOIN paletasmoonsunpops M ON L.codigo = M.codigo 
                                        ORDER BY L.nombre ASC";
                        $paletas = $conn->query($sql_paletas);
                        while($row = $paletas->fetch_assoc()) {
                            echo "<option value='{$row['codigo']}' data-costo='{$row['costo_lunitas']}' data-venta='{$row['precio_moon']}'>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted small">Costo P/Z</label>
                    <input type="text" id="costoProveedor" class="form-control bg-light text-danger fw-bold" readonly tabindex="-1">
                </div>

                <div class="col-md-2">
                    <label class="form-label text-muted small fw-bold">Cantidad</label>
                    <input type="number" name="cantidad" id="inputCantidad" class="form-control border-dark text-center fw-bold fs-5" required min="1" value="1" autocomplete="off">
                </div>
                
                <div class="col-md-2">
                    <label class="form-label text-muted small fw-bold">Precio Venta</label>
                    <input type="number" step="0.01" name="preciov" id="inputVenta" class="form-control bg-light text-success fw-bold" readonly tabindex="-1" required>
                </div>
                
                <div class="col-md-2">
                    <button type="submit" name="agregar" class="btn btn-dark w-100 fw-bold fs-5">✚ Agregar</button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-warning text-center p-4 shadow-sm rounded-3 mb-4">
            <h5 class="fw-bold m-0 text-dark">⚠️ Estás en modo "Resumen Global". Usa el panel de búsqueda o crea un pedido para capturar.</h5>
        </div>
    <?php endif; ?>

    <div class="table-responsive bg-white shadow-sm rounded border">
        <table class="table table-striped table-hover mb-0 align-middle text-center table-sm pb-2">
            <thead class="table-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Título Lote</th>
                    <th>Código</th>
                    <th>Categoría</th>
                    <th>Sabor</th>
                    <th>Cant.</th>
                    <th>Costo P/Z</th>
                    <th>Inversión</th>
                    <th>Precio V.</th>
                    <th>Ganancia U.</th>
                    <th>Ganancia T.</th>
                    <th>Ingreso B.</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $t_cantidad = 0; $t_inversion = 0; $t_ganancia = 0; $t_ingreso = 0;

                while($row = $datos->fetch_assoc()): 
                    $t_cantidad += $row['cantidad'];
                    $t_inversion += $row['inversion'];
                    $t_ganancia += $row['ganacial'];
                    $t_ingreso += $row['ingresob'];
                ?>
                <tr>
                    <td class="small text-muted"><?php echo $row['fecha']; ?></td>
                    <td class="small fw-bold text-primary"><?php echo $row['titulo']; ?></td>
                    <td class="small text-muted"><?php echo $row['codigo']; ?></td>
                    <td><span class="badge bg-success bg-opacity-25 text-success border border-success"><?php echo $row['categoria']; ?></span></td>
                    <td class="fw-bold text-start"><?php echo $row['sabor']; ?></td>
                    <td class="fw-bold fs-6 text-dark"><?php echo $row['cantidad']; ?></td>
                    <td class="text-muted">$<?php echo $row['costopz']; ?></td>
                    <td class="text-danger fw-bold bg-danger bg-opacity-10">$<?php echo $row['inversion']; ?></td>
                    <td class="text-muted">$<?php echo $row['preciov']; ?></td>
                    <td class="text-success">$<?php echo $row['ganaciau']; ?></td>
                    <td class="fw-bold text-success bg-success bg-opacity-10">$<?php echo $row['ganacial']; ?></td>
                    <td class="fw-bold">$<?php echo $row['ingresob']; ?></td>
                    
                    <td>
                        <div class="btn-group shadow-sm">
                            <a href="?fecha_activa=<?php echo $fecha_activa; ?>&titulo_activo=<?php echo urlencode($titulo_activo ?? ''); ?>&accion=sumar&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success fw-bold px-2">+</a>
                            <a href="?fecha_activa=<?php echo $fecha_activa; ?>&titulo_activo=<?php echo urlencode($titulo_activo ?? ''); ?>&accion=restar&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning fw-bold text-dark px-2">-</a>
                            <a href="?fecha_activa=<?php echo $fecha_activa; ?>&titulo_activo=<?php echo urlencode($titulo_activo ?? ''); ?>&accion=eliminar&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger fw-bold px-2" onclick="return confirm('¿Borrar paleta?');">🗑️</a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>

                <?php if($datos->num_rows == 0): ?>
                <tr>
                    <td colspan="13" class="text-center text-muted py-4 fs-5">No hay datos para mostrar en esta vista.</td>
                </tr>
                <?php endif; ?>
            </tbody>
            <?php if($datos->num_rows > 0): ?>
            <tfoot class="table-secondary border-top border-dark border-2">
                <tr class="fw-bold fs-6">
                    <td colspan="5" class="text-end text-dark">TOTALES:</td>
                    <td class="text-primary fs-5"><?php echo $t_cantidad; ?> pz</td>
                    <td></td>
                    <td class="text-danger">$<?php echo number_format($t_inversion, 2); ?></td>
                    <td></td>
                    <td></td>
                    <td class="text-success">$<?php echo number_format($t_ganancia, 2); ?></td>
                    <td class="text-dark">$<?php echo number_format($t_ingreso, 2); ?></td>
                    <td></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
function actualizarPrecios() {
    var select = document.getElementById("selectorPaleta");
    var opcion = select.options[select.selectedIndex];
    
    if(select.value !== "") {
        var costo = opcion.getAttribute("data-costo");
        var venta = opcion.getAttribute("data-venta");
        
        document.getElementById("costoProveedor").value = costo ? "$" + costo : "";
        document.getElementById("inputVenta").value = venta ? venta : "";
        
        var inputCantidad = document.getElementById("inputCantidad");
        inputCantidad.focus();
        inputCantidad.select();
    } else {
        document.getElementById("costoProveedor").value = "";
        document.getElementById("inputVenta").value = "";
    }
}
</script>
</body>
</html>