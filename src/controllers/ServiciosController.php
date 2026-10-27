<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/db.php';

// Validar que el usuario sea Administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . $base_url . '/views/auth/login.php');
    exit;
}

// Procesar los formularios (Alta Técnico, Baja Técnico, Nueva Especialidad)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    // ----------------------------------------------------
    // ACCIÓN: ALTA DE TÉCNICO (Modificado para UOC 1:1)
    // ----------------------------------------------------
    if ($_POST['accion'] === 'crear_tecnico') {
        $nombre = trim($_POST['nombre']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $telefono = trim($_POST['telefono'] ?? '');
        $especialidad_id = intval($_POST['especialidad_id'] ?? 1);

        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // 1. Iniciamos la transacción
            $pdo->beginTransaction();

            // 2. Insertamos en usuarios
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol, telefono) VALUES (:nombre, :email, :password, 'tecnico', :telefono)");
            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':password' => $passwordHash,
                ':telefono' => $telefono
            ]);

            // 3. Atrapamos el ID asignado
            $nuevo_usuario_id = $pdo->lastInsertId();

            // 4. Insertamos en la tabla tecnicos
            $stmtTecnico = $pdo->prepare("INSERT INTO tecnicos (usuario_id, nombre_completo, especialidad_id, disponible) VALUES (:usuario_id, :nombre_completo, :especialidad_id, 1)");
            $stmtTecnico->execute([
                ':usuario_id' => $nuevo_usuario_id,
                ':nombre_completo' => $nombre,
                ':especialidad_id' => $especialidad_id
            ]);

            // 5. Confirmamos todo
            $pdo->commit();
            $_SESSION['mensaje'] = "Técnico registrado correctamente en el sistema (Tablas vinculadas).";

        } catch (PDOException $e) {
            // Si falla, deshacemos ambos
            $pdo->rollBack();
            $_SESSION['error'] = "Error al crear el técnico. Es posible que el email ya exista.";
        }
    }

    // ----------------------------------------------------
    // ACCIÓN: BAJA DE TÉCNICO
    // ----------------------------------------------------
    elseif ($_POST['accion'] === 'eliminar_tecnico') {
        $tecnico_id = $_POST['tecnico_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND rol = 'tecnico'");
            $stmt->execute([':id' => $tecnico_id]);
            $_SESSION['mensaje'] = "Técnico eliminado de la plantilla.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "No se puede eliminar el técnico (posiblemente tenga incidencias asignadas en el historial).";
        }
    }

    // ----------------------------------------------------
    // ACCIÓN: NUEVA ESPECIALIDAD (SERVICIO)
    // ----------------------------------------------------
    elseif ($_POST['accion'] === 'crear_especialidad') {
        $nombre_especialidad = trim($_POST['nombre_especialidad']);
        $precio_base = floatval($_POST['precio_base']);

        try {
            $stmt = $pdo->prepare("INSERT INTO especialidades (nombre_especialidad, precio_base) VALUES (:nombre_especialidad, :precio_base)");
            $stmt->execute([
                ':nombre_especialidad' => $nombre_especialidad,
                ':precio_base' => $precio_base
            ]);
            $_SESSION['mensaje'] = "Especialidad creada correctamente.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al registrar la especialidad.";
        }
    }

    // Redirección limpia (Patrón PRG - Post/Redirect/Get) para volver a la vista
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}