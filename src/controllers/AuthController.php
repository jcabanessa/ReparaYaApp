<?php
// 1. INICIO DE SESIÓN
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. CONEXIÓN Y CONFIGURACIÓN
require_once __DIR__ . '/../config/db.php'; 

$is_uoc = (strpos($_SERVER['HTTP_HOST'] ?? '', 'uoc.edu') !== false);
$base_url = $is_uoc ? '/~uocx9' : '';

// ==========================================
// 3. SECCIÓN 1: REGISTRO DE USUARIO
// ==========================================
// ==========================================
// 3. SECCIÓN 1: REGISTRO DE USUARIO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registro') {
    
    $nombre   = trim($_POST['nombre'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Permitir que el formulario envíe el rol (si no se envía, por defecto es 'particular')
    $rol = $_POST['rol'] ?? 'particular'; 
    // Si el formulario envía una especialidad la cogemos, si no, le ponemos la 1 por defecto
    $especialidad_id = $_POST['especialidad_id'] ?? 1; 
    
    if (empty($nombre) || empty($email) || empty($password)) {
        $_SESSION['error_registro'] = "Por favor, rellena todos los campos obligatorios.";
        header('Location: ' . $base_url . '/views/auth/registro.php');
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    
    try {
        // 1. Iniciamos la transacción (fundamental para relaciones 1:1)
        $pdo->beginTransaction();
        
        // 2. Insertamos siempre en la tabla principal de usuarios
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, telefono, password, rol) VALUES (:nombre, :email, :telefono, :password, :rol)");
        $stmt->execute([
            ':nombre'   => $nombre,
            ':email'    => $email,
            ':telefono' => $telefono,
            ':password' => $passwordHash,
            ':rol'      => $rol
        ]);
        
        // 3. Si el rol es técnico, insertamos también en la tabla tecnicos
        if ($rol === 'tecnico') {
            $nuevo_usuario_id = $pdo->lastInsertId(); // Cogemos el ID que MySQL le acaba de dar
            
            $stmtTecnico = $pdo->prepare("INSERT INTO tecnicos (usuario_id, nombre_completo, especialidad_id, disponible) VALUES (:usuario_id, :nombre_completo, :especialidad_id, 1)");
            $stmtTecnico->execute([
                ':usuario_id'      => $nuevo_usuario_id,
                ':nombre_completo' => $nombre,
                ':especialidad_id' => $especialidad_id
            ]);
        }
        
        // 4. Si todo ha ido bien, confirmamos y guardamos en la base de datos
        $pdo->commit();
        
        $_SESSION['exito_login'] = "¡Cuenta creada con éxito! Ya puedes iniciar sesión.";
        header('Location: ' . $base_url . '/views/auth/login.php');
        exit();
        
    } catch (PDOException $e) {
        // Si hay algún error (como un correo duplicado), deshacemos todo
        $pdo->rollBack();
        
        if ($e->getCode() == 23000) {
            $_SESSION['error_registro'] = "El correo electrónico ya está registrado en la plataforma.";
        } else {
            $_SESSION['error_registro'] = "Error al registrar el usuario: " . $e->getMessage();
        }
        header('Location: ' . $base_url . '/views/auth/registro.php');
        exit();
    }
}

// ==========================================
// 4. SECCIÓN 2: INICIO DE SESIÓN (LOGIN)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'login') {
    
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();
    
    // Verificación de existencia del usuario y contraseña Bcrypt
    if ($usuario && password_verify($password, $usuario['password'])) {
        
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['rol']        = $usuario['rol'];
        
        if ($usuario['rol'] === 'admin') {
        header('Location: ' . $base_url . '/views/admin/panel.php');
        } else {
             header('Location: ' . $base_url . '/index.php');
            }
            exit();
        
    } else {
        // Guardamos el error específico en la sesión para mostrarlo en el HTML
        $_SESSION['error_login'] = "El correo electrónico o la contraseña son incorrectos.";
        header('Location: ' . $base_url . '/views/auth/login.php');
        exit();
    }
}

// ==========================================
// 5. SECCIÓN 3: CERRAR SESIÓN (LOGOUT)
// ==========================================
if (isset($_GET['accion']) && $_GET['accion'] === 'logout') {
    session_destroy();
    header('Location: ' . $base_url . '/index.php');
    exit();
}