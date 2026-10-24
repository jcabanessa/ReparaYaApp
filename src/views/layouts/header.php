<?php
/**
 * views/layouts/header.php
 * Cabecera y menú de navegación dinámico según el rol.
 */
$nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
$rolUsuario    = $_SESSION['rol'] ?? 'particular';
?>
<header class="site-header">
    <div class="site-header-content">
        <div class="site-logo">
            ReparaYaApp <span class="badge badge-dark"><?= ucfirst($rolUsuario) ?></span>
        </div>
        
        <nav class="site-nav">
            <a href="/index.php">Inicio</a>
            
            <?php if ($rolUsuario === 'admin'): ?>
                <a href="/views/admin/gestion_avisos.php">Gestión Avisos</a>
                <a href="/views/admin/crear_aviso.php">+ Nuevo Aviso Admin</a>
                <a href="/views/admin/calendario.php">Calendario</a>
            <?php elseif ($rolUsuario === 'tecnico'): ?>
                <a href="/views/tecnico/agenda.php">Mi Agenda</a>
            <?php elseif ($rolUsuario === 'particular'): ?>
                <a href="/views/cliente/mis_avisos.php">Mis Avisos</a>
                <a href="/views/cliente/nueva_solicitud.php">+ Nueva Solicitud</a>
            <?php endif; ?>

            <a href="/views/perfil.php" class="nav-profile">Mi Perfil (<?= htmlspecialchars($nombreUsuario) ?>)</a>
            <a href="/controllers/AuthController.php?action=logout" class="nav-logout">Cerrar Sesión</a>
        </nav>
    </div>
</header>