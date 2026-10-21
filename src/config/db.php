<?php
date_default_timezone_set("Europe/Madrid");

//Lectura de variables de entorno
$appName     = getenv('APP_NAME') ?: 'ReparaYaApp';
$groupCode   = getenv('GROUP_CODE') ?: 'SIN-GRUPO';
$studentName = getenv('STUDENT_NAME') ?: 'Estudiante';
$appEnv      = getenv('APP_ENV') ?: 'local';
$phpVersion  = getenv('PHP_VERSION') ?: phpversion();

$dbHost     = getenv('MYSQL_HOST') ?: 'mysql';
$dbName     = getenv('MYSQL_DATABASE') ?: 'reparayaapp';
$dbUser     = getenv('MYSQL_USER') ?: 'user';
$dbPassword = getenv('MYSQL_PASSWORD') ?: 'secret_user_pass';


try {
    $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

} catch (PDOException $e) {
    $errorMessage = $e->getMessage();
}
?>