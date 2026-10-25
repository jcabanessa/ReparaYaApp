<?php
declare(strict_types=1);
class Incident {
    public static function byId(int $id): ?array {
        $s=db()->prepare('SELECT i.*,u.nombre AS cliente_nombre,t.nombre_completo AS tecnico_nombre,e.nombre_especialidad FROM incidencias i INNER JOIN usuarios u ON u.id=i.cliente_id LEFT JOIN tecnicos t ON t.id=i.tecnico_id INNER JOIN especialidades e ON e.id=i.especialidad_id WHERE i.id=?');
        $s->execute([$id]);return $s->fetch()?:null;
    }
    public static function forClient(int $clientId): array {
        $s=db()->prepare('SELECT i.*,e.nombre_especialidad FROM incidencias i JOIN especialidades e ON e.id=i.especialidad_id WHERE i.cliente_id=? ORDER BY i.fecha_servicio DESC');$s->execute([$clientId]);return $s->fetchAll();
    }
    public static function forTech(int $techId): array {
        $s=db()->prepare('SELECT i.*,e.nombre_especialidad,u.nombre AS cliente_nombre FROM incidencias i JOIN especialidades e ON e.id=i.especialidad_id JOIN usuarios u ON u.id=i.cliente_id WHERE i.tecnico_id=? ORDER BY i.fecha_servicio DESC');$s->execute([$techId]);return $s->fetchAll();
    }
    public static function all(?string $estado=null): array {
        $sql='SELECT i.*,e.nombre_especialidad,u.nombre AS cliente_nombre,t.nombre_completo AS tecnico_nombre FROM incidencias i JOIN especialidades e ON e.id=i.especialidad_id JOIN usuarios u ON u.id=i.cliente_id LEFT JOIN tecnicos t ON t.id=i.tecnico_id';
        $args=[];
        if ($estado !== null && $estado !== '') { $sql.=' WHERE i.estado=?';$args[]=$estado; }
        $sql.=' ORDER BY i.fecha_servicio DESC';$s=db()->prepare($sql);$s->execute($args);return $s->fetchAll();
    }
    public static function history(int $id): array {
        $s=db()->prepare('SELECT h.*,u.nombre AS autor FROM historial_estados h JOIN usuarios u ON h.usuario_id=u.id WHERE h.incidencia_id=? ORDER BY h.created_at ASC,h.id ASC');$s->execute([$id]);return $s->fetchAll();
    }
    public static function validateFields(array $p): array {
        $tipo=(string)($p['tipo_urgencia']??'');$fecha=valid_date((string)($p['fecha_servicio']??''));
        $franja=trim((string)($p['franja_horaria']??''));$telefono=trim((string)($p['telefono_contacto']??''));
        $descripcion=trim((string)($p['descripcion']??''));$direccion=trim((string)($p['direccion']??''));
        $especialidad=(int)($p['especialidad_id']??0);
        if (!in_array($tipo,['Estándar','Urgente'],true)||!$fecha||!Specialty::byId($especialidad)||$franja===''||mb_strlen($franja)>50||strlen($telefono)<6||strlen($telefono)>20||mb_strlen($descripcion)<10||mb_strlen($descripcion)>5000||mb_strlen($direccion)<5||mb_strlen($direccion)>255) {
            throw new DomainException('Completa fecha, franja, teléfono, dirección (mínimo 5 caracteres), descripción (mínimo 10), urgencia y especialidad.');
        }
        $ahora=new DateTimeImmutable('now',new DateTimeZone('Europe/Madrid'));
        if ($fecha<=$ahora) throw new DomainException('La fecha de la solicitud debe ser futura.');
        if ($tipo==='Estándar' && $fecha<$ahora->modify('+48 hours')) throw new DomainException('Una solicitud estándar requiere al menos 48 horas de antelación.');
        return [$especialidad,$descripcion,$direccion,$fecha->format('Y-m-d H:i:s'),$tipo,$franja,$telefono];
    }
    public static function create(int $clienteId,int $actorId,array $p): int {
        [$esp,$desc,$dir,$fecha,$tipo,$franja,$telefono]=self::validateFields($p);
        $u=User::byId($clienteId);
        if (!$u || $u['rol']!=='particular') throw new DomainException('Selecciona un cliente particular válido.');
        $especialidad=Specialty::byId($esp);
        $pdo=db();$pdo->beginTransaction();
        try {
            // Formato REP-AAAA-XXXX (12 caracteres), con comprobación de UNIQUE en MySQL.
            $sql='INSERT INTO incidencias (localizador,cliente_id,especialidad_id,descripcion,direccion,fecha_servicio,tipo_urgencia,estado,franja_horaria,telefono_contacto,precio_aplicado) VALUES (?,?,?,?,?,?,?,\'Pendiente\',?,?,?)';
            $stmt=$pdo->prepare($sql);
            $id=0;
            for($attempt=0;$attempt<8;$attempt++) {
                $localizador='REP-'.date('Y').'-'.str_pad((string)random_int(0,9999),4,'0',STR_PAD_LEFT);
                try { $stmt->execute([$localizador,$clienteId,$esp,$desc,$dir,$fecha,$tipo,$franja,$telefono,$especialidad['precio_base']]);$id=(int)$pdo->lastInsertId();break; }
                catch (PDOException $e) { if ($e->getCode()!=='23000') throw $e; }
            }
            if (!$id) throw new DomainException('No se ha podido generar un localizador único.');
            self::log($pdo,$id,null,'Pendiente',$actorId,'Creación de la incidencia');$pdo->commit();return $id;
        } catch (Throwable $e) { if ($pdo->inTransaction())$pdo->rollBack();throw $e; }
    }
    private static function log(PDO $pdo,int $id,?string $before,string $after,int $actor,string $notes=''): void {
        $s=$pdo->prepare('INSERT INTO historial_estados (incidencia_id,estado_anterior,nuevo_estado,usuario_id,observaciones) VALUES (?,?,?,?,?)');$s->execute([$id,$before,$after,$actor,$notes]);
    }
    private static function lock(PDO $pdo,int $id): array {
        $s=$pdo->prepare('SELECT * FROM incidencias WHERE id=? FOR UPDATE');$s->execute([$id]);$row=$s->fetch();if(!$row) throw new DomainException('Incidencia no encontrada.');return $row;
    }
    public static function cancel(int $id,int $actor,string $rol): void {
        $pdo=db();$pdo->beginTransaction();
        try {
            $i=self::lock($pdo,$id);
            if (!in_array($i['estado'],['Pendiente','Asignada'],true)) throw new DomainException('Una incidencia en este estado ya no se puede cancelar.');
            if ($rol==='particular') {
                if ((int)$i['cliente_id']!==$actor) throw new DomainException('Esta incidencia no te pertenece.');
                $fecha=new DateTimeImmutable($i['fecha_servicio'],new DateTimeZone('Europe/Madrid'));
                if ($fecha <= (new DateTimeImmutable('now',new DateTimeZone('Europe/Madrid')))->modify('+48 hours')) throw new DomainException('El cliente solo puede cancelar cuando faltan MÁS de 48 horas.');
            } elseif ($rol!=='admin') throw new DomainException('No tienes permiso para cancelar.');
            $s=$pdo->prepare("UPDATE incidencias SET estado='Cancelada' WHERE id=?");$s->execute([$id]);
            self::log($pdo,$id,$i['estado'],'Cancelada',$actor,'Cancelación solicitada');$pdo->commit();
        } catch(Throwable $e){$pdo->rollBack();throw $e;}
    }
    public static function assign(int $id,int $techId,int $actor): void {
        $pdo=db();$pdo->beginTransaction();
        try {
            $i=self::lock($pdo,$id);$t=Technician::byId($techId);
            if (!in_array($i['estado'],['Pendiente','Asignada'],true)) throw new DomainException('No se puede asignar una incidencia iniciada o cerrada.');
            if (!$t || !(bool)$t['disponible'] || (int)$t['especialidad_id']!==(int)$i['especialidad_id']) throw new DomainException('Selecciona un técnico disponible con la especialidad adecuada.');
            $nuevo='Asignada';$s=$pdo->prepare('UPDATE incidencias SET tecnico_id=?,estado=? WHERE id=?');$s->execute([$techId,$nuevo,$id]);
            self::log($pdo,$id,$i['estado'],$nuevo,$actor,'Asignado al técnico n.º '.$techId);$pdo->commit();
        } catch(Throwable $e){$pdo->rollBack();throw $e;}
    }
    public static function progress(int $id,int $techId,int $actor,string $destino): void {
        $pdo=db();$pdo->beginTransaction();
        try {
            $i=self::lock($pdo,$id);
            if ((int)$i['tecnico_id']!==$techId) throw new DomainException('No puedes modificar incidencias de otro técnico.');
            $permitido=['Asignada'=>'En curso','En curso'=>'Finalizada'];
            if (($permitido[$i['estado']]??null)!==$destino) throw new DomainException('Transición no permitida de '.$i['estado'].' a '.$destino.'.');
            $s=$pdo->prepare('UPDATE incidencias SET estado=? WHERE id=?');$s->execute([$destino,$id]);
            self::log($pdo,$id,$i['estado'],$destino,$actor,'Cambio de estado por el técnico');$pdo->commit();
        } catch(Throwable $e){$pdo->rollBack();throw $e;}
    }
    public static function update(int $id,int $actor,array $fields):void {
        [$esp,$desc,$dir,$fecha,$tipo,$franja,$telefono]=self::validateFields($fields);
        $pdo=db();$pdo->beginTransaction();
        try {
            $i=self::lock($pdo,$id);
            if (!in_array($i['estado'],['Pendiente','Asignada'],true)) throw new DomainException('No se puede editar una incidencia en curso o cerrada.');
            if ((int)$i['especialidad_id']!==$esp && $i['estado']==='Asignada') throw new DomainException('Desasignar primero al técnico antes de cambiar de especialidad.');
            $s=$pdo->prepare('UPDATE incidencias SET especialidad_id=?,descripcion=?,direccion=?,fecha_servicio=?,tipo_urgencia=?,franja_horaria=?,telefono_contacto=? WHERE id=?');$s->execute([$esp,$desc,$dir,$fecha,$tipo,$franja,$telefono,$id]);
            self::log($pdo,$id,$i['estado'],$i['estado'],$actor,'Administrador modificó los datos de la incidencia');$pdo->commit();
        } catch(Throwable $e){$pdo->rollBack();throw $e;}
    }
}
