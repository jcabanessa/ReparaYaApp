<?php
/**
 * controllers/TecnicoController.php
 * Gestiona las acciones del técnico (cambiar estados de incidencias asignadas)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Incidencia.php';

// Proteger controlador solo para técnicos
checkRole(['tecnico']);

$incidenciaModel = new Incidencia($pdo);
$action = $_GET['action'] ?? '';

// Procesar el cambio de estado (Asignada -> En curso -> Finalizada)
if ($action === 'cambiar_estado' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidencia_id = $_POST['incidencia_id'] ?? '';
    $nuevo_estado  = $_POST['nuevo_estado'] ?? '';
    $usuario_id    = $_SESSION['usuario_id'];
    $observaciones = trim($_POST['observaciones'] ?? 'Actualización de estado por técnico.');

    try {
        // Obtener la incidencia para verificar que está asignada a este técnico
        $incidencia = $incidenciaModel->obtenerPorId($incidencia_id);
        
        // Buscar el id de técnico vinculado al usuario actual
        $stmt = $pdo->prepare("SELECT id FROM tecnicos WHERE usuario_id = :usuario_id");
        $stmt->execute([':usuario_id' => $usuario_id]);
        $tecnico = $stmt->fetch();

        if (!$tecnico || $incidencia['tecnico_id'] != $tecnico['id']) {
            throw new Exception("No tienes permisos para modificar esta incidencia.");
        }

        // Ejecutar el cambio de estado (el modelo valida las transiciones permitidas)
        $incidenciaModel->cambiarEstado(
            $incidencia_id, 
            $nuevo_estado, 
            $usuario_id, 
            'tecnico', 
            $observaciones
        );

        header('Location: ../views/tecnico/agenda.php?success=actualizado');
        exit();

    } catch (Exception $e) {
        $errorMensaje = urlencode($e->getMessage());
        header('Location: ../views/tecnico/agenda.php?error=' . $errorMensaje);
        exit();
    }
}