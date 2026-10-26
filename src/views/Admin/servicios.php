<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// Validamos que el usuario sea el Administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$mensaje = '';
$error = '';

// Procesar los formularios (Alta Técnico, Baja Técnico, Nueva Especialidad)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    
    // ACCIÓN: ALTA DE TÉCNICO
    if ($_POST['accion'] === 'crear_tecnico') {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $telefono = trim($_POST['telefono'] ?? '');
        
        try {
            // Encriptamos la contraseña como en el registro normal
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, telefono) VALUES (:nombre, :email, :password, 'tecnico', :telefono)");
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':password' => $passwordHash,
                ':telefono' => $telefono
            ]);
            $mensaje = "Técnico registrado correctamente en el sistema.";
        } catch (PDOException $e) {
            $error = "Error al crear el técnico. Es posible que el email ya exista.";
        }
    }
    
    // ACCIÓN: BAJA DE TÉCNICO
    elseif ($_POST['accion'] === 'eliminar_tecnico') {
        $tecnico_id = $_POST['tecnico_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND rol = 'tecnico'");
            $stmt->execute([':id' => $tecnico_id]);
            $mensaje = "Técnico eliminado de la plantilla.";
        } catch (PDOException $e) {
            $error = "No se puede eliminar el técnico (posiblemente tenga incidencias asignadas en el historial).";
        }
    }

    // ACCIÓN: NUEVA ESPECIALIDAD (SERVICIO)
    elseif ($_POST['accion'] === 'crear_especialidad') {
        $nombre_especialidad = trim($_POST['nombre_especialidad']);
        $precio_base = floatval($_POST['precio_base']);

        try {
            // Adaptado al nombre de tu columna: nombre_especialidad
            $stmt = $pdo->prepare("INSERT INTO especialidades (nombre_especialidad, precio_base) VALUES (:nombre_especialidad, :precio_base)");
            $stmt->execute([':nombre_especialidad' => $nombre_especialidad, ':precio_base' => $precio_base]);
            $mensaje = "Especialidad creada correctamente.";
        } catch (PDOException $e) {
            $error = "Error al registrar la especialidad.";
        }
    }
}

// Obtener datos para las tablas
$tecnicos = $pdo->query("SELECT * FROM usuarios WHERE rol = 'tecnico' ORDER BY nombre ASC")->fetchAll();
$especialidades = [];
try {
    // Adaptado al nombre de tu columna para el ORDER BY
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
            
            <form action="" method="POST" style="margin-top: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #2a2a2a;">
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
                                    <form action="" method="POST" onsubmit="return confirm('¿Estás seguro de dar de baja a este técnico?');">
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
            
            <form action="" method="POST" style="margin-top: 15px; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #2a2a2a;">
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
                                <!-- Impresión de la variable usando nombre_especialidad -->
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