<?php
/**
 * controllers/AuthController.php
 * Gestiona el inicio de sesión, registro y cierre de sesión de forma segura.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$action = $_GET['action'] ?? '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Procesar Login
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Registrar datos en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['nombre']     = $usuario['nombre'];
            $_SESSION['email']      = $usuario['email'];
            $_SESSION['rol']        = $usuario['rol'];

            // Redirigir según el rol del usuario
            if ($usuario['rol'] === 'admin') {
                header('Location: ../views/admin/gestion_avisos.php');
            } elseif ($usuario['rol'] === 'tecnico') {
                header('Location: ../views/tecnico/agenda.php');
            } else {
                header('Location: ../views/cliente/mis_avisos.php');
            }
            exit();
        } else {
            throw new Exception("Credenciales incorrectas.");
        }
    } catch (Exception $e) {
        header('Location: ../views/auth/login.php?error=1');
        exit();
    }
}

// Procesar Registro de nuevo cliente particular
if ($action === 'registro' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    try {
        if (empty($nombre) || empty($email) || empty($password)) {
            throw new Exception("Todos los campos obligatorios deben completarse.");
        }

        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        $rol = 'particular'; // Rol por defecto para registros públicos

        $sql = "INSERT INTO usuarios (nombre, email, password, telefono, rol) VALUES (:nombre, :email, :password, :telefono, :rol)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombre'   => $nombre,
            ':email'    => $email,
            ':password' => $hashPassword,
            ':telefono' => $telefono,
            ':rol'      => $rol
        ]);

        header('Location: ../views/auth/login.php?msg=registrado');
        exit();

    } catch (Exception $e) {
        header('Location: ../views/auth/registro.php?error=1');
        exit();
    }
}

// Procesar Logout
if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: ../views/auth/login.php?msg=sesion_cerrada');
    exit();
}