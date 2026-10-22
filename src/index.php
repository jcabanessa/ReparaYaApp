<?php 
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/views/layouts/header.php'; 
?>

<div class="home-container">
    <h1 class="hero-title">Bienvenido a <span>ReparaYaApp</span></h1>
    <p class="hero-subtitle">Plataforma de gestión, mantenimiento y resolución de incidencias del hogar.</p>

    <div class="home-cards-grid">
        <div class="dashboard-card">
            <h3>¿CÓMO FUNCIONA?</h3>
            <p><strong>Regístrate:</strong> Accede a la plataforma de forma gratuita.</p>
            <p><strong>Solicita un aviso:</strong> Estándar (48h) o de carácter Urgente.</p>
            <p><strong>Asignación rápida:</strong> Nuestro administrador asigna al técnico ideal.</p>
            <p><strong>Seguimiento:</strong> Consulta el estado de tu avería en tiempo real.</p>
        </div>

        <div class="dashboard-card">
            <h3>ESTADO DEL SISTEMA</h3>
            <div class="info-row">
                <span>Entorno:</span>
                <strong>local</strong>
            </div>
            <div class="info-row">
                <span>Versión PHP:</span>
                <strong><?php echo phpversion(); ?></strong>
            </div>
            <div class="info-row">
                <span>Seguridad:</span>
                <strong class="text-orange">PDO & Bcrypt Hash</strong>
            </div>
            <div class="info-row">
                <span>Base de Datos:</span>
                <strong class="text-success">Conectada Correctamente</strong>
            </div>
        </div>

        <div class="dashboard-card">
            <h3>INFORMACIÓN DEL PROYECTO & SERVIDOR</h3>
            <div class="info-grid-2">
                <div>
                    <span class="info-label">Grupo:</span>
                    <strong>backeners</strong>
                </div>
                <div>
                    <span class="info-label">Fecha y Hora:</span>
                    <strong><?php echo date('Y-m-d H:i:s'); ?></strong>
                </div>
                <div>
                    <span class="info-label">Estudiante:</span>
                    <strong>merce_pinar</strong>
                </div>
                <div>
                    <span class="info-label">Patrón Arquitectura:</span>
                    <strong>MVC modularizado</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>