<?php
// src/controllers/IncidenciaController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Detección dinámica de $base_url para producción (UOC) y Local
$is_uoc = (strpos($_SERVER['HTTP_HOST'] ?? '', 'uoc.edu') !== false);
$base_url = $is_uoc ? '/~uocx9' : '';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . $base_url . '/views/auth/login.php');
    exit;
}

$cliente_id = $_SESSION['usuario_id'];

// ==========================================
// 1. SOLICITAR NUEVA INCIDENCIA
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear_incidencia') {
    
    // Recogemos y saneamos los datos del formulario
    $tipo_urgencia   = $_POST['tipo_urgencia'] ?? 'Estándar';
    $fecha_servicio  = $_POST['fecha_solicitud'] ?? '';
    $descripcion     = trim($_POST['descripcion'] ?? '');
    $direccion       = trim($_POST['direccion'] ?? '');
    $especialidad_id = intval($_POST['especialidad_id'] ?? 1); 

    $fechaActual = new DateTime();
    $fechaCita   = new DateTime($fecha_servicio . ' 09:00:00');

    // Limpiamos el texto que viene del formulario (pasándolo a minúsculas sin tildes)
    $tipoMinuscula = trim(str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], strtolower($tipo_urgencia)));
    $esEstandar = ($tipoMinuscula === 'estandar');

  
  // Validaciones de fecha y antelación
    if ($fechaCita < $fechaActual) {
        $_SESSION['error_incidencia'] = "La fecha de la cita no puede ser anterior a la actual.";
        header('Location: ' . $_SERVER['HTTP_REFERER']); // Devuelve a la página exacta de origen
        exit();
    } elseif ($esEstandar && ($fechaCita->getTimestamp() - $fechaActual->getTimestamp()) < (48 * 3600)) {
        $_SESSION['error_incidencia'] = "Error de validación: Las solicitudes estándar deben realizarse con un mínimo de 48 horas de antelación.";
        header('Location: ' . $_SERVER['HTTP_REFERER']); // Devuelve a la página exacta de origen
        exit();
    } else {
        try {
            // A. Obtenemos el precio base actual de la especialidad seleccionada
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
                ':precio_aplicado' => $precioBaseAplicado
            ]);

          // D. Registramos la acción en el historial de la incidencia
            $nuevo_id = $pdo->lastInsertId();
            if (function_exists('registrarHistorial')) {
                registrarHistorial($pdo, $nuevo_id, 'Ninguno', 'Pendiente', $cliente_id, 'Aviso registrado.');
            }

            // Mensaje de éxito en sesión
            $_SESSION['exito_incidencia'] = "¡Aviso creado con éxito! Localizador asignado: " . $localizador;

            // Redirección inteligente según el rol del usuario actual
            if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
                header('Location: ' . $base_url . '/views/admin/panel.php');
            } else {
                header('Location: ' . $base_url . '/views/incidencias/mis_avisos.php');
            }
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error_incidencia'] = "Error en la base de datos: " . $e->getMessage();
            header('Location: ' . $base_url . '/views/incidencias/nueva.php');
            exit();
        }
    }
}

// ==========================================
// 2. CANCELACIÓN POR CLIENTE
// ==========================================
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

            if (function_exists('registrarHistorial')) {
                registrarHistorial($pdo, $incidencia_id, $estadoAnterior, 'Cancelada', $cliente_id, 'Cancelado por el cliente con más de 48h de antelación.');
            }
            $_SESSION['exito_incidencia'] = "La solicitud ha sido cancelada correctamente.";
        } else {
            $_SESSION['error_incidencia'] = "No es posible cancelar el aviso: Faltan menos de 48 horas para la cita programada.";
        }
    } else {
        $_SESSION['error_incidencia'] = "No es posible cancelar la solicitud en su estado actual.";
    }

    header('Location: ' . $base_url . '/views/incidencias/mis_avisos.php');
    exit();
}

// ==========================================
// 3. CONSULTAR MIS AVISOS
// ==========================================
$stmtAvisos = $pdo->prepare("
    SELECT i.*, e.nombre_especialidad 
    FROM incidencias i 
    LEFT JOIN especialidades e ON i.especialidad_id = e.id 
    WHERE i.cliente_id = :cliente_id 
    ORDER BY i.fecha_servicio DESC
");
$stmtAvisos->execute([':cliente_id' => $cliente_id]);
$misAvisos = $stmtAvisos->fetchAll();