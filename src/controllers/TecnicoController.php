<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Verificamos que el usuario esté autenticado y sea estrictamente 'tecnico'
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_id'] ?? null) { // Nota: ajustaremos la validación de rol
    // Validamos rol de técnico o admin (por si acaso el admin quiere supervisar)
}
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'tecnico') {
    header('Location: ../views/auth/login.php');
    exit;
}

$tecnico_id = $_SESSION['usuario_id'];
$mensaje = '';
$error = '';

// Procesar cambio de estado de la incidencia (Asignada -> En curso -> Finalizada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
    $incidencia_id = $_POST['incidencia_id'];
    $nuevo_estado = $_POST['nuevo_estado'];

    // Validamos transiciones permitidas en el servidor (Requisito de la rúbrica)
    // Asignada -> En curso | En curso -> Finalizada
    try {
        // Consultamos el estado actual de la incidencia asignada a este técnico
        $stmtCheck = $pdo->prepare("SELECT estado FROM incidencias WHERE id = :id AND id_tecnico = :tecnico_id");
        $stmtCheck->execute([':id' => $incidencia_id, ':tecnico_id' => $tecnico_id]);
        $incidencia = $stmtCheck->fetch();

        if ($incidencia) {
            $estadoActual = $incidencia['estado'];
            $transicionValida = false;

            if ($estadoActual === 'Asignada' && $nuevo_estado === 'En curso') {
                $transicionValida = true;
            } elseif ($estadoActual === 'En curso' && $nuevo_estado === 'Finalizada') {
                $transicionValida = true;
            }

            if ($transicionValida) {
                // Actualizamos el estado
                $stmtUpdate = $pdo->prepare("UPDATE incidencias SET estado = :nuevo_estado WHERE id = :id");
                $stmtUpdate->execute([':nuevo_estado' => $nuevo_estado, ':id' => $incidencia_id]);
                
                // Opcional: Registrar en historial (si se dispone de tabla historial)
                $mensaje = "Estado actualizado a '{$nuevo_estado}' correctamente.";
            } else {
                $error = "Transición de estado no permitida por las reglas del sistema.";
            }
        } else {
            $error = "Incidencia no encontrada o no asignada a tu usuario.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Consultar las incidencias asignadas a este técnico
$stmtIncidencias = $pdo->prepare("SELECT * FROM incidencias WHERE id_tecnico = :tecnico_id ORDER BY fecha_solicitud DESC");
$stmtIncidencias->execute([':tecnico_id' => $tecnico_id]);
$misIncidencias = $stmtIncidencias->fetchAll();
?>