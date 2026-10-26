<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Cargamos la configuración (conexión $pdo y definición de $base_url)
require_once __DIR__ . '/config/db.php';

// Detección automática del entorno
$is_uoc = (strpos($_SERVER['HTTP_HOST'] ?? '', 'uoc.edu') !== false);
$base_url = $is_uoc ? '/~uocx9' : '';

// 2. Capturamos la URL que envía el .htaccess
$url = $_GET['url'] ?? '';
$url = trim($url, '/');

// 3. Si la URL está vacía o es 'index.php', mostramos la página de bienvenida original
if ($url === '' || $url === 'index.php' || $url === 'home') {
    
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
                    <strong><?php echo $is_uoc ? 'producción (UOC)' : 'local'; ?></strong>
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
    <?php 
    require_once __DIR__ . '/views/layouts/footer.php';

} else {
    // --- ENRUTADOR INTELIGENTE ESCALABLE (Para TODOS los enlaces) ---
    // 1. Limpiamos la URL que nos llega
    $clean_url = str_replace(['~uocx9/', 'views/'], '', $url);
    $clean_url = trim($clean_url, '/');
    
    // 2. Separamos la URL por barras (Ej: 'admin/panel.php' -> ['admin', 'panel.php'])
    $partes = explode('/', $clean_url);
    
    // 3. Diccionario de carpetas: Traduce las minúsculas a la mayúscula real de Linux
    $carpetas_reales = [
        'admin'       => 'Admin',
        'incidencias' => 'Incidencias',
        'perfil'      => 'Perfil',
        'tecnico'     => 'Tecnico',
        'auth'        => 'auth' // Si tu carpeta auth está en minúscula, déjala así
    ];

    // 4. Si la ruta tiene una carpeta, la buscamos en el diccionario y la corregimos automáticamente
    if (count($partes) >= 1) {
        $carpeta_peticion = strtolower($partes[0]);
        if (isset($carpetas_reales[$carpeta_peticion])) {
            $partes[0] = $carpetas_reales[$carpeta_peticion]; // Cambia 'admin' por 'Admin'
        }
    }

    // 5. Reconstruimos la ruta final
    $ruta_corregida = implode('/', $partes);
    $target_file = __DIR__ . '/views/' . $ruta_corregida;
    
    // 6. Por si al enlace le falta el ".php", se lo añadimos
    if (!file_exists($target_file) && file_exists($target_file . '.php')) {
        $target_file .= '.php';
    }

    // 7. Cargamos la vista o damos 404
    if (file_exists($target_file) && !is_dir($target_file)) {
        require_once $target_file;
    } else {
        header("HTTP/1.0 404 Not Found");
        echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                <h2>404 - Página no encontrada</h2>
                <p>La ruta solicitada no existe en ReparaYaApp.</p>
                <a href='" . $base_url . "/'>Volver al Inicio</a>
              </div>";
    }
}