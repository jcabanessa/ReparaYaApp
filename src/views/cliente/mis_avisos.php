<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Incidencia.php';

checkRole(['particular']);

$cliente_id = $_SESSION['usuario_id'];
$incidenciaModel = new Incidencia($pdo);
$misIncidencias = $incidenciaModel->obtenerPorCliente($cliente_id);

$success = $_GET['success'] ?? null;
$loc = $_GET['loc'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Avisos - ReparaYaApp</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container container-wide">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        <h1>Mis Solicitudes de Asistencia</h1>
        <p>Cliente: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></p>

        <?php if ($success === 'creada'): ?>
            <div class="alert-success">
                ¡Solicitud creada con éxito! Localizador asignado: <strong><?= htmlspecialchars($loc) ?></strong>
            </div>
        <?php endif; ?>

        <div class="mb-20">
            <a href="nueva_solicitud.php" class="btn btn-success">+ Nueva Solicitud</a>
            <a href="../../controllers/AuthController.php?action=logout" class="float-right">Cerrar Sesión</a>
        </div>

        <?php if (empty($misIncidencias)): ?>
            <p>No tienes ninguna solicitud registrada todavía.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Localizador</th>
                        <th>Servicio</th>
                        <th>Urgencia</th>
                        <th>Fecha Cita</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($misIncidencias as $inc): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($inc['localizador']) ?></strong></td>
                            <td><?= htmlspecialchars($inc['nombre_especialidad']) ?></td>
                            <td><?= htmlspecialchars($inc['tipo_urgencia']) ?></td>
                            <td><?= htmlspecialchars($inc['fecha_servicio']) ?></td>
                            <td>
                                <span class="badge"><?= htmlspecialchars($inc['estado']) ?></span>
                            </td>
                            <td class="text-center">
                                <a href="detalle_aviso.php?id=<?= $inc['id'] ?>">Ver Detalle</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>