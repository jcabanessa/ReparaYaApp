<?php
declare(strict_types=1);
class Technician {
    public static function all(): array { return db()->query('SELECT t.*,u.email,e.nombre_especialidad FROM tecnicos t LEFT JOIN usuarios u ON u.id=t.usuario_id LEFT JOIN especialidades e ON e.id=t.especialidad_id ORDER BY t.nombre_completo')->fetchAll(); }
    public static function byId(int $id): ?array { $s=db()->prepare('SELECT * FROM tecnicos WHERE id=?');$s->execute([$id]);return $s->fetch()?:null; }
    public static function byUser(int $id): ?array { $s=db()->prepare('SELECT * FROM tecnicos WHERE usuario_id=?');$s->execute([$id]);return $s->fetch()?:null; }
    public static function forSpecialty(int $id): array { $s=db()->prepare('SELECT * FROM tecnicos WHERE especialidad_id=? AND disponible=1 ORDER BY nombre_completo');$s->execute([$id]);return $s->fetchAll(); }
    public static function create(string $nombre, string $email, string $password, int $especialidad): void {
        if (mb_strlen($nombre)<2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<10 || !Specialty::byId($especialidad)) throw new DomainException('Comprueba nombre, email, contraseña (mínimo 10 caracteres) y especialidad.');
        if (User::byEmail($email)) throw new DomainException('El correo ya existe.');
        $pdo=db();$pdo->beginTransaction();
        try {
            $s=$pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,'tecnico')");$s->execute([$nombre,$email,password_hash($password,PASSWORD_DEFAULT)]);
            $id=(int)$pdo->lastInsertId();
            $s=$pdo->prepare('INSERT INTO tecnicos (usuario_id,nombre_completo,especialidad_id,disponible) VALUES (?,?,?,1)');$s->execute([$id,$nombre,$especialidad]);$pdo->commit();
        } catch (Throwable $e) { $pdo->rollBack();throw $e; }
    }
    public static function availability(int $id, bool $disponible): void {
        $s=db()->prepare('UPDATE tecnicos SET disponible=? WHERE id=?');$s->execute([(int)$disponible,$id]);
    }
}
