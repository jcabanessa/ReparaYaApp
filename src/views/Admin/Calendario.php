<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// Validar que el usuario sea Administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . $base_url . '/views/auth/login.php');
    exit;
}

// Filtrar por mes, semana y dia.
$filtro = $_GET['filtro'] ?? 'todos';
$whereClause = "";

if ($filtro === 'mensual') {
    $whereClause = "WHERE MONTH(i.fecha_servicio) = MONTH(CURRENT_DATE()) AND YEAR(i.fecha_servicio) = YEAR(CURRENT_DATE())";
} elseif ($filtro === 'semanal') {
    $whereClause = "WHERE YEARWEEK(i.fecha_servicio, 1) = YEARWEEK(CURRENT_DATE(), 1)";
} elseif ($filtro === 'diaria') {
    $whereClause = "WHERE DATE(i.fecha_servicio) = CURRENT_DATE()";
}

// Obtener todas las incidencias programadas
$stmt = $pdo->query("SELECT i.*, u.nombre AS cliente_nombre, t.nombre AS tecnico_nombre 
                     FROM incidencias i 
                     LEFT JOIN usuarios u ON i.cliente_id = u.id 
                     LEFT JOIN usuarios t ON i.tecnico_id = t.id 
                     $whereClause
                     ORDER BY i.fecha_servicio ASC");
$incidencias = $stmt->fetchAll();

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="home-container">
    <h1 class="hero-title">Calendario de <span>Intervenciones</span></h1>
    <p class="hero-subtitle">Vista temporal diferenciada por colores (Urgente vs Estándar) y distintivos explícitos de estado.</p>
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <a href="?filtro=todos" class="filter-link <?php echo $filtro === 'todos' ? 'active' : ''; ?>">Todos</a>
        <a href="?filtro=mensual" class="filter-link <?php echo $filtro === 'mensual' ? 'active' : ''; ?>">Mensual</a>
        <a href="?filtro=semanal" class="filter-link <?php echo $filtro === 'semanal' ? 'active' : ''; ?>">Semanal</a>
        <a href="?filtro=diaria" class="filter-link <?php echo $filtro === 'diaria' ? 'active' : ''; ?>">Diaria</a>
    </div>

    <!-- Leyenda del Calendario -->
    <div style="display: flex; gap: 20px; margin-bottom: 25px; background: #141414; padding: 15px; border-radius: 8px; border: 1px solid #2a2a2a;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 14px; height: 14px; background: #ef4444; border-radius: 3px; display: inline-block;"></span>
            <span style="color: #fff; font-size: 0.9rem;">Servicio Urgente</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 14px; height: 14px; background: #3182ce; border-radius: 3px; display: inline-block;"></span>
            <span style="color: #fff; font-size: 0.9rem;">Servicio Estándar</span>
        </div>
    </div>

    <div class="dashboard-card">
        <h3>Programación de Citas</h3>

        <?php if (empty($incidencias)): ?>
            <p style="color: #9ca3af; padding: 15px 0;">No hay servicios registrados en el calendario.</p>
        <?php else: ?>
            <div style="display: grid; gap: 15px; margin-top: 20px;">
                <?php foreach ($incidencias as $inc): ?>
                    <?php 
                        $esUrgente = strtolower($inc['tipo_urgencia']) === 'urgente';
                        $colorBorde = $esUrgente ? '#ef4444' : '#3182ce';
                    ?>
                    <div style="background: #0e0e0e; border: 1px solid #222; border-left: 5px solid <?php echo $colorBorde; ?>; padding: 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                <strong style="color: #fff; font-size: 1.1rem;"><?php echo htmlspecialchars($inc['localizador']); ?></strong>
                                
                                <!-- Distintivo de tipo (Color) -->
                                <span style="background: <?php echo $colorBorde; ?>; color: #fff; font-size: 0.75rem; padding: 2px 6px; border-radius: 3px; text-transform: uppercase; font-weight: bold;">
                                    <?php echo htmlspecialchars($inc['tipo_urgencia']); ?>
                                </span>

                                <!-- Distintivo explícito de estado (Texto/Badge) -->
                                <span style="background: #2a2a2a; color: #e5e7eb; font-size: 0.75rem; padding: 2px 8px; border-radius: 3px; border: 1px solid #444;">
                                    Estado: <strong><?php echo htmlspecialchars($inc['estado']); ?></strong>
                                </span>
                            </div>

                            <p style="color: #9ca3af; font-size: 0.9rem; margin: 0;">
                                <strong>Cliente:</strong> <?php echo htmlspecialchars($inc['cliente_nombre'] ?? 'N/A'); ?> | 
                                <strong>Dirección:</strong> <?php echo htmlspecialchars($inc['direccion']); ?>
                            </p>
                            <p style="color: #d1d5db; font-size: 0.85rem; margin-top: 4px;">
                                <?php echo htmlspecialchars($inc['descripcion']); ?>
                            </p>
                        </div>

                        <div style="text-align: right;">
                            <span style="color: #ff6600; font-weight: bold; font-size: 1rem; display: block;">
                                📅 <?php echo date('d/m/Y - H:i', strtotime($inc['fecha_servicio'])); ?>
                            </span>
                            <small style="color: #9ca3af; font-size: 0.85rem;">
                                Técnico: <?php echo htmlspecialchars($inc['tecnico_nombre'] ?? 'Sin asignar'); ?>
                            </small>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>