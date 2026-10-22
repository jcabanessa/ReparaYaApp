<?php
// src/config/db.php

$host = 'mysql'; // Nombre del contenedor MySQL dentro de la red Docker
$port = '3306';  // Puerto interno de MySQL en Docker
$db   = 'reparayaapp';
$user = 'root';
$pass = 'root';  
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

/**
 * Función global para registrar cada transición de estado en la tabla 'historial_estados'
 */
if (!function_exists('registrarHistorial')) {
    function registrarHistorial($pdo, $incidencia_id, $estado_anterior, $estado_nuevo, $usuario_id, $observaciones = '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO historial_estados (incidencia_id, estado_anterior, estado_nuevo, usuario_id, fecha, observaciones) 
                                   VALUES (:incidencia_id, :estado_anterior, :estado_nuevo, :usuario_id, NOW(), :observaciones)");
            $stmt->execute([
                ':incidencia_id'  => $incidencia_id,
                ':estado_anterior'=> $estado_anterior,
                ':estado_nuevo'   => $estado_nuevo,
                ':usuario_id'     => $usuario_id,
                ':observaciones'  => $observaciones
            ]);
        } catch (PDOException $e) {
            // Silenciar excepciones secundarias
        }
    }
}