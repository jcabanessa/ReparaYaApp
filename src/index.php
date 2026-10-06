<?php
date_default_timezone_set("Europe/Madrid");

//Lectura de variables de entorno
$appName     = getenv('APP_NAME') ?: 'ReparaYaApp';
$groupCode   = getenv('GROUP_CODE') ?: 'SIN-GRUPO';
$studentName = getenv('STUDENT_NAME') ?: 'Estudiante';
$appEnv      = getenv('APP_ENV') ?: 'local';

$dbHost     = getenv('MYSQL_HOST') ?: 'mysql';
$dbName     = getenv('MYSQL_DATABASE') ?: 'reparayaapp';
$dbUser     = getenv('MYSQL_USER') ?: 'user';
$dbPassword = getenv('MYSQL_PASSWORD') ?: '1234user';

$dbConnected = false;
$errorMessage = '';
$visitasCount = 0;

try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
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

    $stmt = $pdo->prepare("INSERT INTO comprobacion_persistencia (fecha) VALUES (NOW())");
    $stmt->execute();

    $stmtCount = $pdo->query("SELECT COUNT(*) AS total FROM comprobacion_persistencia");
    $visitasCount = $stmtCount->fetch()['total'];

} catch (PDOException $e) {
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
    <p>Grupo: <?php echo htmlspecialchars($groupCode); ?></p>
    <p>Estudiante: <?php echo htmlspecialchars($studentName); ?></p>
    <p>Entorno: <?php echo htmlspecialchars($appEnv); ?></p>

    <div class="status">
        <?php if ($dbConnected): ?>
            <p>Conexión a la base de datos: <strong>Exitosa</strong></p>
            <p>Número de visitas registradas en la base de datos: <strong><?php echo $visitasCount; ?></strong></p>
        <?php else: ?>
            <p class="error">Conexión a la base de datos: <strong>Fallida</strong></p>
            <p class="error">Error: <?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>
    </div>
</body>
</html>