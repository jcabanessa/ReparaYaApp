<?php
/**
 * controllers/ProfileController.php
 * Gestiona la actualización de datos personales para cualquier rol autenticado.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Usuario.php';

// Cualquier usuario autenticado puede acceder a su propio perfil
checkAuth();

$usuarioModel = new Usuario($pdo);
$action = $_GET['action'] ?? '';

if ($action === 'actualizar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = $_SESSION['usuario_id'];
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    try {
        if (empty($nombre) || empty($email)) {
            throw new Exception("El nombre y el correo electrónico son obligatorios.");
        }

        // Actualizar en base de datos
        $usuarioModel->actualizarPerfil($id, $nombre, $email, $password);

        // Actualizar las variables de sesión activas
        $_SESSION['nombre'] = $nombre;
        $_SESSION['email']  = $email;

        header('Location: ../views/perfil.php?success=actualizado');
        exit();

    } catch (Exception $e) {
        $errorMensaje = urlencode($e->getMessage());
        header('Location: ../views/perfil.php?error=' . $errorMensaje);
        exit();
    }
}