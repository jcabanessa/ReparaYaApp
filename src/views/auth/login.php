<?php
/**
 * views/auth/login.php
 * Formulario de acceso de usuarios al sistema.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = $_GET['error'] ?? null;
$msg = $_GET['msg'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión - ReparaYaApp</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        <h1>Iniciar Sesión</h1>
        <p class="text-muted">Accede a tu panel en ReparaYaApp</p>

        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Error de acceso:</strong> Credenciales incorrectas o sesión no autorizada.
            </div>
        <?php endif; ?>

        <?php if ($msg === 'sesion_cerrada'): ?>
            <div class="alert-success">
                Has cerrado sesión correctamente.
            </div>
        <?php endif; ?>

        <form action="../../controllers/AuthController.php?action=login" method="POST">
            <label for="email">Correo Electrónico:</label>
            <input type="text" name="email" id="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="btn">Entrar</button>
        </form>

        <br>
        <a href="registro.php">¿No tienes cuenta? Regístrate aquí</a> | 
        <a href="../../index.php">← Volver al inicio</a>
    </div>
</body>
</html>