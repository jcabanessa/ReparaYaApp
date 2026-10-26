<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Verificamos que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . $base_url . '/views/auth/login.php');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$mensaje = '';
$error = '';

// ==========================================
// 1. PROCESAR MODIFICACIÓN DE DATOS PERSONALES
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_datos') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if (empty($nombre) || empty($email)) {
        $error = "El nombre y el correo electrónico son obligatorios.";
    } else {
        try {
            // Actualizamos los datos del usuario
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre = :nombre, email = :email, telefono = :telefono WHERE id = :id");
            $stmt->execute([
                ':nombre'   => $nombre,
                ':email'    => $email,
                ':telefono' => $telefono,
                ':id'       => $usuario_id
            ]);

            // Actualizamos el nombre en la sesión si estuviera guardado
            $_SESSION['usuario_nombre'] = $nombre;
            $mensaje = "Datos personales actualizados correctamente.";
        } catch (PDOException $e) {
            $error = "Error al actualizar los datos. Es posible que el email ya esté registrado por otro usuario.";
        }
    }
}

// ==========================================
// 2. PROCESAR CAMBIO DE CONTRASEÑA
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_password') {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';

    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
        $error = "Todos los campos de contraseña son obligatorios.";
    } elseif ($password_nueva !== $password_confirmar) {
        $error = "La nueva contraseña y su confirmación no coinciden.";
    } elseif (strlen($password_nueva) < 6) {
        $error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } else {
        try {
            // Obtenemos la contraseña encriptada actual
            $stmtPass = $pdo->prepare("SELECT password FROM usuarios WHERE id = :id");
            $stmtPass->execute([':id' => $usuario_id]);
            $user = $stmtPass->fetch();

            if ($user && password_verify($password_actual, $user['password'])) {
                // Generamos el nuevo hash BCRYPT
                $nuevoHash = password_hash($password_nueva, PASSWORD_BCRYPT);
                
                $stmtUpdate = $pdo->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
                $stmtUpdate->execute([
                    ':password' => $nuevoHash,
                    ':id'       => $usuario_id
                ]);

                $mensaje = "Contraseña actualizada con éxito.";
            } else {
                $error = "La contraseña actual introducida no es correcta.";
            }
        } catch (PDOException $e) {
            $error = "Error en la base de datos al cambiar la contraseña.";
        }
    }
}

// ==========================================
// 3. CONSULTAR DATOS ACTUALIZADOS DEL USUARIO
// ==========================================
$stmtUser = $pdo->prepare("SELECT id, nombre, email, rol, telefono, created_at FROM usuarios WHERE id = :id");
$stmtUser->execute([':id' => $usuario_id]);
$usuario = $stmtUser->fetch();
?>