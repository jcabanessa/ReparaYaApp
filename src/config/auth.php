<?php
// config/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Verifica si el usuario ha iniciado sesión.
 */
function checkAuth() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: login.php?error=no_auth');
        exit();
    }
}


 # Restringe el acceso a uno o varios roles específicos.
$rolesPermitidos = ['admin', 'tecnico', 'particular'];

function checkRole(array $rolesPermitidos) {
    checkAuth();
    if (!in_array($_SESSION['rol'], $rolesPermitidos)) {
        header('Location: index.php?error=acceso_denegado');
        exit();
    }
}

/**
 * Devuelve los datos del usuario logueado actualmente.
 * @return array|null
 */
function getUsuarioLogueado() {
    if (isset($_SESSION['usuario_id'])) {
        return [
            'id'     => $_SESSION['usuario_id'],
            'nombre' => $_SESSION['nombre'],
            'email'  => $_SESSION['email'],
            'rol'    => $_SESSION['rol']
        ];
    }
    return null;
}