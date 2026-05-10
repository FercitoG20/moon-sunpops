<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard/dashboard.php");
    exit;
}
require 'conexion.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario']);
    $password = $_POST['password'];
    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1");
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $password === $user['password']) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario'] = $user['usuario'];
        $_SESSION['rol'] = $user['rol'];
        header("Location: dashboard/dashboard.php");
        exit;
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso | Moon & Sun Pops</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="bg-shape sun-shape"></div>
    <div class="bg-shape moon-shape"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <img src="img/logo.png" alt="Moon & Sun Pops Logo" class="brand-logo">
                <h2>Bienvenido de vuelta</h2>
                <p>Ingresa tus credenciales para continuar</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="error-alert">
                    <i class="fa-solid fa-circle-exclamation"></i> 
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form id="loginForm" action="login.php" method="POST">
                <div class="input-group">
                    <i class="fa-solid fa-user icon"></i>
                    <input type="text" name="usuario" placeholder="Usuario" required autocomplete="off">
                </div>
                
                <div class="input-group">
                    <i class="fa-solid fa-lock icon"></i>
                    <input type="password" id="password" name="password" placeholder="Contraseña" required>
                    <i class="fa-solid fa-eye-slash toggle-password" id="togglePassword"></i>
                </div>

                <button type="submit" class="btn-login" id="btnSubmit">
                    <span>Acceder al Dashboard</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <script src="login.js"></script>
</body>
</html>