<?php
/**
 * views/admin/gestion_avisos.php
 * Panel general del administrador para filtrar incidencias y asignar técnicos.
 */
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Incidencia.php';

// Proteger vista para administradores
checkRole(['admin']);

$incidenciaModel = new Incidencia($pdo);

// Filtro por estado si se solicita
$estadoFiltro = $_GET['estado'] ?? null;
$incidencias = $incidenciaModel->obtenerTodas($estadoFiltro);

// Obtener lista de técnicos disponibles para el selector de asignación
$stmtTecnicos = $pdo->query("SELECT t.id, u.nombre, e.nombre_especialidad FROM tecnicos t JOIN usuarios u ON t.usuario_id = u.id JOIN especialidades e ON t.especialidad_id = e.id");
$tecnicos = $stmtTecnicos->fetchAll();

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Incidencias - Panel Admin</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container container-wide">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        
        <h1>Panel de Administración - Gestión de Avisos</h1>
        <p>Administrador: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></p>

        <?php if ($error): ?>
            <div class="alert-error">
                <strong>Error:</strong> <?= htmlspecialchars(urldecode($error)) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success">
                Operación realizada con éxito.
            </div>
        <?php endif; ?>

        <div class="mb-20">
            <a href="../../controllers/AuthController.php?action=logout" class="float-right">Cerrar Sesión</a>
        </div>

        <!-- Filtros por Estado -->
        <div class="details-box">
            <strong>Filtrar por Estado:</strong> 
            <a href="gestion_avisos.php">Todas</a> | 
            <a href="gestion_avisos.php?estado=Pendiente">Pendiente</a> | 
            <a href="gestion_avisos.php?estado=Asignada">Asignada</a> | 
            <a href="gestion_avisos.php?estado=En curso">En curso</a> | 
            <a href="gestion_avisos.php?estado=Finalizada">Finalizada</a> | 
            <a href="gestion_avisos.php?estado=Cancelada">Cancelada</a>
        </div>

        <?php if (empty($incidencias)): ?>
            <p>No se encontraron incidencias con el filtro seleccionado.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Localizador</th>
                        <th>Cliente</th>
                        <th>Servicio / Urgencia</th>
                        <th>Fecha Cita</th>
                        <th>Estado</th>
                        <th>Técnico Asignado</th>
                        <th class="text-center">Acciones / Gestión</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($incidencias as $inc): ?>
                        <!-- Distintivo de Urgencia -->
                        <tr>
                            <td><strong><?= htmlspecialchars($inc['localizador']) ?></strong></td>
                            <td><?= htmlspecialchars($inc['cliente_nombre']) ?></td>
                            <td><?= htmlspecialchars($inc['nombre_especialidad']) ?> (<?php if ($inc['tipo_urgencia'] === 'Urgente'): ?>
                        <span class="badge-urgente">Urgente</span>
                    <?php else: ?>
                        <span class="badge-estandar">Estándar</span>
                    <?php endif; ?>)</td>
                            <td><?= htmlspecialchars($inc['fecha_servicio']) ?></td>
                            <td><span class="badge"><?= htmlspecialchars($inc['estado']) ?></span></td>
                            <td><?= htmlspecialchars($inc['tecnico_nombre'] ?? 'Sin asignar') ?></td>
                            <td class="text-center">
                                <!-- Asignar Técnico si está Pendiente o Asignada -->
                                <?php if (in_array($inc['estado'], ['Pendiente', 'Asignada'])): ?>
                                    <form action="../../controllers/AdminController.php?action=asignar" method="POST" style="margin-bottom: 5px;">
                                        <input type="hidden" name="incidencia_id" value="<?= $inc['id'] ?>">
                                        <select name="tecnico_id" required class="font-small" style="padding: 4px; width: auto;">
                                            <option value="">-- Seleccionar Técnico --</option>
                                            <?php foreach ($tecnicos as $tec): ?>
                                                <option value="<?= $tec['id'] ?>">
                                                    <?= htmlspecialchars($tec['nombre']) ?> (<?= htmlspecialchars($tec['nombre_especialidad']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-success" style="padding: 4px 8px; font-size: 12px; margin-top: 0;">Asignar</button>
                                    </form>

                                    <form action="../../controllers/AdminController.php?action=cancelar" method="POST" onsubmit="return confirm('¿Seguro que deseas cancelar esta incidencia?');">
                                        <input type="hidden" name="incidencia_id" value="<?= $inc['id'] ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">Cancelar</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">Cerrada / No modificable</span>
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