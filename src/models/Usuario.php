<?php
// models/Usuario.php
require_once __DIR__ . '/../config/db.php';

class Usuario {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Registro de nuevo usuario (password_hash)
    public function registrar($nombre, $email, $password,$rol = 'particular', $telefono) {
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nombre, email, password, rol, telefono) VALUES (:nombre, :email, :password, :rol, :telefono)";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            ':nombre'   => $nombre,
            ':email'    => $email,
            ':password' => $hashPassword,
            ':rol'      => $rol,
            ':telefono' => $telefono
        ]);
    }

    // Buscar por email para Login
    public function buscarPorEmail($email) {
        $sql = "SELECT * FROM usuarios WHERE email = :email LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }
}