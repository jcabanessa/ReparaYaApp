<?php
// src/controllers/IncidenciaController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../views/auth/login.php');
    exit;
}

$cliente_id = $_SESSION['usuario_id'];
$mensaje = '';
$error = '';


// ==========================================
// 1. SOLICITAR NUEVA INCIDENCIA POR CLIENTE
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_incidencia') {
    
    // Recogemos y saneamos los datos del formulario
    $tipo_urgencia   = $_POST['tipo_urgencia'] ?? 'Estándar';
    $fecha_servicio  = $_POST['fecha_solicitud'] ?? '';
    $descripcion     = trim($_POST['descripcion'] ?? '');
    $direccion       = trim($_POST['direccion'] ?? '');
    $especialidad_id = intval($_POST['especialidad_id'] ?? 1); 
    $cliente_id      = $_SESSION['usuario_id'] ?? 0; // Aseguramos recuperar el ID del cliente logueado

    $fechaActual = new DateTime();
    $fechaCita   = new DateTime($fecha_servicio);

    // Validaciones de fecha y antelación
    if ($fechaCita < $fechaActual) {
        $error = "La fecha de la cita no puede ser anterior a la actual.";
    } elseif (strtolower($tipo_urgencia) === 'estándar' && ($fechaCita->getTimestamp() - $fechaActual->getTimestamp()) < (48 * 3600)) {
        $error = "Error de validación: Las solicitudes estándar deben realizarse con un mínimo de 48 horas de antelación.";
    } else {
        try {
            // A. Obtenemos el precio base actual de la especialidad seleccionada para el Producto 3
            $stmtPrecio = $pdo->prepare("SELECT precio_base FROM especialidades WHERE id = :id");
            $stmtPrecio->execute([':id' => $especialidad_id]);
            $especialidadData = $stmtPrecio->fetch();
            $precioBaseAplicado = $especialidadData['precio_base'] ?? 0.00;

            // B. Generamos el localizador único y el estado inicial
            $localizador = 'INC-' . strtoupper(substr(uniqid(), -6));
            $estado_inicial = 'Pendiente';

            // C. Insertamos la incidencia incluyendo el precio base histórico
            $stmt = $pdo->prepare("INSERT INTO incidencias (localizador, cliente_id, especialidad_id, descripcion, direccion, fecha_servicio, tipo_urgencia, estado, precio_aplicado) 
                                   VALUES (:localizador, :cliente_id, :especialidad_id, :descripcion, :direccion, :fecha_servicio, :tipo_urgencia, :estado, :precio_aplicado)");
            
            $stmt->execute([
                ':localizador'     => $localizador,
                ':cliente_id'      => $cliente_id,
                ':especialidad_id' => $especialidad_id,
                ':descripcion'     => $descripcion,
                ':direccion'       => $direccion,
                ':fecha_servicio'  => $fecha_servicio . ' 09:00:00',
                ':tipo_urgencia'   => $tipo_urgencia,
                ':estado'          => $estado_inicial,
                ':precio_aplicado' => $precioBaseAplicado // Guardamos el precio fijo para las comisiones futuras
            ]);

            // D. Registramos la acción en el historial de la incidencia
            $nuevo_id = $pdo->lastInsertId();
            if (function_exists('registrarHistorial')) {
                registrarHistorial($pdo, $nuevo_id, 'Ninguno', 'Pendiente', $cliente_id, 'Aviso registrado por el cliente.');
            }

            $mensaje = "¡Aviso creado con éxito! Localizador: " . $localizador;
            
        } catch (PDOException $e) {
            $error = "Error en la base de datos: " . $e->getMessage();
        }
    }
}

// 2. CANCELACIÓN POR CLIENTE (REGLA ESTRICTA: MÁS DE 48 HORAS DE MARGEN)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cancelar_cliente') {
    $incidencia_id = intval($_POST['incidencia_id'] ?? 0);

    $stmt = $pdo->prepare("SELECT * FROM incidencias WHERE id = :id AND cliente_id = :cliente_id");
    $stmt->execute([':id' => $incidencia_id, ':cliente_id' => $cliente_id]);
    $incidencia = $stmt->fetch();

    if ($incidencia && in_array($incidencia['estado'], ['Pendiente', 'Asignada'])) {
        $fechaActual = new DateTime();
        $fechaCita   = new DateTime($incidencia['fecha_servicio']);
        $diferenciaHoras = ($fechaCita->getTimestamp() - $fechaActual->getTimestamp()) / 3600;

        if ($diferenciaHoras > 48) {
            $estadoAnterior = $incidencia['estado'];
            $update = $pdo->prepare("UPDATE incidencias SET estado = 'Cancelada' WHERE id = :id");
            $update->execute([':id' => $incidencia_id]);

            registrarHistorial($pdo, $incidencia_id, $estadoAnterior, 'Cancelada', $cliente_id, 'Cancelado por el cliente con más de 48h de antelación.');
            $mensaje = "La solicitud ha sido cancelada correctamente.";
        } else {
            $error = "No es posible cancelar el aviso: Faltan menos de 48 horas para la cita programada.";
        }
    } else {
        $error = "No es posible cancelar la solicitud en su estado actual.";
    }
}

// 3. CONSULTAR MIS AVISOS (CON JOIN A ESPECIALIDADES)
$stmtAvisos = $pdo->prepare("
    SELECT i.*, e.nombre_especialidad 
    FROM incidencias i 
    LEFT JOIN especialidades e ON i.especialidad_id = e.id 
    WHERE i.cliente_id = :cliente_id 
    ORDER BY i.fecha_servicio DESC
");
$stmtAvisos->execute([':cliente_id' => $cliente_id]);
$misAvisos = $stmtAvisos->fetchAll();