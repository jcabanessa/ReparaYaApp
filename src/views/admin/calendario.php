<?php
/**
 * views/admin/calendario.php
 * Vista de calendario/agenda global para el administrador con distinción por tipo y estado.
 */
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Incidencia.php';

// Proteger vista para administradores
checkRole(['admin']);

$incidenciaModel = new Incidencia($pdo);

// Filtro opcional por vista (mensual, semanal, diaria) o fecha
$vista = $_GET['vista']  ?? 'mensual';
$incidencias = $incidenciaModel->obtenerTodas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calendario de Incidencias - Panel Admin</title>
    <link rel="stylesheet" href="../../public/css/style.css">
</head>
<body>
    <div class="container container-wide">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        <h1>Calendario y Agenda de Intervenciones</h1>
        <p>Administrador: <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></p>

        <div class="mb-20">
            <a href="gestion_avisos.php" class="btn" style="padding: 8px 12px; font-size: 14px; margin-top: 0;">← Volver a Gestión de Avisos</a>
            <a href="../../controllers/AuthController.php?action=logout" class="float-right">Cerrar Sesión</a>
        </div>

        <!-- Filtros de Vista -->
        <div class="calendar-filters">
            <strong>Modo de Vista:</strong> 
            <a href="calendario.php?vista=mensual">Mensual</a> | 
            <a href="calendario.php?vista=semanal">Semanal</a> | 
            <a href="calendario.php?vista=diaria">Diaria</a>
            <span class="text-muted float-right" style="float: right;">Mostrando vista: <strong><?= ucfirst($vista) ?></strong></span>
        </div>

        <?php if (empty($incidencias)): ?>
            <p>No hay intervenciones programadas en el sistema.</p>
        <?php else: ?>
            <ul class="calendar-list">
                <?php foreach ($incidencias as $inc): ?>
                    <?php 
                        $esUrgente = ($inc['tipo_urgencia'] === 'Urgente');
                        $claseUrgencia = $esUrgente ? 'urgente' : '';
                    ?>
                    <li class="calendar-item <?= $claseUrgencia ?>">
                        <div class="calendar-item-info">
                            <div>
                                <strong><?= htmlspecialchars($inc['localizador']) ?></strong> - 
                                <span><?= htmlspecialchars($inc['nombre_especialidad']) ?></span>
                                
                                <!-- Distintivo de Urgencia -->
                                <?php if ($esUrgente): ?>
                                    <span class="badge-urgente">Urgente</span>
                                <?php else: ?>
                                    <span class="badge-estandar">Estándar</span>
                                <?php endif; ?>

                                <!-- Distintivo de Estado -->
                                <span class="badge"><?= htmlspecialchars($inc['estado']) ?></span>
                            </div>
                            <div class="font-small text-muted" style="margin-top: 5px;">
                                📅 <strong>Fecha Cita:</strong> <?= htmlspecialchars($inc['fecha_servicio']) ?> | 
                                📍 <strong>Dirección:</strong> <?= htmlspecialchars($inc['direccion']) ?>
                            </div>
                        </div>
                        <div>
                            <a href="gestion_avisos.php" class="btn" style="padding: 6px 12px; font-size: 13px; margin-top: 0;">Ver Detalle</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>