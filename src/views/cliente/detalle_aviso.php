<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Incidencia.php';

checkRole(['particular']);

$incidencia_id = $_GET['id'] ?? null;
$cliente_id = $_SESSION['usuario_id'];

if (!$incidencia_id) {
    header('Location: mis_avisos.php');
    exit();
}

$incidenciaModel = new Incidencia($pdo);
$incidencia = $incidenciaModel->obtenerPorId($incidencia_id);

if (!$incidencia || $incidencia['cliente_id'] != $cliente_id) {
    header('Location: mis_avisos.php?error=no_autorizado');
    exit();
}

$historial = $incidenciaModel->obtenerHistorial($incidencia_id);

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;

$puedeCancelar = false;
if (in_array($incidencia['estado'], ['Pendiente', 'Asignada'])) {
    $fechaServicio = new DateTime($incidencia['fecha_servicio']);
    $limiteCancelacion = new DateTime('+48 hours');
    if ($fechaServicio >= $limiteCancelacion) {
        $puedeCancelar = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Incidencia - <?= htmlspecialchars($incidencia['localizador']) ?></title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container container-medium">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        <h1>Detalle del Aviso: <?= htmlspecialchars($incidencia['localizador']) ?></h1>

        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Error:</strong> <?= htmlspecialchars(urldecode($error)) ?>
            </div>
        <?php endif; ?>

        <?php if ($success === 'cancelada'): ?>
            <div class="alert-success">
                La incidencia ha sido cancelada correctamente.
            </div>
        <?php endif; ?>

        <div class="details-box">
            <p><strong>Tipo de Servicio:</strong> <?= htmlspecialchars($incidencia['nombre_especialidad']) ?></p>
            <p><strong>Tipo de Urgencia:</strong> <?= htmlspecialchars($incidencia['tipo_urgencia']) ?></p>
            <p><strong>Estado Actual:</strong> <span class="badge"><?= htmlspecialchars($incidencia['estado']) ?></span></p>
            <p><strong>Fecha y Hora Cita:</strong> <?= htmlspecialchars($incidencia['fecha_servicio']) ?></p>
            <p><strong>Dirección:</strong> <?= htmlspecialchars($incidencia['direccion']) ?></p>
            <p><strong>Descripción:</strong> <?= htmlspecialchars($incidencia['descripcion']) ?></p>
        </div>

        <?php if ($puedeCancelar): ?>
            <form action="../../controllers/ClientController.php?action=cancelar" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta solicitud?');">
                <input type="hidden" name="incidencia_id" value="<?= $incidencia['id'] ?>">
                <button type="submit" class="btn-danger">Cancelar Solicitud</button>
            </form>
            <p class="text-muted">* Puedes cancelar esta solicitud porque faltan más de 48 horas para la cita.</p>
        <?php elseif (in_array($incidencia['estado'], ['Pendiente', 'Asignada'])): ?>
            <p class="alert-warning"><strong>Nota:</strong> Ya no es posible cancelar esta solicitud directamente ya que faltan menos de 48 horas para la cita.</p>
        <?php endif; ?>

        <h3>Historial de Cambios de Estado</h3>
        <table>
            <thead>
                <tr>
                    <th>Fecha / Hora</th>
                    <th>Estado Anterior</th>
                    <th>Nuevo Estado</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $h): ?>
                    <tr>
                        <td class="font-small"><?= htmlspecialchars($h['created_at']) ?></td>
                        <td class="font-small"><?= htmlspecialchars($h['estado_anterior'] ?? 'Inicio') ?></td>
                        <td class="font-small font-bold"><?= htmlspecialchars($h['nuevo_estado']) ?></td>
                        <td class="font-small"><?= htmlspecialchars($h['observaciones'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br>
        <a href="mis_avisos.php">← Volver a mis avisos</a>
    </div>
</body>
</html>