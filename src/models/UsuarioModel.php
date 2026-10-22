<?php

require_once '../config/db.php'; // Cargar la conexión PDO

// Función de ejemplo para guardar un usuario protegiendo contra Inyección SQL
function registrarUsuario($pdo, $email, $password_hasheada, $rol) {
    // 1. Preparar la sentencia SQL con "huecos" (:email, :password, :rol)
    $sql = "INSERT INTO usuarios (email, password, rol) VALUES (:email, :password, :rol)";
    $stmt = $pdo->prepare($sql);
    
    // 2. Ejecutar pasando los parámetros de forma segura
    return $stmt->execute([
        ':email' => $email,
        ':password' => $password_hasheada,
        ':rol' => $rol
    ]);
}
?>