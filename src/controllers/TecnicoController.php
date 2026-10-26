<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Detección dinámica de $base_url para compatibilidad con UOC y Local
$is_uoc = (strpos($_SERVER['HTTP_HOST'] ?? '', 'uoc.edu') !== false);
$base_url = $is_uoc ? '/~uocx9' : '';

// Verificamos que el usuario esté autenticado y sea estrictamente 'tecnico'
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'tecnico') {
    header('Location: ' . $base_url . '/views/auth/login.php');
    exit;
}

$tecnico_id = $_SESSION['usuario_id'];

// ==========================================
// PROCESAR CAMBIO DE ESTADO (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
    $incidencia_id = intval($_POST['incidencia_id'] ?? 0);
    $nuevo_estado = $_POST['nuevo_estado'] ?? '';

    try {
        // Consultamos el estado actual de la incidencia asignada a este técnico (usando tecnico_id)
        $stmtCheck = $pdo->prepare("SELECT estado FROM incidencias WHERE id = :id AND tecnico_id = :tecnico_id");
        $stmtCheck->execute([':id' => $incidencia_id, ':tecnico_id' => $tecnico_id]);
        $incidencia = $stmtCheck->fetch();

        if ($incidencia) {
            $estadoActual = $incidencia['estado'];
            $transicionValida = false;

            // Validamos transiciones permitidas
            if ($estadoActual === 'Asignada' && $nuevo_estado === 'En curso') {
                $transicionValida = true;
            } elseif ($estadoActual === 'En curso' && $nuevo_estado === 'Finalizada') {
                $transicionValida = true;
            }

            if ($transicionValida) {
                // Actualizamos el estado
                $stmtUpdate = $pdo->prepare("UPDATE incidencias SET estado = :nuevo_estado WHERE id = :id");
                $stmtUpdate->execute([':nuevo_estado' => $nuevo_estado, ':id' => $incidencia_id]);
                
                // Registramos en el historial si la función existe
                if (function_exists('registrarHistorial')) {
                    registrarHistorial($pdo, $incidencia_id, $estadoActual, $nuevo_estado, $tecnico_id, "Estado actualizado a '{$nuevo_estado}' por el técnico.");
                }

                $_SESSION['exito_incidencia'] = "Estado actualizado a '{$nuevo_estado}' correctamente.";
            } else {
                $_SESSION['error_incidencia'] = "Transición de estado no permitida por las reglas del sistema.";
            }
        } else {
            $_SESSION['error_incidencia'] = "Incidencia no encontrada o no asignada a tu usuario.";
        }
    } catch (PDOException $e) {
        $_SESSION['error_incidencia'] = "Error en la base de datos: " . $e->getMessage();
    }

    // Redirección limpia (Patrón PRG)
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// ==========================================
// CONSULTAR INCIDENCIAS ASIGNADAS AL TÉCNICO
// ==========================================
$stmtIncidencias = $pdo->prepare("
    SELECT i.*, e.nombre_especialidad 
    FROM incidencias i 
    LEFT JOIN especialidades e ON i.especialidad_id = e.id 
    WHERE i.tecnico_id = :tecnico_id 
    ORDER BY i.fecha_servicio DESC
");
$stmtIncidencias->execute([':tecnico_id' => $tecnico_id]);
$misIncidencias = $stmtIncidencias->fetchAll();