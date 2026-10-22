<?php
/**
 * views/perfil.php
 * Vista universal de modificación de datos personales y credenciales.
 */
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Usuario.php';

checkAuth();

$usuario_id = $_SESSION['usuario_id'];

// Obtener los datos más recientes del usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute([':id' => $usuario_id]);
$usuario = $stmt->fetch();

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;

// Determinar la ruta de retorno según el rol del usuario
$rol = $usuario['rol'];
$enlaceVolver = 'cliente/mis_avisos.php';
if ($rol === 'admin') {
    $enlaceVolver = 'admin/gestion_avisos.php';
} elseif ($rol === 'tecnico') {
    $enlaceVolver = 'tecnico/agenda.php';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Perfil - ReparaYaApp</title>
    <link rel="stylesheet" href="../public/css/style.css">
</head>
<body>
    <div class="container container-medium">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        <h1>Modificar Datos de Perfil</h1>
        <p>Rol en el sistema: <span class="badge"><?= htmlspecialchars($rol) ?></span></p>

        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Error:</strong> <?= htmlspecialchars(urldecode($error)) ?>
            </div>
        <?php endif; ?>

        <?php if ($success === 'actualizado'): ?>
            <div class="alert-success">
                Tus datos de perfil se han actualizado correctamente.
            </div>
        <?php endif; ?>

        <form action="../controllers/PerfilController.php?action=actualizar" method="POST">
            <label for="nombre">Nombre de usuario:</label>
            <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>

            <label for="email">Correo Electrónico:</label>
            <input type="text" name="email" id="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

            <label for="password">Nueva Contraseña (dejar en blanco para mantener la actual):</label>
            <input type="password" name="password" id="password" placeholder="Introduce nueva contraseña si deseas cambiarla">

            <button type="submit" class="btn">Guardar Cambios</button>
        </form>

        <br>
        <a href="<?= $enlaceVolver ?>">← Volver a mi panel principal</a>
    </div>
</body>
</html>