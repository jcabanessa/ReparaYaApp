<?php 
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../layouts/header.php'; 
?>

<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Iniciar <span>Sesión</span></h1>
            <p class="hero-subtitle">Accede a tu cuenta para gestionar y consultar tus avisos.</p>
        </div>
    </div>

    <!-- Alerta de Error en Login -->
    <?php if (!empty($_SESSION['error_login'])): ?>
        <div class="alert-error">
            <?php 
                echo htmlspecialchars($_SESSION['error_login']); 
                unset($_SESSION['error_login']); 
            ?>
        </div>
    <?php endif; ?>

    <!-- Alerta de Éxito tras Registro -->
    <?php if (!empty($_SESSION['exito_login'])): ?>
        <div class="alert-success" style="color: #22c55e; font-weight: 600; margin-bottom: 25px; background: #052e16; padding: 14px 18px; border-radius: 8px; border: 1px solid #22c55e; text-align: center;">
            <?php 
                echo htmlspecialchars($_SESSION['exito_login']); 
                unset($_SESSION['exito_login']); 
            ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-card" style="max-width: 500px; margin: 0 auto;">
        <form action="" method="POST">
            <input type="hidden" name="accion" value="login">

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico *</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="tu@email.com" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña *</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">Entrar</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>