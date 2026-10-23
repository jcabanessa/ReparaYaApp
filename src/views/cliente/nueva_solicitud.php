<?php
/**
 * views/cliente/nueva_solicitud.php
 * Formulario para que el cliente solicite una nueva asistencia técnica.
 */
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';

// Proteger vista para clientes (esto ya inicializa la sesión de forma segura)
checkRole(['particular']);

$stmt = $pdo->query("SELECT * FROM especialidades");
$especialidades = $stmt->fetchAll();

$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Solicitud - ReparaYaApp</title>
    <!-- Enlace a la hoja de estilos externa -->
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        <h1>Crear Nueva Solicitud de Asistencia</h1>
        <p>Cliente: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></p>

        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Error:</strong> <?= htmlspecialchars(urldecode($error)) ?>
            </div>
        <?php endif; ?>

        <form action="../../controllers/ClienteController.php?action=crear" method="POST">
            <label for="especialidad_id">Tipo de Servicio (Especialidad):</label>
            <select name="especialidad_id" id="especialidad_id" required>
                <option value="">-- Seleccione una especialidad --</option>
                <?php foreach ($especialidades as $esp): ?>
                    <option value="<?= $esp['id'] ?>">
                        <?= htmlspecialchars($esp['nombre_especialidad']) ?> (Precio base: <?= $esp['precio_base'] ?>€)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="tipo_urgencia">Tipo de Urgencia:</label>
            <select name="tipo_urgencia" id="tipo_urgencia" required>
                <option value="Estándar">Estándar (Requiere mínimo 48h de antelación)</option>
                <option value="Urgente">Urgente</option>
            </select>

            <label for="fecha_servicio">Fecha y Hora Solicitada:</label>
            <input type="datetime-local" name="fecha_servicio" id="fecha_servicio" required>

            <label for="direccion">Dirección de la intervención:</label>
            <input type="text" name="direccion" id="direccion" required>

            <label for="descripcion">Descripción detallada de la avería:</label>
            <textarea name="descripcion" id="descripcion" rows="4" required></textarea>

            <button type="submit">Enviar Solicitud</button>
        </form>

        <a href="mis_avisos.php">← Volver a mis avisos</a>
    </div>
</body>
</html>