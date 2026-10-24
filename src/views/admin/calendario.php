<?php
/**
 * views/admin/calendario.php
 * Vista de calendario/agenda global para el administrador con vistas filtradas.
 */
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../models/Incidencia.php';

// Proteger vista para administradores
checkRole(['admin']);

$incidenciaModel = new Incidencia($pdo);

// 1. Capturar y validar el filtro de vista
$vistasPermitidas = ['mensual', 'semanal', 'diaria'];
$vista = $_GET['vista'] ?? 'mensual';
if (!in_array($vista, $vistasPermitidas)) {
    $vista = 'mensual';
}

// 2. Obtener todas las incidencias
$todasIncidencias = $incidenciaModel->obtenerTodas();
$hoy = time();

// 3. Filtrar dinámicamente según la vista seleccionada
$incidencias = array_filter($todasIncidencias, function($inc) use ($vista, $hoy) {
    $fechaServicio = strtotime($inc['fecha_servicio']);
    if (!$fechaServicio) return false;

    if ($vista === 'diaria') {
        return date('Y-m-d', $fechaServicio) === date('Y-m-d', $hoy);
    } elseif ($vista === 'semanal') {
        return date('W', $fechaServicio) === date('W', $hoy) && date('Y', $fechaServicio) === date('Y', $hoy);
    } else {
        // Mensual por defecto
        return date('m', $fechaServicio) === date('m', $hoy) && date('Y', $fechaServicio) === date('Y', $hoy);
    }
});
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
            <a href="gestion_avisos.php" class="btn">← Volver a Gestión de Avisos</a>
        </div>

        <!-- Filtros de Vista Interactivos -->
        <div class="calendar-filters">
            <strong>Modo de Vista:</strong> 
            <a href="calendario.php?vista=mensual" class="calendar-filter-link <?= $vista === 'mensual' ? 'active' : '' ?>">Mensual</a> | 
            <a href="calendario.php?vista=semanal" class="calendar-filter-link <?= $vista === 'semanal' ? 'active' : '' ?>">Semanal</a> | 
            <a href="calendario.php?vista=diaria" class="calendar-filter-link <?= $vista === 'diaria' ? 'active' : '' ?>">Diaria</a>
            
            <span class="calendar-filters-right">
                Mostrando vista: <strong><?= ucfirst($vista) ?></strong> (<?= count($incidencias) ?> citas)
            </span>
        </div>

        <?php if (empty($incidencias)): ?>
            <div class="calendar-empty-state">
                <p>No hay intervenciones programadas para este periodo (<?= ucfirst($vista) ?>).</p>
            </div>
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
                                <span class="calendar-item-title"><?= htmlspecialchars($inc['localizador']) ?></span>
                                <span class="badge-especialidad"><?= htmlspecialchars($inc['nombre_especialidad']) ?></span>
                                
                                <!-- Distintivo de Urgencia -->
                                <?php if ($esUrgente): ?>
                                    <span class="badge-urgente">Urgente</span>
                                <?php else: ?>
                                    <span class="badge-estandar">Estándar</span>
                                <?php endif; ?>

                                <!-- Distintivo de Estado -->
                                <span class="badge-estado-cal">
                                    Estado: <?= htmlspecialchars($inc['estado']) ?>
                                </span>
                            </div>
                            <div class="calendar-meta">
                                📅 <strong>Fecha Cita:</strong> <?= htmlspecialchars($inc['fecha_servicio']) ?> | 
                                📍 <strong>Dirección:</strong> <?= htmlspecialchars($inc['direccion']) ?>
                            </div>
                        </div>
                        <div>
                            <a href="gestion_avisos.php" class="btn-calendar-detail">Ver Detalle</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>