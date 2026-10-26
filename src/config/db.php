<?php
// src/config/db.php - Versión para el Servidor de la UOC

// Detección automática del entorno para la URL base
$is_uoc = (strpos($_SERVER['HTTP_HOST'] ?? '', 'uoc.edu') !== false);
$base_url = $is_uoc ? '/~uocx9' : '';

// 2. Credenciales de la base de datos en la UOC 
$host = 'localhost'; 
$port = '3306'; // Puerto estándar de MySQL
$db   = 'wordpress9';
$user = 'wordpress9';
$pass = '7eIduWyty2XW56nf';  
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
            // CORREGIDO: La columna en la base de datos es 'nuevo_estado' (no estado_nuevo)
            $stmt = $pdo->prepare("INSERT INTO historial_estados (incidencia_id, estado_anterior, nuevo_estado, usuario_id, observaciones) 
                                   VALUES (:incidencia_id, :estado_anterior, :nuevo_estado, :usuario_id, :observaciones)");
            
            $stmt->execute([
                ':incidencia_id'  => $incidencia_id,
                ':estado_anterior'=> $estado_anterior,
                ':nuevo_estado'   => $estado_nuevo, // Asignamos el valor de la variable
                ':usuario_id'     => $usuario_id,
                ':observaciones'  => $observaciones
            ]);
            
        } catch (PDOException $e) {
            // Si algo falla, esto forzará a que el servidor guarde el error para poder leerlo
            error_log("Fallo en historial: " . $e->getMessage());
        }
    }
}