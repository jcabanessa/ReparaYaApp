<?php
// src/controllers/AdminController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Control estricto de acceso por rol 'admin'
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../views/auth/login.php');
    exit;
}

$mensaje = '';
$error = '';

// 1. PROCESAR ALTA MANUAL DE INCIDENCIA POR EL ADMIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'alta_manual_incidencia') {
    $cliente_id      = intval($_POST['cliente_id'] ?? 0);
    $especialidad_id = intval($_POST['especialidad_id'] ?? 0);
    $tipo_urgencia   = $_POST['tipo_urgencia'] ?? 'Estándar';
    $fecha_solicitada= $_POST['fecha_servicio'] ?? '';
    $hora_solicitada = $_POST['hora_servicio'] ?? '09:00';
    $direccion       = trim($_POST['direccion'] ?? '');
    $descripcion     = trim($_POST['descripcion'] ?? '');
    $tecnico_id      = !empty($_POST['tecnico_id']) ? intval($_POST['tecnico_id']) : null;

    if ($cliente_id <= 0 || $especialidad_id <= 0 || empty($fecha_solicitada) || empty($direccion) || empty($descripcion)) {
        $error = "Por favor, completa todos los campos obligatorios del aviso.";
    } else {
        $fechaHoraServicio = $fecha_solicitada . ' ' . $hora_solicitada . ':00';
        $fechaActual = new DateTime();
        $fechaCita   = new DateTime($fechaHoraServicio);

        // Regla de Negocio: Validar mínimo 48 horas para servicios estándar
        if ($fechaCita < $fechaActual) {
            $error = "La fecha del servicio no puede ser anterior a la actual.";
        } elseif (strtolower($tipo_urgencia) === 'estándar' && ($fechaCita->getTimestamp() - $fechaActual->getTimestamp()) < (48 * 3600)) {
            $error = "Error de regla de negocio: Las solicitudes estándar requieren al menos 48 horas de antelación.";
        } else {
            try {
                $localizador = 'INC-' . strtoupper(substr(uniqid(), -6));
                $estado_inicial = !empty($tecnico_id) ? 'Asignada' : 'Pendiente';

                $stmt = $pdo->prepare("INSERT INTO incidencias (localizador, cliente_id, tecnico_id, especialidad_id, descripcion, direccion, fecha_servicio, tipo_urgencia, estado) 
                                       VALUES (:localizador, :cliente_id, :tecnico_id, :especialidad_id, :descripcion, :direccion, :fecha_servicio, :tipo_urgencia, :estado)");
                
                $stmt->execute([
                    ':localizador'     => $localizador,
                    ':cliente_id'      => $cliente_id,
                    ':tecnico_id'      => $tecnico_id,
                    ':especialidad_id' => $especialidad_id,
                    ':descripcion'     => $descripcion,
                    ':direccion'       => $direccion,
                    ':fecha_servicio'  => $fechaHoraServicio,
                    ':tipo_urgencia'   => $tipo_urgencia,
                    ':estado'          => $estado_inicial
                ]);

                $nuevo_id = $pdo->lastInsertId();
                registrarHistorial($pdo, $nuevo_id, 'Ninguno', $estado_inicial, $_SESSION['usuario_id'], 'Alta manual registrada por el Administrador.');

                $mensaje = "¡Aviso creado manualmente con éxito! Localizador: " . $localizador;
            } catch (PDOException $e) {
                $error = "Error en la base de datos: " . $e->getMessage();
            }
        }
    }
}

// 2. CANCELACIÓN POR EL ADMIN (Sin restricción de 48h, siempre que esté Pendiente o Asignada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar_admin') {
    $incidencia_id = intval($_POST['incidencia_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM incidencias WHERE id = :id");
    $stmt->execute([':id' => $incidencia_id]);
    $incidencia = $stmt->fetch();

    if ($incidencia && in_array($incidencia['estado'], ['Pendiente', 'Asignada'])) {
        $estadoAnterior = $incidencia['estado'];
        $update = $pdo->prepare("UPDATE incidencias SET estado = 'Cancelada' WHERE id = :id");
        $update->execute([':id' => $incidencia_id]);

        registrarHistorial($pdo, $incidencia_id, $estadoAnterior, 'Cancelada', $_SESSION['usuario_id'], 'Cancelación realizada por el Administrador.');
        $mensaje = "Incidencia cancelada correctamente.";
    } else {
        $error = "No se puede cancelar una incidencia en el estado actual.";
    }
}

// Consultas para poblar selectores
$clientes       = $pdo->query("SELECT id, nombre, email FROM usuarios WHERE rol = 'particular' ORDER BY nombre ASC")->fetchAll();
$especialidades = $pdo->query("SELECT id, nombre_especialidad, precio_base FROM especialidades ORDER BY nombre_especialidad ASC")->fetchAll();
$tecnicos       = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'tecnico' ORDER BY nombre ASC")->fetchAll();