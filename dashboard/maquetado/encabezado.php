<?php
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard | Moon & Sun Pops</title>
    
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --text-dark: #334155;
            --text-muted: #64748b;
            --sun-gold: #f59e0b;
            --nebula-purple: #4f46e5;
            --melon-border: #ffcda3; 
            
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-shadow: 0 8px 30px rgba(0, 0, 0, 0.03);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Outfit', sans-serif; }
        
        /* FIX DEFINITIVO PARA SAFARI / iOS */
        html {
            height: -webkit-fill-available;
        }

        body { 
            background: linear-gradient(-45deg, #fdfbfb, #fdf6f5, #f4f0ea, #fdfbfb);
            background-size: 400% 400%;
            animation: dreamyGalaxy 15s ease infinite;
            color: var(--text-dark);
            
            /* Altura estricta para celulares modernos */
            min-height: 100vh;
            min-height: -webkit-fill-available;
            height: 100dvh; 
            
            padding: 20px; 
            gap: 20px; 
            display: flex;
            flex-direction: column;
            overflow: hidden; /* Nada sale de la pantalla principal */
        }

        @keyframes dreamyGalaxy {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 35px;
            border: 2px solid var(--melon-border);
            box-shadow: var(--glass-shadow);
        }

        /* ----- HEADER (FIJO ARRIBA) ----- */
        header { 
            padding: 0.6rem 2rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-shrink: 0; /* Impide que se aplaste */
            z-index: 10;
        }

        .logo-area { display: flex; align-items: center; gap: 1rem; }
        
        .brand-logo {
            height: 45px; 
            width: auto;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
            transition: transform 0.3s ease;
        }
        
        .brand-name {
            font-family: 'Pacifico', cursive; 
            font-size: 1.7rem; 
            color: var(--sun-gold); 
            letter-spacing: 1px;
        }

        .menu-toggle { 
            width: 42px; height: 42px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; cursor: pointer; color: var(--text-dark); 
            border: 2px solid var(--melon-border); 
            background: rgba(255, 255, 255, 0.8);
            border-radius: 50%; 
            transition: 0.3s ease;
        }

        .user-info { 
            display: flex; align-items: center; gap: 12px; 
            background: rgba(255, 255, 255, 0.9);
            padding: 8px 20px; 
            border-radius: 35px; 
            border: 2px solid var(--melon-border);
        }
        .user-info span { font-weight: 700; color: var(--nebula-purple); }
        .fa-circle-user { color: var(--sun-gold); font-size: 1.2rem; }

        /* ----- LAYOUT CENTRAL (EL MOTOR DEL SCROLL) ----- */
        .layout-central {
            display: flex;
            flex: 1; 
            gap: 20px; 
            min-height: 0; /* Vital para que el main sepa dónde detenerse */
            position: relative;
        }

        /* ----- MENÚ LATERAL ----- */
        .side-menu { 
            display: flex;
            flex-direction: column;
            flex-shrink: 0; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            z-index: 1000;
        }

        .side-menu a { 
            display: flex; align-items: center; padding: 1rem 1.5rem; 
            color: var(--text-dark); font-weight: 600; text-decoration: none; 
            font-size: 1.05rem; transition: 0.3s; 
            border-radius: 20px; 
            margin: 5px 15px;
        }
        .side-menu a i { width: 35px; font-size: 1.2rem; color: var(--nebula-purple); transition: 0.3s; }
        .side-menu a:hover { background: rgba(255, 205, 163, 0.3); transform: translateX(5px); color: var(--sun-gold); }
        
        .side-menu .spacer { flex: 1; } 
        .side-menu .btn-salir { margin-top: auto; margin-bottom: 15px; color: #e11d48; background: rgba(225, 29, 72, 0.05); border: 1px solid rgba(225, 29, 72, 0.1); }

        /* ----- CONTENIDO PRINCIPAL (EL ÚNICO QUE HACE SCROLL) ----- */
        main { 
            flex: 1; 
            overflow-y: auto; 
            overflow-x: hidden;
            padding-right: 10px; 
            padding-bottom: 10px;
        }
        
        main::-webkit-scrollbar { width: 8px; }
        main::-webkit-scrollbar-track { background: transparent; }
        main::-webkit-scrollbar-thumb { background: var(--melon-border); border-radius: 10px; }

        /* =========================================
           LÓGICA RESPONSIVA
           ========================================= */
        @media (min-width: 769px) {
            .side-menu { width: 260px; padding: 1.5rem 0; }
            .side-menu.toggled { width: 0; padding: 0; margin: 0; border: none; opacity: 0; overflow: hidden; }
        }

        @media (max-width: 768px) {
            body { padding: 10px; gap: 10px; }
            header { padding: 0.6rem 1rem; }
            .brand-name { display: none; }
            .layout-central { gap: 10px; }
            
            .side-menu {
                position: fixed;
                top: 0;
                left: -320px; 
                height: 100dvh; /* Usa la misma altura dinámica */
                width: 260px;
                padding: 1.5rem 0;
                border-radius: 0 35px 35px 0;
                box-shadow: 10px 0 30px rgba(0,0,0,0.2);
            }
            .side-menu.toggled { left: 0; }
        }
    </style>
</head>
<body>

<header class="glass-panel">
    <div class="logo-area">
        <button class="menu-toggle" id="openMenu">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>
        
        <img src="../img/logo.png" alt="Logo" class="brand-logo">
        <span class="brand-name">Moon & Sun Pops</span>
    </div>
    
    <div class="user-info">
        <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
        <i class="fa-solid fa-circle-user"></i>
    </div>
</header>

<div class="layout-central">
    
    <?php require 'menu_lateral.php'; ?>

    <main>
        <div class="main-content-wrapper">