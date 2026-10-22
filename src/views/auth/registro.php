<?php 
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Crear cuenta <span>nueva</span></h1>
            <p class="hero-subtitle">Crea tu cuenta gratuita para comenzar a solicitar reparaciones del hogar.</p>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <p class="alert-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="dashboard-card" style="max-width: 550px; margin: 0 auto;">
        <form action="" method="POST">
            <input type="hidden" name="accion" value="registro">

            <div class="form-group">
                <label for="nombre" class="form-label">Nombre Completo *</label>
                <input type="text" name="nombre" id="nombre" class="form-control" placeholder="Tu nombre y apellidos" required>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico *</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="tu@email.com" required>
            </div>

            <div class="form-group">
                <label for="telefono" class="form-label">Teléfono de Contacto *</label>
                <input type="text" name="telefono" id="telefono" class="form-control" placeholder="600000000" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña *</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Registrarse</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>