<?php
/**
 * models/Incidencia.php
 * Modelo para la gestión de incidencias, validaciones de negocio e historial de estados.
 */

class Incidencia {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Genera un código localizador único tipo REP-2026-XXXX
     */
    private function generarLocalizador() {
        $año = date('Y');
        $random = strtoupper(substr(uniqid(), -4));
        return "REP-{$año}-{$random}";
    }

    /**
     * Crea una nueva incidencia validando las reglas de antelación en el servidor.
     */
    public function crear($cliente_id, $especialidad_id, $descripcion, $direccion, $fecha_servicio, $tipo_urgencia) {
        // 1. Validar la regla de las 48 horas para servicios estándar
        if ($tipo_urgencia === 'Estándar') {
            $fechaMinima = new DateTime('+48 hours');
            $fechaSolicitada = new DateTime($fecha_servicio);

            if ($fechaSolicitada < $fechaMinima) {
                throw new Exception("Las solicitudes estándar deben realizarse con un mínimo de 48 horas de antelación.");
            }
        }

        $localizador = $this->generarLocalizador();
        $estadoInicial = 'Pendiente';

        try {
            $this->pdo->beginTransaction();

            // 2. Insertar la incidencia
            $sql = "INSERT INTO incidencias (localizador, cliente_id, especialidad_id, descripcion, direccion, fecha_servicio, tipo_urgencia, estado) 
                    VALUES (:localizador, :cliente_id, :especialidad_id, :descripcion, :direccion, :fecha_servicio, :tipo_urgencia, :estado)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':localizador'     => $localizador,
                ':cliente_id'      => $cliente_id,
                ':especialidad_id' => $especialidad_id,
                ':descripcion'     => $descripcion,
                ':direccion'       => $direccion,
                ':fecha_servicio'  => $fecha_servicio,
                ':tipo_urgencia'   => $tipo_urgencia,
                ':estado'          => $estadoInicial
            ]);

            $incidenciaId = $this->pdo->lastInsertId();

            // 3. Registrar el estado inicial en el historial
            $this->registrarHistorial($incidenciaId, null, $estadoInicial, $cliente_id, 'Creación de la solicitud.');

            $this->pdo->commit();
            return $localizador;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Registra un cambio en el historial de estados
     */
    private function registrarHistorial($incidencia_id, $estado_anterior, $nuevo_estado, $usuario_id, $observaciones = null) {
        $sql = "INSERT INTO historial_estados (incidencia_id, estado_anterior, nuevo_estado, usuario_id, observaciones) 
                VALUES (:incidencia_id, :estado_anterior, :nuevo_estado, :usuario_id, :observaciones)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':incidencia_id'   => $incidencia_id,
            ':estado_anterior' => $estado_anterior,
            ':nuevo_estado'    => $nuevo_estado,
            ':usuario_id'      => $usuario_id,
            ':observaciones'   => $observaciones
        ]);
    }

    /**
     * Cambia el estado de una incidencia validando las reglas de transición del servidor.
     */
    public function cambiarEstado($incidencia_id, $nuevo_estado, $usuario_id, $rol_usuario, $observaciones = null) {
        // Obtener la incidencia actual
        $incidencia = $this->obtenerPorId($incidencia_id);
        if (!$incidencia) {
            throw new Exception("La incidencia no existe.");
        }

        $estadoActual = $incidencia['estado'];

        // Si ya está finalizada o cancelada, no se puede modificar
        if (in_array($estadoActual, ['Finalizada', 'Cancelada'])) {
            throw new Exception("Una incidencia Finalizada o Cancelada no puede volver a modificarse.");
        }

        // Validar transiciones permitidas según el rol y las reglas del enunciado
        $valido = false;

        if ($rol_usuario === 'admin') {
            // El admin puede asignar, cancelar, etc., mientras esté Pendiente o Asignada
            if (in_array($nuevo_estado, ['Asignada', 'Cancelada'])) {
                $valido = true;
            }
        } elseif ($rol_usuario === 'tecnico') {
            // El técnico solo puede mover de Asignada -> En curso -> Finalizada
            if ($estadoActual === 'Asignada' && $nuevo_estado === 'En curso') $valido = true;
            if ($estadoActual === 'En curso' && $nuevo_estado === 'Finalizada') $valido = true;
        } elseif ($rol_usuario === 'particular') {
            // El cliente solo puede cancelar si faltan más de 48h y está Pendiente o Asignada
            if ($nuevo_estado === 'Cancelada' && in_array($estadoActual, ['Pendiente', 'Asignada'])) {
                $fechaServicio = new DateTime($incidencia['fecha_servicio']);
                $limiteCancelacion = new DateTime('+48 hours');
                if ($fechaServicio >= $limiteCancelacion) {
                    $valido = true;
                } else {
                    throw new Exception("Solo se puede cancelar con más de 48 horas de antelación.");
                }
            }
        }

        if (!$valido) {
            throw new Exception("Transición de estado no permitida para tu rol o el estado actual.");
        }

        // Ejecutar el cambio en BD y guardar historial
        try {
            $this->pdo->beginTransaction();

            $sql = "UPDATE incidencias SET estado = :nuevo_estado WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nuevo_estado' => $nuevo_estado,
                ':id'           => $incidencia_id
            ]);

            $this->registrarHistorial($incidencia_id, $estadoActual, $nuevo_estado, $usuario_id, $observaciones);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function obtenerPorId($id) {
        $sql = "SELECT i.*, e.nombre_especialidad, u.nombre as cliente_nombre 
                FROM incidencias i 
                JOIN especialidades e ON i.especialidad_id = e.id 
                JOIN usuarios u ON i.cliente_id = u.id 
                WHERE i.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function obtenerHistorial($incidencia_id) {
        $sql = "SELECT h.*, u.nombre as usuario_nombre 
                FROM historial_estados h 
                JOIN usuarios u ON h.usuario_id = u.id 
                WHERE h.incidencia_id = :incidencia_id 
                ORDER BY h.created_at ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':incidencia_id' => $incidencia_id]);
        return $stmt->fetchAll();
    }
}