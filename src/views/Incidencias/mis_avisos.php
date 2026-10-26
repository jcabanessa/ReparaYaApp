<?php 
require_once __DIR__ . '/../../controllers/IncidenciaController.php';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Mis <span>Avisos</span></h1>
            <p class="hero-subtitle">Consulta el estado y seguimiento de tus solicitudes de reparación.</p>
        </div>
        <div>
            <a href="<?php echo $base_url; ?>/views/incidencias/nueva.php" class="btn-orange">+ Nueva Solicitud</a>
        </div>
    </div>

    <!-- Alerta de Éxito al Registrar / Cancelar Incidencia -->
    <?php if (!empty($_SESSION['exito_incidencia'])): ?>
       <div class="alert-success">
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

    <div class="dashboard-card">
        <h3>Historial de Solicitudes</h3>
        
        <?php if (empty($misAvisos)): ?>
            <p class="text-empty">No has registrado ninguna solicitud todavía.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Servicio / Descripción</th>
                            <th>Urgencia</th>
                            <th>Fecha Cita</th>
                            <th>Estado</th>
                            <th class="action-cell">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($misAvisos as $aviso): ?>
                            <tr>
                                <td class="table-code"><?php echo htmlspecialchars($aviso['localizador']); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($aviso['nombre_especialidad'] ?? 'Servicio General'); ?></strong>
                                    <div class="table-subtext"><?php echo htmlspecialchars($aviso['descripcion']); ?></div>
                                </td>
                                <td class="table-urgency <?php echo strtolower($aviso['tipo_urgencia']) === 'urgente' ? 'urgente' : 'estandar'; ?>">
                                    <?php echo htmlspecialchars($aviso['tipo_urgencia']); ?>
                                </td>
                                <td class="table-date"><?php echo date('d/m/Y - H:i', strtotime($aviso['fecha_servicio'])); ?></td>
                                <td>
                                    <?php 
                                        $badgeClass = match($aviso['estado']) {
                                            'Pendiente'  => 'badge-pendiente',
                                            'Asignada'   => 'badge-asignada',
                                            'En curso'   => 'badge-encurso',
                                            'Finalizada' => 'badge-finalizada',
                                            'Cancelada'  => 'badge-cancelada',
                                            default      => ''
                                        };
                                    ?>
                                    <span class="badge-status <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($aviso['estado']); ?>
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <?php if (in_array($aviso['estado'], ['Pendiente', 'Asignada'])): ?>
                                        <form action="" method="POST" onsubmit="return confirm('¿Seguro que deseas cancelar esta solicitud?');">
                                            <input type="hidden" name="accion" value="cancelar_cliente">
                                            <input type="hidden" name="incidencia_id" value="<?php echo $aviso['id']; ?>">
                                            <button type="submit" class="btn-danger">Cancelar</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="table-subtext">-</span>
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