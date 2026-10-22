<?php
/** Controller/AdminController.php
 * Gestiona las acciones administrativas (asignar téncnicos, cancelar, filtrar avisos)
*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Incidencia.php';

// Proteger controlador solo para administradores
checkRole(['admin']);

$incidenciaModel = new Incidencia($pdo);
$action = $_GET['action'] ?? '';

// Asignar técnico a una incidencia
if ($action === 'asignar_tecnico' && $_SERVER['REQUEST_METHOD'] ==='POST') {
    $incidencia_id = $_POST['incidencia_id'] ?? '';
    $tecnico_id    = $_POST['tecnico_id'] ?? '';
    $usuario_id    = $_SESSION['usuario_id'] ??'';

    try {
        if (empty($tecnico_id)) {
            throw new Exception("Debes seleccionar un técnico válido.");
        }
        $incidenciaModel->asignarTecnico($incidencia_id, $tecnico_id, $usuario_id);

        header('Location: ../views/admin/gestion_avisos.php?success=asignado');
        exit();

    } catch (Exception $e) {
        $errorMensaje = urlencode($e->getMessage());
        header('Location: ../views/admin/gestion_avisos.php?error=' . $errorMensaje);
        exit();
    }
}

// Cancelar incidencia por parte del Administrador
if ($action === 'cancelar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidencia_id = $_POST['incidencia_id'] ?? '';
    $usuario_id    = $_SESSION['usuario_id'];

    try {
        $incidenciaModel->cambiarEstado(
            $incidencia_id, 
            'Cancelada', 
            $usuario_id, 
            'admin', 
            'Cancelación ejecutada por el Administrador.'
        );

        header('Location: ../views/admin/gestion_avisos.php?success=cancelada');
        exit();

    } catch (Exception $e) {
        $errorMensaje = urlencode($e->getMessage());
        header('Location: ../views/admin/gestion_avisos.php?error=' . $errorMensaje);
        exit();
    }
}