<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// Detección dinámica de $base_url para compatibilidad con UOC y Local
$is_uoc = (strpos($_SERVER['HTTP_HOST'] ?? '', 'uoc.edu') !== false);
$base_url = $is_uoc ? '/~uocx9' : '';

if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . $base_url . '/views/auth/login.php');
    exit;
}

// ==========================================
// 1. PROCESAMIENTO DE ACCIONES (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'asignar_tecnico') {
    $incidencia_id = intval($_POST['incidencia_id'] ?? 0);
    $tecnico_id = intval($_POST['tecnico_id'] ?? 0);

    if ($incidencia_id > 0 && $tecnico_id > 0) {
        try {
            // 1. Obtenemos la fecha exacta y el estado actual de la incidencia que queremos asignar
            $stmtInfo = $pdo->prepare("SELECT fecha_servicio, estado FROM incidencias WHERE id = :id");
            $stmtInfo->execute([':id' => $incidencia_id]);
            $infoIncidencia = $stmtInfo->fetch();
            
            $fecha_servicio_actual = $infoIncidencia['fecha_servicio'];
            $estadoAnterior = $infoIncidencia['estado'] ?: 'Pendiente';

            // 2. COMPROBACIÓN DE SOLAPAMIENTO (Misma fecha y hora)
            $stmtConflicto = $pdo->prepare("
                SELECT id FROM incidencias 
                WHERE tecnico_id = :tecnico_id 
                  AND fecha_servicio = :fecha_servicio 
                  AND estado IN ('Asignada', 'En curso')
                  AND id != :id_actual
            ");
            
            $stmtConflicto->execute([
                ':tecnico_id' => $tecnico_id,
                ':fecha_servicio' => $fecha_servicio_actual,
                ':id_actual' => $incidencia_id
            ]);

            // 3. Decidimos si asignamos o bloqueamos
            if ($stmtConflicto->rowCount() > 0) {
                // Bloqueo: El técnico ya tiene trabajo en ese momento exacto
                $_SESSION['error_incidencia'] = "No se puede asignar: El técnico seleccionado ya tiene otro aviso programado para esa misma fecha y hora.";
            } else {
                // Éxito: El técnico está libre, procedemos a asignar
                $stmt = $pdo->prepare("UPDATE incidencias SET tecnico_id = :tecnico, estado = 'Asignada' WHERE id = :id");
                $stmt->execute([':tecnico' => $tecnico_id, ':id' => $incidencia_id]);

                if (function_exists('registrarHistorial')) {
                    registrarHistorial($pdo, $incidencia_id, $estadoAnterior, 'Asignada', $_SESSION['usuario_id'], 'Técnico asignado por el administrador.');
                }
                $_SESSION['exito_incidencia'] = "Técnico asignado correctamente. Estado actualizado a 'Asignada'.";
            }
        } catch (PDOException $e) {
            $_SESSION['error_incidencia'] = "Error al asignar el técnico: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_incidencia'] = "Selecciona un técnico válido.";
    }
    
    // REDIRECCIÓN LIMPIA (Patrón PRG)
    header('Location: ' . $base_url . '/views/admin/panel.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar_admin') {
    $incidencia_id = intval($_POST['incidencia_id'] ?? 0);
    $stmtEstado = $pdo->prepare("SELECT estado FROM incidencias WHERE id = :id");
    $stmtEstado->execute([':id' => $incidencia_id]);
    $estadoAnterior = $stmtEstado->fetchColumn();

    if (in_array($estadoAnterior, ['Pendiente', 'Asignada'])) {
        $stmt = $pdo->prepare("UPDATE incidencias SET estado = 'Cancelada' WHERE id = :id");
        $stmt->execute([':id' => $incidencia_id]);

        if (function_exists('registrarHistorial')) {
            registrarHistorial($pdo, $incidencia_id, $estadoAnterior, 'Cancelada', $_SESSION['usuario_id'], 'Cancelación manual efectuada por el Administrador.');
        }
        $_SESSION['exito_incidencia'] = "Incidencia cancelada con éxito.";
    } else {
        $_SESSION['error_incidencia'] = "No se puede cancelar en el estado actual.";
    }
    
    // REDIRECCIÓN LIMPIA
    header('Location: ' . $base_url . '/views/admin/panel.php');
    exit;
}

// ==========================================
// 2. CONSULTAS GET PARA MOSTRAR LA TABLA
// ==========================================
$filtroEstado = $_GET['estado'] ?? '';
if (!empty($filtroEstado)) {
    $sql = "SELECT i.*, u.nombre AS nombre_tecnico, e.nombre_especialidad 
            FROM incidencias i 
            LEFT JOIN usuarios u ON i.tecnico_id = u.id 
            LEFT JOIN especialidades e ON i.especialidad_id = e.id 
            WHERE i.estado = :estado 
            ORDER BY i.fecha_servicio DESC";
    $stmtInc = $pdo->prepare($sql);
    $stmtInc->execute([':estado' => $filtroEstado]);
} else {
    $sql = "SELECT i.*, u.nombre AS nombre_tecnico, e.nombre_especialidad 
            FROM incidencias i 
            LEFT JOIN usuarios u ON i.tecnico_id = u.id 
            LEFT JOIN especialidades e ON i.especialidad_id = e.id 
            ORDER BY i.fecha_servicio DESC";
    $stmtInc = $pdo->query($sql);
}
$incidencias = $stmtInc->fetchAll();

$tecnicos = $pdo->query("SELECT * FROM usuarios WHERE rol = 'tecnico' ORDER BY nombre ASC")->fetchAll();

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Panel de <span>Administración</span></h1>
            <p class="hero-subtitle">Gestión centralizada de avisos, filtrado por estados y asignación de técnicos.</p>
        </div>
        <div>
            <a href="<?php echo $base_url; ?>/views/admin/nueva_incidencia.php" class="btn-submit">+ Crear Aviso Manual</a>
        </div>
    </div>

    <!-- Alerta de Éxito al Asignar / Cancelar / Crear Incidencia -->
    <?php if (!empty($_SESSION['exito_incidencia'])): ?>
    <div class="alert-success" style="color: #22c55e; font-weight: 600; margin-bottom: 25px; background: #052e16; padding: 14px 18px; border-radius: 8px; border: 1px solid #22c55e; text-align: center;">
        <?php 
            echo htmlspecialchars($_SESSION['exito_incidencia']); 
            unset($_SESSION['exito_incidencia']); 
        ?>
    </div>
    <?php endif; ?>

    <!-- Alerta de Error -->
    <?php if (!empty($_SESSION['error_incidencia'])): ?>
    <div class="alert-error">
        <?php 
            echo htmlspecialchars($_SESSION['error_incidencia']); 
            unset($_SESSION['error_incidencia']); 
        ?>
    </div>
    <?php endif; ?>

    <div class="filter-bar">
        <span class="filter-label">Filtrar por estado:</span>
        <a href="<?php echo $base_url; ?>/views/admin/panel.php" class="filter-link <?php echo empty($filtroEstado) ? 'active' : ''; ?>">Todos</a>
        <a href="<?php echo $base_url; ?>/views/admin/panel.php?estado=Pendiente" class="filter-link <?php echo $filtroEstado === 'Pendiente' ? 'active' : ''; ?>">Pendientes</a>
        <a href="<?php echo $base_url; ?>/views/admin/panel.php?estado=Asignada" class="filter-link <?php echo $filtroEstado === 'Asignada' ? 'active' : ''; ?>">Asignadas</a>
        <a href="<?php echo $base_url; ?>/views/admin/panel.php?estado=En curso" class="filter-link <?php echo $filtroEstado === 'En curso' ? 'active' : ''; ?>">En curso</a>
        <a href="<?php echo $base_url; ?>/views/admin/panel.php?estado=Finalizada" class="filter-link <?php echo $filtroEstado === 'Finalizada' ? 'active' : ''; ?>">Finalizadas</a>
    </div>

    <div class="dashboard-card">
        <h3>Listado Global de Incidencias</h3>
        <?php if (empty($incidencias)): ?>
            <p class="text-empty">No hay incidencias registradas con este filtro.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Servicio</th>
                            <th>Urgencia</th>
                            <th>Fecha Cita</th>
                            <th>Estado</th>
                            <th>Técnico</th>
                            <th class="action-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($incidencias as $inc): ?>
                            <tr>
                                <td class="table-code"><?php echo htmlspecialchars($inc['localizador']); ?></td>
                                <td><?php echo htmlspecialchars($inc['nombre_especialidad'] ?? 'General'); ?></td>
                                <td class="table-urgency <?php echo strtolower($inc['tipo_urgencia']) === 'urgente' ? 'urgente' : 'estandar'; ?>">
                                    <?php echo htmlspecialchars($inc['tipo_urgencia']); ?>
                                </td>
                                <td class="table-date"><?php echo date('d/m/Y H:i', strtotime($inc['fecha_servicio'])); ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = match($inc['estado']) {
                                            'Pendiente'  => 'badge-pendiente',
                                            'Asignada'   => 'badge-asignada',
                                            'En curso'   => 'badge-encurso',
                                            'Finalizada' => 'badge-finalizada',
                                            'Cancelada'  => 'badge-cancelada',
                                            default      => ''
                                        };
                                    ?>
                                    <span class="badge-status <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($inc['estado']); ?>
                                    </span>
                                </td>
                                <td class="table-subtext">
                                    <?php echo htmlspecialchars($inc['nombre_tecnico'] ?? 'Sin asignar'); ?>
                                </td>
                                <td class="action-cell">
                                    <?php if (in_array($inc['estado'], ['Pendiente', 'Asignada'])): ?>
                                        <div class="action-group">
                                            <form action="" method="POST" class="action-group">
                                                <input type="hidden" name="accion" value="asignar_tecnico">
                                                <input type="hidden" name="incidencia_id" value="<?php echo $inc['id']; ?>">
                                                <select name="tecnico_id" required class="form-control btn-sm">
                                                    <option value="">Técnico...</option>
                                                    <?php foreach ($tecnicos as $tec): ?>
                                                        <option value="<?php echo $tec['id']; ?>" <?php echo $inc['tecnico_id'] == $tec['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($tec['nombre']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn-submit btn-sm">Asignar</button>
                                            </form>
                                            <form action="" method="POST" onsubmit="return confirm('¿Cancelar esta incidencia?');">
                                                <input type="hidden" name="accion" value="cancelar_admin">
                                                <input type="hidden" name="incidencia_id" value="<?php echo $inc['id']; ?>">
                                                <button type="submit" class="btn-danger">Cancelar</button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <span class="table-subtext">No modificable</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>