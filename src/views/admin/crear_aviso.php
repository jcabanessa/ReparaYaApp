<?php
/**
 * views/admin/crear_aviso.php
 * Formulario para que el Administrador cree una incidencia manualmente.
 */
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';

checkRole(['admin']);

// Obtener la lista de clientes particulares
$stmtClientes = $pdo->query("SELECT id, nombre, email FROM usuarios WHERE rol = 'particular' ORDER BY nombre ASC");
$clientes = $stmtClientes->fetchAll();

// Obtener especialidades
$stmtEspecialidades = $pdo->query("SELECT * FROM especialidades ORDER BY nombre_especialidad ASC");
$especialidades = $stmtEspecialidades->fetchAll();

$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Aviso Manual - Administrador</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container container-medium">
        <?php include __DIR__ . '/../layouts/header.php'; ?>

        <h1>Alta Manual de Incidencia</h1>
        <p class="text-muted">Creación directa de aviso desde el Panel de Administración</p>

        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Error:</strong> <?= htmlspecialchars(urldecode($error)) ?>
            </div>
        <?php endif; ?>

        <form action="../../controllers/AdminController.php?action=crear_manual" method="POST">
            <label for="cliente_id">Seleccionar Cliente:</label>
            <select name="cliente_id" id="cliente_id" required>
                <option value="">-- Seleccionar Cliente --</option>
                <?php foreach ($clientes as $cli): ?>
                    <option value="<?= $cli['id'] ?>">
                        <?= htmlspecialchars($cli['nombre']) ?> (<?= htmlspecialchars($cli['email']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="especialidad_id">Especialidad / Servicio:</label>
            <select name="especialidad_id" id="especialidad_id" required>
                <option value="">-- Seleccionar Especialidad --</option>
                <?php foreach ($especialidades as $esp): ?>
                    <option value="<?= $esp['id'] ?>">
                        <?= htmlspecialchars($esp['nombre_especialidad']) ?> (<?= $esp['precio_base'] ?>€)
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="tipo_urgencia">Tipo de Urgencia:</label>
            <select name="tipo_urgencia" id="tipo_urgencia" required>
                <option value="Estándar">Estándar (Requiere 48h de antelación)</option>
                <option value="Urgente">Urgente</option>
            </select>

            <label for="fecha_servicio">Fecha y Hora de Intervención:</label>
            <input type="datetime-local" name="fecha_servicio" id="fecha_servicio" required>

            <label for="direccion">Dirección del Servicio:</label>
            <input type="text" name="direccion" id="direccion" required>

            <label for="descripcion">Descripción de la Avería:</label>
            <textarea name="descripcion" id="descripcion" rows="4" required></textarea>

            <button type="submit" class="btn btn-success">Registrar Aviso</button>
        </form>

        <br>
        <a href="gestion_avisos.php">← Volver a Gestión de Avisos</a>
    </div>
</body>
</html>