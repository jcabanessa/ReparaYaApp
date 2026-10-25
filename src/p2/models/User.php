<?php
declare(strict_types=1);
class User {
    public static function byId(int $id): ?array {
        $s = db()->prepare('SELECT id,nombre,email,rol,telefono,password FROM usuarios WHERE id=?'); $s->execute([$id]); return $s->fetch() ?: null;
    }
    public static function byEmail(string $email): ?array {
        $s = db()->prepare('SELECT * FROM usuarios WHERE email=?'); $s->execute([$email]); return $s->fetch() ?: null;
    }
    public static function allClients(): array { return db()->query("SELECT id,nombre,email FROM usuarios WHERE rol='particular' ORDER BY nombre")->fetchAll(); }
    public static function register(string $nombre, string $email, string $password, string $telefono): int {
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10 || strlen($telefono) > 20) {
            throw new DomainException('Datos inválidos. Nombre mínimo 2 caracteres, email válido y contraseña de al menos 10 caracteres.');
        }
        if (self::byEmail($email)) { throw new DomainException('El correo ya está registrado.'); }
        $s = db()->prepare("INSERT INTO usuarios (nombre,email,password,rol,telefono) VALUES (?,?,?,'particular',?)");
        $s->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT), $telefono]); return (int)db()->lastInsertId();
    }
    public static function updateProfile(int $id, string $nombre, string $email, string $telefono, string $actual, string $nueva): void {
        $u = self::byId($id);
        if (!$u || mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($telefono) > 20) throw new DomainException('Datos de perfil inválidos.');
        $otro = self::byEmail($email);
        if ($otro && (int)$otro['id'] !== $id) throw new DomainException('Ese correo ya está en uso.');
        if ($nueva !== '') {
            if (!password_verify($actual, $u['password']) || strlen($nueva) < 10) throw new DomainException('Para cambiar la contraseña indica la anterior y una nueva de al menos 10 caracteres.');
            $s = db()->prepare('UPDATE usuarios SET nombre=?,email=?,telefono=?,password=? WHERE id=?');
            $s->execute([$nombre,$email,$telefono,password_hash($nueva,PASSWORD_DEFAULT),$id]);
        } else {
            $s = db()->prepare('UPDATE usuarios SET nombre=?,email=?,telefono=? WHERE id=?'); $s->execute([$nombre,$email,$telefono,$id]);
        }
    }
}
