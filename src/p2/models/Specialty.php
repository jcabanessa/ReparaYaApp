<?php
declare(strict_types=1);
class Specialty {
    public static function all(): array { return db()->query('SELECT * FROM especialidades ORDER BY nombre_especialidad')->fetchAll(); }
    public static function byId(int $id): ?array { $s=db()->prepare('SELECT * FROM especialidades WHERE id=?');$s->execute([$id]);return $s->fetch()?:null; }
    public static function save(int $id, string $nombre, string $precio): void {
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 50 || !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $precio)) throw new DomainException('Especialidad o precio inválido. Usa números positivos con máximo dos decimales.');
        if ($id) { $s=db()->prepare('UPDATE especialidades SET nombre_especialidad=?,precio_base=? WHERE id=?');$s->execute([$nombre,$precio,$id]); }
        else { $s=db()->prepare('INSERT INTO especialidades (nombre_especialidad,precio_base) VALUES (?,?)');$s->execute([$nombre,$precio]); }
    }
    public static function remove(int $id): void {
        try { $s=db()->prepare('DELETE FROM especialidades WHERE id=?');$s->execute([$id]); }
        catch (PDOException) { throw new DomainException('No puede eliminarse una especialidad utilizada por técnicos o incidencias.'); }
    }
}
