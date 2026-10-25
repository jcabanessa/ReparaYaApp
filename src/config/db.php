<?php
declare(strict_types=1);

// Conexión centralizada a MySQL mediante PDO.
function conectarBD(): PDO
{
    $host = getenv('MYSQL_HOST') ?: 'mysql';
    $database = getenv('MYSQL_DATABASE') ?: 'reparayaapp';
    $usuario = getenv('MYSQL_USER');
    $password = getenv('MYSQL_PASSWORD');

    if ($usuario === false || $password === false) {
        throw new RuntimeException(
            'Faltan variables de configuración de MySQL.'
        );
    }

    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";

    return new PDO($dsn, $usuario, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
}
