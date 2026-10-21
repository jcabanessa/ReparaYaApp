<?php
// controllers/AuthController.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Usuario.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioModel = new Usuario($pdo);
$action = $_GET['action'] ?? '';

// Procesar Login
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $usuario = $usuarioModel->buscarPorEmail($email);

    if ($usuario && password_verify($password, $usuario['password'])) {
        // Credenciales correctas: Iniciar sesión
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['email']      = $usuario['email'];
        $_SESSION['rol']        = $usuario['rol'];

        // Redireccionar según el rol
        switch ($usuario['rol']) {
            case 'admin':
                header('Location: ../views/admin/dashboard.php');
                break;
            case 'tecnico':
                header('Location: ../views/tecnico/agenda.php');
                break;
            case 'particular':
            default:
                header('Location: ../views/cliente/mis_avisos.php');
                break;
        }
        exit();
    } else {
        header('Location: ../views/login.php?error=invalid_credentials');
        exit();
    }
}

// Procesar Registro
if ($action === 'registro' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        if ($usuarioModel->registrar($nombre, $email, $password, $telefono, 'particular')) {
            header('Location: ../views/login.php?success=registrado');
            exit();
        }
    }
    header('Location: ../views/registro.php?error=fallo_registro');
    exit();
}

// Logout
if ($action === 'logout') {
    session_destroy();
    header('Location: ../views/login.php?msg=sesion_cerrada');
    exit();
}