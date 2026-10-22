<?php
/**
 * controllers/ClientController.php
 * Gestiona las acciones del cliente particular (crear solicitudes, etc.)
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../models/Incidencia.php';

// Asegurar que solo accedan clientes
checkRole(['particular']);

$incidenciaModel = new Incidencia($pdo);
$action = $_GET['action'] ?? '';

// Procesar la creación de una nueva solicitud
if ($action === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id      = $_SESSION['usuario_id'];
    $especialidad_id = $_POST['especialidad_id'] ?? '';
    $descripcion     = trim($_POST['descripcion'] ?? '');
    $direccion       = trim($_POST['direccion'] ?? '');
    $fecha_servicio  = $_POST['fecha_servicio'] ?? '';
    $tipo_urgencia   = $_POST['tipo_urgencia'] ?? 'Estándar';

    try {
        // Intentar crear la incidencia (el modelo valida las 48h si es estándar)
        $localizador = $incidenciaModel->crear(
            $cliente_id, 
            $especialidad_id, 
            $descripcion, 
            $direccion, 
            $fecha_servicio, 
            $tipo_urgencia
        );

        // Éxito: Redirigir a sus avisos con el localizador
        header('Location: ../views/cliente/mis_avisos.php?success=creada&loc=' . $localizador);
        exit();

    } catch (Exception $e) {
        // Error de validación (ej. menos de 48h): Redirigir con mensaje de error
        $errorMensaje = urlencode($e->getMessage());
        header('Location: ../views/cliente/nueva_solicitud.php?error=' . $errorMensaje);
        exit();
    }
}

// Procesar la cancelación de una incidencia por parte del cliente
if ($action === 'cancelar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $incidencia_id = $_POST['incidencia_id'] ?? '';
    $cliente_id    = $_SESSION['usuario_id'];

    try {
        // Verificar que la incidencia pertenece al cliente logueado
        $incidencia = $incidenciaModel->obtenerPorId($incidencia_id);
        if (!$incidencia || $incidencia['cliente_id'] != $cliente_id) {
            throw new Exception("No tienes permisos sobre esta solicitud.");
        }

        // Llamar al modelo para cambiar el estado a 'Cancelada' (valida las 48h automáticamente)
        $incidenciaModel->cambiarEstado(
            $incidencia_id, 
            'Cancelada', 
            $cliente_id, 
            'particular', 
            'Cancelación solicitada por el cliente.'
        );

        header('Location: ../views/cliente/detalle_aviso.php?id=' . $incidencia_id . '&success=cancelada');
        exit();

    } catch (Exception $e) {
        $errorMensaje = urlencode($e->getMessage());
        header('Location: ../views/cliente/detalle_aviso.php?id=' . $incidencia_id . '&error=' . $errorMensaje);
        exit();
    }
}