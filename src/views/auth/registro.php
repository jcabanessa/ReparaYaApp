<?php
/**
 * views/auth/registro.php
 * Formulario de alta para nuevos clientes particulares.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - ReparaYaApp</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        <h1>Registro de Nuevo Cliente</h1>
        <p class="text-muted">Crea tu cuenta para solicitar asistencias técnicas.</p>

        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Error:</strong> No se pudo completar el registro. Comprueba los datos introducidos.
            </div>
        <?php endif; ?>

        <form action="../../controllers/AuthController.php?action=registro" method="POST">
            <label for="nombre">Nombre completo:</label>
            <input type="text" name="nombre" id="nombre" required>

            <label for="email">Correo Electrónico:</label>
            <input type="text" name="email" id="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>

            <label for="telefono">Teléfono de contacto:</label>
            <input type="text" name="telefono" id="telefono">

            <button type="submit" class="btn btn-success">Registrarse</button>
        </form>

        <br>
        <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a> | 
        <a href="../../index.php">← Volver al inicio</a>
    </div>
</body>
</html>