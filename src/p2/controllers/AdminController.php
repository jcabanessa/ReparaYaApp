<?php
declare(strict_types=1);
class AdminController {
    private static function stateFilter():string { $f=(string)($_GET['estado']??'');return in_array($f,['Pendiente','Asignada','En curso','Finalizada','Cancelada'],true)?$f:''; }
    public static function list():void {require_role('admin');$f=self::stateFilter();render('admin_lista',['titulo'=>'Gestión de incidencias','incidencias'=>Incident::all($f),'estadoFiltro'=>$f]);}
    public static function new():void {
        $u=require_role('admin');
        if (!post()) {render('incidencia_form',['titulo'=>'Crear incidencia','especialidades'=>Specialty::all(),'clientes'=>User::allClients(),'accion'=>'admin_nueva']);return;}
        check_csrf();
        try { $id=Incident::create((int)($_POST['cliente_id']??0),(int)$u['id'],$_POST);flash('ok','Incidencia creada.');redirect('admin_detalle',['id'=>$id]); }
        catch(DomainException $e){fail_form($e->getMessage(),'admin_nueva');}
    }
    public static function detail():void {
        require_role('admin');$id=int_param('id');$i=Incident::byId($id);
        if (!$i){http_response_code(404);render('message',['titulo'=>'No encontrada','mensaje'=>'No existe esa incidencia.']);return;}
        render('incidencia_detalle',['titulo'=>'Gestión del aviso','incidencia'=>$i,'historial'=>Incident::history($id),'tecnicos'=>Technician::forSpecialty((int)$i['especialidad_id'])]);
    }
    public static function edit():void {
        $u=require_role('admin');$id=int_param('id');$i=Incident::byId($id);
        if (!$i) {http_response_code(404);render('message',['titulo'=>'No encontrada','mensaje'=>'Incidencia inexistente']);return;}
        if (!post()) {render('incidencia_form',['titulo'=>'Editar aviso','especialidades'=>Specialty::all(),'incidencia'=>$i,'accion'=>'admin_editar']);return;}
        check_csrf();
        try {Incident::update($id,(int)$u['id'],$_POST);flash('ok','Incidencia actualizada.');redirect('admin_detalle',['id'=>$id]);}
        catch(DomainException $e){fail_form($e->getMessage(),'admin_editar',['id'=>$id]);}
    }
    public static function assign():void {
        $u=require_role('admin');if(!post()){http_response_code(405);return;}check_csrf();$id=int_param('id');
        try {Incident::assign($id,(int)($_POST['tecnico_id']??0),(int)$u['id']);flash('ok','Técnico asignado.');}
        catch(DomainException $e){flash('error',$e->getMessage());}
        redirect('admin_detalle',['id'=>$id]);
    }
    public static function cancel():void {
        $u=require_role('admin');if(!post()){http_response_code(405);return;}check_csrf();$id=int_param('id');
        try {Incident::cancel($id,(int)$u['id'],'admin');flash('ok','Incidencia cancelada.');}
        catch(DomainException $e){flash('error',$e->getMessage());}
        redirect('admin_detalle',['id'=>$id]);
    }
    public static function specialties():void {require_role('admin');render('admin_maestros',['titulo'=>'Técnicos y servicios','especialidades'=>Specialty::all(),'tecnicos'=>Technician::all()]);}
    public static function specialtySave():void {
        require_role('admin');if(!post()){http_response_code(405);return;}check_csrf();
        try {Specialty::save((int)($_POST['id']??0),trim((string)($_POST['nombre']??'')),str_replace(',','.',trim((string)($_POST['precio']??''))));flash('ok','Servicio guardado.');}
        catch(DomainException $e){flash('error',$e->getMessage());}redirect('admin_maestros');
    }
    public static function specialtyDelete():void {
        require_role('admin');if(!post()){http_response_code(405);return;}check_csrf();
        try {Specialty::remove((int)($_POST['id']??0));flash('ok','Servicio eliminado.');}catch(DomainException $e){flash('error',$e->getMessage());}redirect('admin_maestros');
    }
    public static function technicianNew():void {
        require_role('admin');if(!post()){http_response_code(405);return;}check_csrf();
        try {Technician::create(trim((string)($_POST['nombre']??'')),strtolower(trim((string)($_POST['email']??''))),(string)($_POST['password']??''),(int)($_POST['especialidad_id']??0));flash('ok','Técnico creado con acceso a la aplicación.');}
        catch(DomainException $e){flash('error',$e->getMessage());}redirect('admin_maestros');
    }
    public static function technicianAvailability():void {
        require_role('admin');if(!post()){http_response_code(405);return;}check_csrf();
        Technician::availability((int)($_POST['id']??0),(string)($_POST['disponible']??'')==='1');flash('ok','Disponibilidad actualizada.');redirect('admin_maestros');
    }
    public static function calendar():void {
        require_role('admin');$modo=(string)($_GET['modo']??'mes');if(!in_array($modo,['mes','semana','dia'],true))$modo='mes';
        $raw=(string)($_GET['fecha']??date('Y-m-d'));$fecha=DateTimeImmutable::createFromFormat('!Y-m-d',$raw);
        if(!$fecha || $fecha->format('Y-m-d')!==$raw)$fecha=new DateTimeImmutable('today');
        $todos=Incident::all();render('admin_calendario',['titulo'=>'Calendario de avisos','modo'=>$modo,'fecha'=>$fecha,'incidencias'=>$todos]);
    }
}
