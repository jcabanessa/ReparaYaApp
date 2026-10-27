<?php
// src/controllers/AdminController.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php';

// Control estricto de acceso por rol 'admin'
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . $base_url . '/views/auth/login.php');
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
            // 0. Iniciamos la transacción
            $pdo->beginTransaction();

            // 1. PRIMERO: Borramos de la tabla dependiente (tecnicos)
            $stmt1 = $pdo->prepare("DELETE FROM tecnicos WHERE usuario_id = :id");
            $stmt1->execute([':id' => $tecnico_id]);

            // 2. SEGUNDO: Borramos de la tabla principal (usuarios)
            $stmt2 = $pdo->prepare("DELETE FROM usuarios WHERE id = :id AND rol = 'tecnico'");
            $stmt2->execute([':id' => $tecnico_id]);

            // 3. Confirmamos ambos borrados
            $pdo->commit();
            
            $_SESSION['mensaje'] = "Técnico eliminado completamente de la plantilla.";
            
        } catch (PDOException $e) {
            // Si hay error (ej. incidencias asociadas), deshacemos todo
            $pdo->rollBack();
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