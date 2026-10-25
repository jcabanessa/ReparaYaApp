<?php
declare(strict_types=1);
require __DIR__.'/core/bootstrap.php';

$route=(string)($_GET['r']??'inicio');
$routes=[
    'registro'=>[AuthController::class,'register'], 'login'=>[AuthController::class,'login'],
    'salir'=>[AuthController::class,'logout'], 'perfil'=>[AuthController::class,'profile'],
    'cliente_avisos'=>[ClientController::class,'list'], 'cliente_nueva'=>[ClientController::class,'new'],
    'cliente_detalle'=>[ClientController::class,'detail'], 'cliente_cancelar'=>[ClientController::class,'cancel'],
    'admin_avisos'=>[AdminController::class,'list'], 'admin_nueva'=>[AdminController::class,'new'],
    'admin_detalle'=>[AdminController::class,'detail'], 'admin_editar'=>[AdminController::class,'edit'],
    'admin_asignar'=>[AdminController::class,'assign'], 'admin_cancelar'=>[AdminController::class,'cancel'],
    'admin_maestros'=>[AdminController::class,'specialties'], 'admin_servicio_guardar'=>[AdminController::class,'specialtySave'],
    'admin_servicio_eliminar'=>[AdminController::class,'specialtyDelete'], 'admin_tecnico_crear'=>[AdminController::class,'technicianNew'],
    'admin_tecnico_disponibilidad'=>[AdminController::class,'technicianAvailability'],
    'admin_calendario'=>[AdminController::class,'calendar'],
    'tecnico_agenda'=>[TechnicianController::class,'list'], 'tecnico_detalle'=>[TechnicianController::class,'detail'],
    'tecnico_estado'=>[TechnicianController::class,'progress'],
];
try {
    if($route==='inicio') {render('home',['titulo'=>'ReparaYa · Reparaciones a tu alcance']);}
    elseif (isset($routes[$route])) {call_user_func($routes[$route]);}
    else {http_response_code(404);render('message',['titulo'=>'Página no encontrada','mensaje'=>'No existe esa sección.']);}
} catch (Throwable $e) {
    error_log('ReparaYa P2: '.$e->getMessage());http_response_code(500);
    render('message',['titulo'=>'No se pudo completar la operación','mensaje'=>'Ha ocurrido un error interno. Revisa la configuración o el registro de Apache.']);
}
