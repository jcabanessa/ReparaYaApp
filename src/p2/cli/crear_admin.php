<?php
declare(strict_types=1);
if (PHP_SAPI!=='cli'){http_response_code(403);exit;}
require dirname(__DIR__).'/core/bootstrap.php';
echo "Crear cuenta administradora ReparaYa\n";
echo "Nombre: ";$nombre=trim((string)fgets(STDIN));
echo "Email: ";$email=strtolower(trim((string)fgets(STDIN)));
echo "Contraseña (mínimo 10 caracteres; se mostrará al escribir): ";$pass=trim((string)fgets(STDIN));
if (strlen($nombre)<2 || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($pass)<10){fwrite(STDERR,"Datos inválidos.\n");exit(1);}
if (User::byEmail($email)){fwrite(STDERR,"Ese correo ya existe.\n");exit(1);}
$stmt=db()->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,'admin')");
$stmt->execute([$nombre,$email,password_hash($pass,PASSWORD_DEFAULT)]);
echo "Cuenta administradora creada.\n";
