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

/**
 * Verifica que el usuario autenticado pertenezca a uno de los roles permitidos.
 */
function checkRole(array $rolesPermitidos) {
    checkAuth();
    
    $rolActual = $_SESSION['rol'] ?? '';
    if (!in_array($rolActual, $rolesPermitidos)) {
        // Redirigir al panel correspondiente según su rol en caso de acceso no autorizado
        if ($rolActual === 'admin') {
            header('Location: /reparayaapp/views/admin/gestion_avisos.php?error=no_autorizado');
        } elseif ($rolActual === 'tecnico') {
            header('Location: /reparayaapp/views/tecnico/agenda.php?error=no_autorizado');
        } else {
            header('Location: /reparayaapp/views/cliente/mis_avisos.php?error=no_autorizado');
        }
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