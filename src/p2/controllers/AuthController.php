<?php
declare(strict_types=1);
class AuthController {
    public static function register():void {
        if (me()) redirect('inicio');
        if (!post()) { render('registro',['titulo'=>'Crear cuenta']);return; }
        check_csrf();
        try {
            $nombre=trim((string)($_POST['nombre']??''));$email=strtolower(trim((string)($_POST['email']??'')));
            $pass=(string)($_POST['password']??'');
            if ($pass!==($_POST['confirmacion']??'')) throw new DomainException('Las contraseñas no coinciden.');
            User::register($nombre,$email,$pass,trim((string)($_POST['telefono']??'')));
            flash('ok','Cuenta creada. Ya puedes iniciar sesión.');redirect('login');
        } catch (DomainException $e){fail_form($e->getMessage(),'registro');}
    }
    public static function login():void {
        if (me()) redirect('inicio');
        if (!post()) { render('login',['titulo'=>'Iniciar sesión']);return; }
        check_csrf();$email=strtolower(trim((string)($_POST['email']??'')));
        $u=User::byEmail($email);
        if (!$u || !password_verify((string)($_POST['password']??''),$u['password'])) fail_form('Correo o contraseña incorrectos.','login');
        session_regenerate_id(true);$_SESSION['usuario']=['id'=>(int)$u['id'],'nombre'=>$u['nombre'],'rol'=>$u['rol']];
        csrf();redirect('inicio');
    }
    public static function logout():void {
        if (!post()) { http_response_code(405); return; }
        check_csrf(); $_SESSION=[];session_regenerate_id(true);flash('ok','Sesión cerrada.');redirect('inicio');
    }
    public static function profile():void {
        $u=require_login();
        if (!post()) { render('perfil',['titulo'=>'Mi perfil','perfil'=>User::byId((int)$u['id'])]);return; }
        check_csrf();
        try {
            User::updateProfile((int)$u['id'],trim((string)($_POST['nombre']??'')),strtolower(trim((string)($_POST['email']??''))),trim((string)($_POST['telefono']??'')),(string)($_POST['actual']??''),(string)($_POST['nueva']??''));
            $_SESSION['usuario']['nombre']=trim((string)$_POST['nombre']);flash('ok','Perfil actualizado.');redirect('perfil');
        } catch (DomainException $e){fail_form($e->getMessage(),'perfil');}
    }
}
