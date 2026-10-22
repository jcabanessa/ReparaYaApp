<?php
// 1. INICIO DE SESIÓN
// Iniciamos o recuperamos la sesión de PHP para poder guardar datos del usuario logueado.
session_start();

// 2. CONEXIÓN A LA BASE DE DATOS
// Importamos el archivo centralizado de conexión PDO (Patrón MVC).
require_once __DIR__ . '/../config/db.php'; 

// ==========================================
// 3. SECCIÓN 1: REGISTRO DE USUARIO
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registro') {
    
    $nombre   = trim($_POST['nombre']);
    $email    = trim($_POST['email']);
    $telefono = trim($_POST['telefono'] ?? '');
    $password = $_POST['password'];
    
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $rol = 'particular'; 
    
    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, telefono, password, rol) VALUES (:nombre, :email, :telefono, :password, :rol)");
        $stmt->execute([
            ':nombre'   => $nombre,
            ':email'    => $email,
            ':telefono' => $telefono,
            ':password' => $passwordHash,
            ':rol'      => $rol
        ]);
        
        header('Location: /views/auth/login.php');
        exit;
        
    } catch (PDOException $e) {
        // Código 23000 en MySQL indica entrada duplicada (clave única duplicada como el email)
        if ($e->getCode() == 23000) {
            $error = "El correo electrónico introducido ya está registrado en la plataforma.";
        } else {
            $error = "Error al registrar el usuario. Inténtalo de nuevo.";
        }
    }
}

// ==========================================
// 4. SECCIÓN 2: INICIO DE SESIÓN (LOGIN)
// ==========================================
// Comprobamos si la petición es por POST y la acción enviada es 'login'.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'login') {
    
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Buscamos en la base de datos al usuario que coincida con el email introducido
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch();
    
    // VALIDACIÓN DE SEGURIDAD:
    // 1. Verificamos que el usuario realmente exista en la base de datos.
    // 2. Usamos password_verify() para comprobar si la contraseña escrita coincide con el hash cifrado.
    if ($usuario && password_verify($password, $usuario['password'])) {
        
        // Creamos las variables de sesión que mantendrán al usuario conectado
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['rol']      = $usuario['rol'];
        
        // Redirigimos al usuario a la página de inicio (el menú cambiará automáticamente)
        header('Location: ../../index.php');
        exit;
        
    } else {
        // Si las credenciales fallan, redirigimos de vuelta al login añadiendo un parámetro de error por URL.
       header('Location: /views/auth/login.php?error=1');
        exit;
    }
}

// ==========================================
// 5. SECCIÓN 3: CERRAR SESIÓN (LOGOUT)
// ==========================================
// Si el usuario hace clic en el botón de cerrar sesión, se activa esta acción por GET.
if (isset($_GET['accion']) && $_GET['accion'] === 'logout') {
    
    session_destroy(); // Destruimos por completo la sesión activa
    
    // Redirigimos al usuario a la página de inicio como invitado anónimo
    header('Location: /index.php');
        exit;
}