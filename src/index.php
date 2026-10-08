<?php
// 1. CONFIGURACIÓN INICIAL
// Establece la zona horaria para que las funciones de fecha (como date) muestren la hora local correcta.
date_default_timezone_set("Europe/Madrid");

//Lectura de variables de entorno
// getenv() busca el valor en el archivo .env o en el docker-compose.yaml.
// El operador ?: significa: "Si no encuentras la variable, usa el valor por defecto que está a la derecha".
$appName     = getenv('APP_NAME') ?: 'ReparaYaApp';
$groupCode   = getenv('GROUP_CODE') ?: 'SIN-GRUPO';
$studentName = getenv('STUDENT_NAME') ?: 'Estudiante';
$appEnv      = getenv('APP_ENV') ?: 'local';
$phpVersion  = getenv('PHP_VERSION') ?: phpversion();

// 3. CREDENCIALES DE LA BASE DE DATOS
// Lee la configuración inyectada por Docker de forma segura para no tener contraseñas escritas directamente.
$dbHost     = getenv('MYSQL_HOST') ?: 'mysql';
$dbName     = getenv('MYSQL_DATABASE') ?: 'reparayaapp';
$dbUser     = getenv('MYSQL_USER') ?: 'user';
$dbPassword = getenv('MYSQL_PASSWORD') ?: 'secret_user_pass';

// Variables de control que usaremos más abajo para decidir qué mostrar en el HTML.
$dbConnected = false;
$errorMessage = '';
$visitasCount = 0;


// 4. CONEXIÓN A LA BASE DE DATOS Y GESTIÓN DE ERRORES
// Usamos try/catch. Si algo dentro del 'try' falla (ej: la base de datos está apagada), 
// el sistema no se cuelga, sino que salta al 'catch' y guarda el error.
try {
    // DSN (Data Source Name): Especifica el tipo de base de datos, el servidor y el nombre de la base.
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";

    // Crea la conexión PDO. Es el estándar moderno y seguro que también exige Laravel.
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $dbConnected = true;

    // Tabla para comprobar la persistencia en el volumen mysql-data
    $pdo->exec("CREATE TABLE IF NOT EXISTS comprobacion_persistencia (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATETIME NOT NULL
    )");

// Prepara e inserta un registro nuevo con la fecha y hora actual cada vez que se recarga la página.
    $stmt = $pdo->prepare("INSERT INTO comprobacion_persistencia (fecha) VALUES (NOW())");
    $stmt->execute();

    // Hace una consulta para contar cuántos registros (visitas) hay en total en la tabla.
    $stmtCount = $pdo->query("SELECT COUNT(*) AS total FROM comprobacion_persistencia");
    $visitasCount = $stmtCount->fetch()['total'];

} catch (PDOException $e) {
    // Si la conexión falla, cambiamos el estado a falso y guardamos el motivo exacto del fallo.
    $dbConnected  = false;
    $errorMessage = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($appName); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .status { margin-top: 20px; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1><?php echo htmlspecialchars($appName); ?></h1>
    <p><b>Grupo:</b> <?php echo htmlspecialchars($groupCode); ?></p>
    <p><b>Estudiante:</b> <?php echo htmlspecialchars($studentName); ?></p>
    <p><b>Entorno:</b> <?php echo htmlspecialchars($appEnv); ?></p>
    <p><b>Versión de PHP:</b> <?php echo htmlspecialchars($phpVersion); ?></p>
    <p><b>Fecha y hora:</b> <?php echo date("Y-m-d H:i:s"); ?></p>

    <div class="status">
       <!-- Si $dbConnected es true (conexión exitosa), muestra el bloque verde y el contador de visitas. --> 
        <?php if ($dbConnected): ?>
            <p><b>Conexión a la base de datos:</b> <strong style="color: green;">Exitosa</strong></p>
            <p><b>Número de visitas registradas en la base de datos:</b> <strong style="color: blue;"><?php echo $visitasCount; ?></strong></p>
        
        <!-- Si $dbConnected es false, entra en el 'else' y muestra el bloque rojo con el error capturado. -->
            <?php else: ?>
            <p class="error"><b>Conexión a la base de datos:</b> <strong style="color: red;">Fallida</strong></p>
            <p class="error"><b>Error:</b> <?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>