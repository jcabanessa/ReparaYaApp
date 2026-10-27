<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// Validamos que el usuario sea Administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . $base_url . '/views/auth/login.php');
    exit;
}

// Gestionar mensajes de sesión procedentes del controlador
$mensaje = $_SESSION['mensaje'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['error']);

// Obtener datos para poblar las tablas y selectores de la vista
$tecnicos = $pdo->query("SELECT * FROM usuarios WHERE rol = 'tecnico' ORDER BY nombre ASC")->fetchAll();
$especialidades = [];
try {
    $especialidades = $pdo->query("SELECT * FROM especialidades ORDER BY nombre_especialidad ASC")->fetchAll();
} catch (PDOException $e) {
    $error = "Error al cargar las especialidades.";
}

require_once __DIR__ . '/../layouts/header.php';
?>
<div class="home-container" style="max-width: 1000px;">
    <h1 class="hero-title">Maestro de <span>Técnicos y Servicios</span></h1>
    <p class="hero-subtitle">Panel de gestión para la plantilla de profesionales y los tipos de servicio.</p>

    <?php if (!empty($mensaje)): ?>
        <p style="color: #10b981; font-weight: 600; margin-bottom: 20px;"><?php echo $mensaje; ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color: #ef4444; font-weight: 600; margin-bottom: 20px;"><?php echo $error; ?></p>
    <?php endif; ?>

    <div class="dashboard-grid">
        
        <!-- ============================================== -->
        <!-- PANEL DE TÉCNICOS                              -->
        <!-- ============================================== -->
        <div class="dashboard-card">
            <h3>Gestión de Técnicos</h3>
            
           <form action="../../controllers/AdminController.php" method="POST" style="margin-top: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #2a2a2a;">
                <input type="hidden" name="accion" value="crear_tecnico">
                <div class="form-group">
                    <input type="text" name="nombre" placeholder="Nombre completo" required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <input type="email" name="email" placeholder="Correo electrónico" required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Contraseña de acceso" required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                
                <!-- NUEVO: Selector de especialidad al crear técnico -->
                <div class="form-group">
                    <select name="especialidad_id" required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                        <option value="">Seleccionar Especialidad...</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?php echo $esp['id']; ?>"><?php echo htmlspecialchars($esp['nombre_especialidad']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" style="background: #ff6600; color: #fff; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; width: 100%;">+ Dar de Alta Técnico</button>
            </form>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #2a2a2a; color: #ff6600; text-align: left;">
                        <th style="padding: 8px;">Nombre</th>
                        <th style="padding: 8px;">Email</th>
                        <th style="padding: 8px; text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tecnicos)): ?>
                        <tr><td colspan="3" style="padding: 10px; color: #9ca3af;">No hay técnicos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tecnicos as $tec): ?>
                            <tr style="border-bottom: 1px solid #1a1a1a;">
                                <td style="padding: 8px; color: #fff;"><?php echo htmlspecialchars($tec['nombre']); ?></td>
                                <td style="padding: 8px; color: #9ca3af;"><?php echo htmlspecialchars($tec['email']); ?></td>
                                <td style="padding: 8px; text-align: center;">
                                    <form action="../../controllers/AdminController.php" method="POST" onsubmit="return confirm('¿Estás seguro de dar de baja a este técnico?');">
                                        <input type="hidden" name="accion" value="eliminar_tecnico">
                                        <input type="hidden" name="tecnico_id" value="<?php echo $tec['id']; ?>">
                                        <button type="submit" style="background: #ef4444; color: #fff; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer;">Baja</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ============================================== -->
        <!-- PANEL DE ESPECIALIDADES (SERVICIOS)            -->
        <!-- ============================================== -->
        <div class="dashboard-card">
            <h3>Tipos de Servicio y Precios Base</h3>
            
            <form action="../../controllers/AdminController.php" method="POST" style="margin-top: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #2a2a2a;">
                <input type="hidden" name="accion" value="crear_especialidad">
                <div class="form-group">
                    <input type="text" name="nombre_especialidad" placeholder="Ej: Fontanería, Electricidad..." required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                <div class="form-group">
                    <input type="number" step="0.01" name="precio_base" placeholder="Precio Base (€) Ej: 50.00" required style="width: 100%; padding: 8px; margin-bottom: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                </div>
                <button type="submit" style="background: #ff6600; color: #fff; border: none; padding: 8px 15px; border-radius: 4px; cursor: pointer; width: 100%;">+ Añadir Servicio</button>
            </form>

            <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 1px solid #2a2a2a; color: #ff6600; text-align: left;">
                        <th style="padding: 8px;">Especialidad</th>
                        <th style="padding: 8px; text-align: right;">Precio Base</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($especialidades)): ?>
                        <tr><td colspan="2" style="padding: 10px; color: #9ca3af;">No hay servicios configurados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($especialidades as $esp): ?>
                            <tr style="border-bottom: 1px solid #1a1a1a;">
                                <td style="padding: 8px; color: #fff;"><?php echo htmlspecialchars($esp['nombre_especialidad']); ?></td>
                                <td style="padding: 8px; color: #10b981; font-weight: bold; text-align: right;">
                                    <?php echo number_format($esp['precio_base'], 2); ?> €
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>