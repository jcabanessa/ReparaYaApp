<?php
session_start();

require_once __DIR__ . '/../../config/db.php';

// Solo pueden acceder los administradores
if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ' . $base_url . '/views/auth/login.php');
    exit;
}

$mensaje = '';
$error = '';

// Crear cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || $email === '' || $telefono === '' || $password === '') {
        $error = 'Todos los campos son obligatorios.';
    } else {
        try {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nombre, email, telefono, password, rol)
                 VALUES (:nombre, :email, :telefono, :password, 'particular')"
            );

            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':telefono' => $telefono,
                ':password' => $passwordHash
            ]);

            header('Location: ' . $base_url . '/views/admin/clientes.php?mensaje=creado');
            exit;
        } catch (PDOException $e) {
            $error = $e->getCode() == 23000
                ? 'Ese correo electrónico ya está registrado.'
                : 'No se ha podido crear el cliente.';
        }
    }
}

// Eliminar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id > 0) {
        $stmt = $pdo->prepare(
            "DELETE FROM usuarios WHERE id = :id AND rol = 'particular'"
        );
        $stmt->execute([':id' => $id]);

        header('Location: ' . $base_url . '/views/admin/clientes.php?mensaje=eliminado');
        exit;
    }
}

// Editar cliente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar') {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');

    if ($id <= 0 || $nombre === '' || $email === '' || $telefono === '') {
        $error = 'Revisa los datos del formulario.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "UPDATE usuarios
                 SET nombre = :nombre, email = :email, telefono = :telefono
                 WHERE id = :id AND rol = 'particular'"
            );

            $stmt->execute([
                ':nombre' => $nombre,
                ':email' => $email,
                ':telefono' => $telefono,
                ':id' => $id
            ]);

            header('Location: ' . $base_url . '/views/admin/clientes.php?mensaje=editado');
            exit;
        } catch (PDOException $e) {
            $error = $e->getCode() == 23000
                ? 'Ese correo electrónico ya está registrado.'
                : 'No se ha podido actualizar el cliente.';
        }
    }
}

// Mensajes tras las redirecciones
if (isset($_GET['mensaje'])) {
    $mensajes = [
        'creado' => 'Cliente creado correctamente.',
        'editado' => 'Cliente actualizado correctamente.',
        'eliminado' => 'Cliente eliminado correctamente.'
    ];

    $mensaje = $mensajes[$_GET['mensaje']] ?? '';
}

// Cliente seleccionado para editar
$clienteEditar = null;

if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare(
        "SELECT id, nombre, email, telefono
         FROM usuarios
         WHERE id = :id AND rol = 'particular'"
    );
    $stmt->execute([':id' => (int)$_GET['editar']]);
    $clienteEditar = $stmt->fetch();
}

// Listado de clientes
$stmt = $pdo->query(
    "SELECT id, nombre, email, telefono
     FROM usuarios
     WHERE rol = 'particular'
     ORDER BY nombre ASC"
);
$clientes = $stmt->fetchAll();

require_once __DIR__ . '/../layouts/header.php';
?>

<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Gestión de <span>clientes</span></h1>
            <p class="hero-subtitle">
                Consulta, crea, modifica y elimina perfiles de clientes.
            </p>
        </div>
    </div>

    <?php if ($mensaje !== ''): ?>
        <div class="alert-success">
            <?= htmlspecialchars($mensaje) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert-error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="dashboard-card">
        <h2><?= $clienteEditar ? 'Editar cliente' : 'Crear cliente' ?></h2>

        <form method="POST">
            <input
                type="hidden"
                name="accion"
                value="<?= $clienteEditar ? 'editar' : 'crear' ?>"
            >

            <?php if ($clienteEditar): ?>
                <input
                    type="hidden"
                    name="id"
                    value="<?= (int)$clienteEditar['id'] ?>"
                >
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label" for="nombre">Nombre completo</label>
                <input
                    class="form-control"
                    type="text"
                    id="nombre"
                    name="nombre"
                    required
                    value="<?= htmlspecialchars($clienteEditar['nombre'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Correo electrónico</label>
                <input
                    class="form-control"
                    type="email"
                    id="email"
                    name="email"
                    required
                    value="<?= htmlspecialchars($clienteEditar['email'] ?? '') ?>"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="telefono">Teléfono</label>
                <input
                    class="form-control"
                    type="text"
                    id="telefono"
                    name="telefono"
                    required
                    value="<?= htmlspecialchars($clienteEditar['telefono'] ?? '') ?>"
                >
            </div>

            <?php if (!$clienteEditar): ?>
                <div class="form-group">
                    <label class="form-label" for="password">Contraseña inicial</label>
                    <input
                        class="form-control"
                        type="password"
                        id="password"
                        name="password"
                        required
                    >
                </div>
            <?php endif; ?>

            <button type="submit" class="btn-submit">
                <?= $clienteEditar ? 'Guardar cambios' : 'Crear cliente' ?>
            </button>

            <?php if ($clienteEditar): ?>
                <a href="<?= $base_url ?>/views/admin/clientes.php">
                    Cancelar edición
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="dashboard-card">
        <h2>Clientes registrados</h2>

        <?php if (empty($clientes)): ?>
            <p>No hay clientes registrados.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <div class="table-responsive clientes-table">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr>
                                    <td><?= htmlspecialchars($cliente['nombre']) ?></td>
                                    <td><?= htmlspecialchars($cliente['email']) ?></td>
                                    <td><?= htmlspecialchars($cliente['telefono'] ?? '') ?></td>
                                    <td class="action-cell">
                                        <div class="action-group">
                                            <a
                                                class="btn-editar"
                                                href="<?= $base_url ?>/views/admin/clientes.php?editar=<?= (int)$cliente['id'] ?>"
                                            >
                                                Editar
                                            </a>

                                            <form
                                                method="POST"
                                                onsubmit="return confirm('¿Seguro que quieres eliminar este cliente?');"
                                            >
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id" value="<?= (int)$cliente['id'] ?>">

                                                <button type="submit" class="btn-eliminar">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>