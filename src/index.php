<?php
/**
 * index.php (Raíz)
 * Sitio web de contenido estático para la presentación de ReparaYaApp.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ReparaYaApp - Gestión Integral de Incidencias</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <div class="container container-wide">
        <!-- Inclusión de la cabecera dinámica -->
        <?php include __DIR__ . '/../layouts/header.php'; ?>
        <h1>Bienvenido a ReparaYaApp</h1>
        <p class="text-muted">Plataforma profesional para la gestión y seguimiento de incidencias y servicios técnicos.</p>

        <div class="mb-20" style="margin-top: 20px;">
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="views/perfil.php" class="btn btn-success">Mi Perfil</a>
                <a href="controllers/AuthController.php?action=logout" class="float-right">Cerrar Sesión</a>
            <?php else: ?>
                <a href="views/auth/login.php" class="btn">Iniciar Sesión</a>
                <a href="views/auth/registro.php" class="btn btn-success" style="margin-left: 10px;">Registrarse</a>
            <?php endif; ?>
        </div>

        <div class="details-box" style="margin-top: 30px;">
            <h3>¿Qué es ReparaYaApp?</h3>
            <p>ReparaYaApp es una solución web desarrollada en PHP nativo bajo arquitectura MVC diseñada para automatizar la solicitud, asignación y resolución de incidencias en diversos servicios técnicos (fontanería, electricidad, climatización, etc.).</p>
        </div>

        <h3>Características Principales</h3>
        <ul>
            <li><strong>Gestión de Roles Dinámica:</strong> Paneles específicos y seguros para Administradores, Técnicos y Clientes Particulares.</li>
            <li><strong>Solicitudes y Precios Base:</strong> Creación de avisos estándar (con validación estricta de 48 horas de antelación) y urgentes.</li>
            <li><strong>Asignación Automática y Control de Estados:</strong> Ciclo completo de vida de la incidencia (Pendiente $\rightarrow$ Asignada $\rightarrow$ En curso $\rightarrow$ Finalizada / Cancelada).</li>
            <li><strong>Historial de Cambios:</strong> Registro cronológico detallado de cada modificación de estado en el servidor.</li>
        </ul>

        <h3>¿Cómo funciona?</h3>
        <ol>
            <li><strong>Regístrate</strong> como cliente particular de forma rápida y sencilla con tu correo electrónico.</li>
            <li><strong>Crea una solicitud</strong> indicando la especialidad, la fecha deseada y la descripción de la avería.</li>
            <li><strong>Realiza un seguimiento</strong> en tiempo real del estado de tu aviso y consulta su historial detallado.</li>
        </ol>

        <div class="text-center" style="margin-top: 40px; border-top: 1px solid #dee2e6; padding-top: 20px;">
            <p class="text-muted">Desarrollado en PHP nativo con seguridad PDO y control de sesiones por roles.</p>
        </div>
    </div>
</body>
</html>