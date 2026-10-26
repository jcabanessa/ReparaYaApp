<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Asegurarnos de que $base_url existe en este archivo
if (!isset($base_url)) {
    $base_url = (strpos($_SERVER['HTTP_HOST'] ?? '', 'uoc.edu') !== false) ? '/~uocx9' : '';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReparaYaApp</title>
<link rel="stylesheet" href="<?php echo $base_url; ?><?php echo (file_exists($_SERVER['DOCUMENT_ROOT'] . '/src/css/style.css')) ? '/src/css/style.css' : '/css/style.css'; ?>?v=<?php echo time(); ?>">
</head>
<body>
<nav class="navbar">
    <div class="logo">
        <a href="<?php echo $base_url; ?>/">ReparaYaApp</a>
    </div>
    <ul class="nav-list">
        <li><a href="<?php echo $base_url; ?>/" class="nav-link">Inicio</a></li>

        <?php if (isset($_SESSION['usuario_id'])): ?>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <li><a href="<?php echo $base_url; ?>/views/admin/panel.php" class="nav-link">Panel Admin</a></li>
                <li><a href="<?php echo $base_url; ?>/views/admin/nueva_incidencia.php" class="btn-orange">+ Crear Aviso</a></li>
                <li><a href="<?php echo $base_url; ?>/views/admin/calendario.php" class="nav-link">Calendario</a></li>
                <li><a href="<?php echo $base_url; ?>/views/admin/servicios.php" class="nav-link">Servicios & Técnicos</a></li>
            <?php elseif ($_SESSION['rol'] === 'tecnico'): ?>
                <li><a href="<?php echo $base_url; ?>/views/tecnico/agenda.php" class="nav-link">Mi Agenda</a></li>
            <?php elseif ($_SESSION['rol'] === 'particular'): ?>
                <li><a href="<?php echo $base_url; ?>/views/incidencias/mis_avisos.php" class="nav-link">Mis Avisos</a></li>
                <li><a href="<?php echo $base_url; ?>/views/incidencias/nueva.php" class="btn-orange">+ Nueva Solicitud</a></li>
            <?php endif; ?>

            <li><a href="<?php echo $base_url; ?>/views/perfil/index.php" class="nav-link">Mi Perfil</a></li>
            <li><a href="<?php echo $base_url; ?>/views/auth/logout.php" class="btn-logout">Cerrar Sesión</a></li>
        <?php else: ?>
            <li><a href="<?php echo $base_url; ?>/views/auth/login.php" class="nav-link">Iniciar Sesión</a></li>
            <li><a href="<?php echo $base_url; ?>/views/auth/registro.php" class="btn-orange">Registrarse</a></li>
        <?php endif; ?>
    </ul>
</nav>