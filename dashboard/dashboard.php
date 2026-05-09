<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require 'maquetado/encabezado.php';

$view = isset($_GET['view']) ? $_GET['view'] : 'inicio';

switch ($view) {
    case 'inicio':
        require 'inicio/inicio.php';
        break;
        
    case 'calculador':
        require 'calculador/calculador.php';
        break;

    case 'pedidos':
        require 'pedidos/pedidos.php'; 
        break;

    case 'modificar':
        require 'modificar/modificar.php'; 
        break;
    
    case 'inventario':
        require 'inventario/inventario.php'; 
        break;

    case 'estadisticas':
        require 'estadisticas/estadisticas.php'; 
        break;
        
    case 'ventas':
        echo "<h2 style='color:var(--sun-gold)'><i class='fa-solid fa-rocket'></i> Módulo de Ventas Estelares</h2>
              <p>Iniciando secuencia de despacho de paletas...</p>";
        break;
        
    default:
        require 'inicio/inicio.php';
        break;
}

require 'maquetado/pie-pagina.php';
?>