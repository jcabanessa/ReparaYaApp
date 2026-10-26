<?php 
require_once __DIR__ . '/../../controllers/PerfilController.php';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="home-container" style="max-width: 900px;">
    <h1 class="hero-title">Mi <span>Perfil</span></h1>
    <p class="hero-subtitle">Gestiona tu información personal y la seguridad de tu cuenta.</p>

    <?php if (!empty($mensaje)): ?>
        <p style="color: #10b981; font-weight: 600; margin-bottom: 20px;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color: #ef4444; font-weight: 600; margin-bottom: 20px;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Información Personal</h3>
            
            <form action="" method="POST" style="margin-top: 15px;">
                <input type="hidden" name="accion" value="actualizar_datos">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="color: #9ca3af; font-size: 0.85rem; display: block; margin-bottom: 5px;">Rol de Usuario</label>
                    <input type="text" value="<?php echo strtoupper(htmlspecialchars($usuario['rol'] ?? '')); ?>" disabled style="width: 100%; padding: 10px; background: #1a1a1a; border: 1px solid #2a2a2a; border-radius: 4px; color: #ff6600; font-weight: bold;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nombre" style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 5px;">Nombre Completo</label>
                    <input type="text" name="nombre" id="nombre" value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" required style="width: 100%; padding: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; border-radius: 4px; color: #fff;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="email" style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 5px;">Correo Electrónico</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" required style="width: 100%; padding: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; border-radius: 4px; color: #fff;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="telefono" style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 5px;">Teléfono de Contacto</label>
                    <input type="text" name="telefono" id="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" style="width: 100%; padding: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; border-radius: 4px; color: #fff;">
                </div>

                <button type="submit" class="btn-submit" style="width: 100%;">Guardar Cambios</button>
            </form>
        </div>

        <div class="dashboard-card">
            <h3>Cambiar Contraseña</h3>
            
            <form action="" method="POST" style="margin-top: 15px;">
                <input type="hidden" name="accion" value="cambiar_password">

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="password_actual" style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 5px;">Contraseña Actual</label>
                    <input type="password" name="password_actual" id="password_actual" required style="width: 100%; padding: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; border-radius: 4px; color: #fff;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="password_nueva" style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 5px;">Nueva Contraseña</label>
                    <input type="password" name="password_nueva" id="password_nueva" required style="width: 100%; padding: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; border-radius: 4px; color: #fff;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="password_confirmar" style="color: #d1d5db; font-size: 0.85rem; display: block; margin-bottom: 5px;">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmar" id="password_confirmar" required style="width: 100%; padding: 10px; background: #0b0b0b; border: 1px solid #2a2a2a; border-radius: 4px; color: #fff;">
                </div>

                <button type="submit" style="width: 100%; padding: 10px; background: #2a2a2a; border: 1px solid #ff6600; color: #ff6600; font-weight: bold; border-radius: 4px; cursor: pointer;">Actualizar Contraseña</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>