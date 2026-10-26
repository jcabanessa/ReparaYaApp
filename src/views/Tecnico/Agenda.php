<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// Validar que el usuario sea Técnico o Administrador
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['rol'], ['tecnico', 'admin'])) {
    header('Location: ../auth/login.php');
    exit;
}

$tecnico_id = $_SESSION['usuario_id'];
$mensaje = '';
$error = '';

// Procesar transiciones válidas de estado (Asignada -> En curso -> Finalizada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
    $incidencia_id = $_POST['incidencia_id'];
    $nuevo_estado = $_POST['nuevo_estado'];

    try {
        $stmtCheck = $pdo->prepare("SELECT estado FROM incidencias WHERE id = :id AND tecnico_id = :tecnico_id");
        $stmtCheck->execute([':id' => $incidencia_id, ':tecnico_id' => $tecnico_id]);
        $incidencia = $stmtCheck->fetch();

        if ($incidencia) {
            $estadoActual = $incidencia['estado'];
            $transicionValida = false;

            if ($estadoActual === 'Asignada' && $nuevo_estado === 'En curso') {
                $transicionValida = true;
            } elseif ($estadoActual === 'En curso' && $nuevo_estado === 'Finalizada') {
                $transicionValida = true;
            }

            if ($transicionValida) {
                $stmtUpdate = $pdo->prepare("UPDATE incidencias SET estado = :nuevo_estado WHERE id = :id");
                $stmtUpdate->execute([':nuevo_estado' => $nuevo_estado, ':id' => $incidencia_id]);
                $mensaje = "Estado de la incidencia actualizado a '{$nuevo_estado}'.";
            } else {
                $error = "Transición no permitida: Solo se permite pasar de 'Asignada' ➔ 'En curso' ➔ 'Finalizada'.";
            }
        } else {
            $error = "Incidencia no encontrada o no asignada a tu usuario.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Consultar incidencias asignadas a este técnico
$stmtIncidencias = $pdo->prepare("SELECT i.*, u.nombre AS cliente_nombre FROM incidencias i LEFT JOIN usuarios u ON i.cliente_id = u.id WHERE i.tecnico_id = :tecnico_id ORDER BY i.fecha_servicio ASC");
$stmtIncidencias->execute([':tecnico_id' => $tecnico_id]);
$misIncidencias = $stmtIncidencias->fetchAll();

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="home-container">
    <h1 class="hero-title">Mi Agenda de <span>Intervenciones</span></h1>
    <p class="hero-subtitle">Consulta tus avisos asignados y gestiona los cambios de estado en tiempo real.</p>

    <?php if (!empty($mensaje)): ?>
        <p style="color: #10b981; font-weight: 600; margin-bottom: 20px;"><?php echo $mensaje; ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color: #ef4444; font-weight: 600; margin-bottom: 20px;"><?php echo $error; ?></p>
    <?php endif; ?>

    <div class="dashboard-card">
        <h3>Avisos Asignados</h3>
        
        <?php if (empty($misIncidencias)): ?>
            <p style="color: #9ca3af; padding: 15px 0;">No tienes ninguna intervención asignada actualmente.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem;">
                    <thead>
                        <tr style="border-bottom: 1px solid #2a2a2a; color: #ff6600; text-align: left;">
                            <th style="padding: 10px;">Localizador</th>
                            <th style="padding: 10px;">Cliente / Dirección</th>
                            <th style="padding: 10px;">Descripción</th>
                            <th style="padding: 10px;">Fecha Cita</th>
                            <th style="padding: 10px;">Estado Actual</th>
                            <th style="padding: 10px; text-align: center;">Acciones de Técnico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($misIncidencias as $inc): ?>
                            <tr style="border-bottom: 1px solid #1a1a1a;">
                                <td style="padding: 10px; color: #fff; font-weight: bold;"><?php echo htmlspecialchars($inc['localizador']); ?></td>
                                <td style="padding: 10px;">
                                    <strong style="color: #fff; display: block;"><?php echo htmlspecialchars($inc['cliente_nombre'] ?? 'Cliente'); ?></strong>
                                    <span style="color: #9ca3af; font-size: 0.85rem;"><?php echo htmlspecialchars($inc['direccion']); ?></span>
                                </td>
                                <td style="padding: 10px; color: #d1d5db;"><?php echo htmlspecialchars($inc['descripcion']); ?></td>
                                <td style="padding: 10px; color: #d1d5db;"><?php echo date('d/m/Y H:i', strtotime($inc['fecha_servicio'])); ?></td>
                                <td style="padding: 10px;">
                                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; background: 
                                        <?php 
                                            echo match($inc['estado']) {
                                                'Asignada' => '#3182ce',
                                                'En curso' => '#d69e2e',
                                                'Finalizada' => '#38a169',
                                                default => '#4a5568'
                                            };
                                        ?>; color: white;">
                                        [<?php echo htmlspecialchars($inc['estado']); ?>]
                                    </span>
                                </td>
                                <td style="padding: 10px; text-align: center;">
                                    <form action="" method="POST">
                                        <input type="hidden" name="accion" value="cambiar_estado">
                                        <input type="hidden" name="incidencia_id" value="<?php echo $inc['id']; ?>">
                                        
                                        <?php if ($inc['estado'] === 'Asignada'): ?>
                                            <input type="hidden" name="nuevo_estado" value="En curso">
                                            <button type="submit" style="background: #d69e2e; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85rem;">Iniciar (En curso)</button>
                                        <?php elseif ($inc['estado'] === 'En curso'): ?>
                                            <input type="hidden" name="nuevo_estado" value="Finalizada">
                                            <button type="submit" style="background: #38a169; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 0.85rem;">Finalizar Intervención</button>
                                        <?php else: ?>
                                            <span style="color: #6b7280; font-size: 0.85rem;">Completada</span>
                                        <?php endif; ?>
                                    </form>
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