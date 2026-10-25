<?php
declare(strict_types=1);
class ClientController {
    public static function list():void { $u=require_role('particular');render('cliente_lista',['titulo'=>'Mis avisos','incidencias'=>Incident::forClient((int)$u['id'])]); }
    public static function new():void {
        $u=require_role('particular');
        if (!post()) {render('incidencia_form',['titulo'=>'Nueva solicitud','especialidades'=>Specialty::all(),'accion'=>'cliente_nueva']);return;}
        check_csrf();
        try { $id=Incident::create((int)$u['id'],(int)$u['id'],$_POST);flash('ok','Solicitud registrada.');redirect('cliente_detalle',['id'=>$id]); }
        catch (DomainException $e) {fail_form($e->getMessage(),'cliente_nueva');}
    }
    public static function detail():void {
        $u=require_role('particular');$id=int_param('id');$inc=Incident::byId($id);
        if (!$inc || (int)$inc['cliente_id']!==(int)$u['id']) {http_response_code(404);render('message',['titulo'=>'Aviso no encontrado','mensaje'=>'No existe o no tienes acceso.']);return;}
        render('incidencia_detalle',['titulo'=>'Detalle de aviso','incidencia'=>$inc,'historial'=>Incident::history($id)]);
    }
    public static function cancel():void {
        $u=require_role('particular');if(!post()){http_response_code(405);return;}check_csrf();$id=int_param('id');
        try {Incident::cancel($id,(int)$u['id'],'particular');flash('ok','Solicitud cancelada correctamente.');}
        catch (DomainException $e){flash('error',$e->getMessage());}
        redirect('cliente_detalle',['id'=>$id]);
    }
}
