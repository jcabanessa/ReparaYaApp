
<?php

// Registrar un usuario
function registrarUsuario($pdo, $nombre, $email, $telefono, $password, $rol = 'particular') {
    $sql = "INSERT INTO usuarios (nombre, email, telefono, password, rol)
            VALUES (:nombre, :email, :telefono, :password, :rol)";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':nombre' => $nombre,
        ':email' => $email,
        ':telefono' => $telefono,
        ':password' => $password,
        ':rol' => $rol
    ]);
}

// Buscar un usuario por su correo
function obtenerUsuarioPorEmail($pdo, $email) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);

    return $stmt->fetch();
}

// Buscar un usuario por su ID
function obtenerUsuarioPorId($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $id]);

    return $stmt->fetch();
}

// Actualizar los datos de un usuario
function actualizarUsuario($pdo, $id, $nombre, $email, $telefono) {
    $sql = "UPDATE usuarios
            SET nombre = :nombre, email = :email, telefono = :telefono
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);

    return $stmt->execute([
        ':nombre' => $nombre,
        ':email' => $email,
        ':telefono' => $telefono,
        ':id' => $id
    ]);
}

// Eliminar un usuario
function eliminarUsuario($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");

    return $stmt->execute([':id' => $id]);
}
