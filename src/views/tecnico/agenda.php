<?php
/**
 * views/tecnico/agenda.php
 * Agenda del técnico para consultar incidencias asignadas y actualizar sus estados.
 */
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Incidencia.php';

// Proteger vista para técnicos
checkRole(['tecnico']);

$usuario_id = $_SESSION['usuario_id'];

// Obtener el ID de la tabla 'tecnicos' asociado al usuario logueado
$stmt = $pdo->prepare("SELECT id FROM tecnicos WHERE usuario_id = :usuario_id");
$stmt->execute([':usuario_id' => $usuario_id]);
$tecnicoReg = $stmt->fetch();
$tecnico_id = $tecnicoReg['id'] ?? 0;

$incidenciaModel = new Incidencia($pdo);
$misIncidencias = $incidenciaModel->obtenerPorTecnico($tecnico_id);

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agenda de Técnico - ReparaYaApp</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container container-wide">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        <h1>Agenda de Incidencias Asignadas</h1>
        <p>Técnico: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></p>

        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Error:</strong> <?= htmlspecialchars(urldecode($error)) ?>
            </div>
        <?php endif; ?>

        <?php if ($success === 'actualizado'): ?>
            <div class="alert-success">
                El estado de la incidencia se ha actualizado correctamente.
            </div>
        <?php endif; ?>

        <div class="mb-20">
            <a href="../../controllers/AuthController.php?action=logout" class="float-right">Cerrar Sesión</a>
        </div>

        <?php if (empty($misIncidencias)): ?>
            <p>No tienes ninguna incidencia asignada en este momento.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Localizador</th>
                        <th>Servicio</th>
                        <th>Cliente / Contacto</th>
                        <th>Dirección</th>
                        <th>Fecha Cita</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones / Cambiar Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($misIncidencias as $inc): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($inc['localizador']) ?></strong></td>
                            <td><?= htmlspecialchars($inc['nombre_especialidad']) ?> (<?= htmlspecialchars($inc['tipo_urgencia']) ?>)</td>
                            <td>
                                <?= htmlspecialchars($inc['cliente_nombre']) ?><br>
                                <span class="text-muted"><?= htmlspecialchars($inc['cliente_telefono'] ?? 'Sin teléfono') ?></span>
                            </td>
                            <td><?= htmlspecialchars($inc['direccion']) ?></td>
                            <td><?= htmlspecialchars($inc['fecha_servicio']) ?></td>
                            <td>
                                <span class="badge"><?= htmlspecialchars($inc['estado']) ?></span>
                            </td>
                            <td class="text-center">
                                <?php if ($inc['estado'] === 'Asignada'): ?>
                                    <form action="../../controllers/TecnicoController.php?action=cambiar_estado" method="POST">
                                        <input type="hidden" name="incidencia_id" value="<?= $inc['id'] ?>">
                                        <input type="hidden" name="nuevo_estado" value="En curso">
                                        <input type="text" name="observaciones" placeholder="Observación opcional" class="font-small mb-20" style="margin-top: 0; padding: 5px;">
                                        <button type="submit" class="btn" style="padding: 6px 12px; font-size: 13px; margin-top: 0;">Marcar En Curso</button>
                                    </form>
                                <?php elseif ($inc['estado'] === 'En curso'): ?>
                                    <form action="../../controllers/TecnicoController.php?action=cambiar_estado" method="POST">
                                        <input type="hidden" name="incidencia_id" value="<?= $incidencia_id ?? $inc['id'] ?>">
                                        <!-- Corrigiendo el id del form -->
                                        <input type="hidden" name="incidencia_id" value="<?= $inc['id'] ?>">
                                        <input type="hidden" name="nuevo_estado" value="Finalizada">
                                        <input type="text" name="observaciones" placeholder="Observación de cierre" class="font-small mb-20" style="margin-top: 0; padding: 5px;">
                                        <button type="submit" class="btn btn-success" style="padding: 6px 12px; font-size: 13px; margin-top: 0;">Marcar Finalizada</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Sin acciones</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>