<?php
declare(strict_types=1);
class TechnicianController {
    private static function identity():array {
        $u=require_role('tecnico');$t=Technician::byUser((int)$u['id']);
        if(!$t){http_response_code(403);render('message',['titulo'=>'Sin ficha técnica','mensaje'=>'Consulta al administrador.']);exit;}
        return [$u,$t];
    }
    public static function list():void {[, $t]=self::identity();render('tecnico_lista',['titulo'=>'Mi agenda','incidencias'=>Incident::forTech((int)$t['id'])]);}
    public static function detail():void {
        [, $t]=self::identity();$i=Incident::byId(int_param('id'));
        if(!$i || (int)$i['tecnico_id']!==(int)$t['id']){http_response_code(404);render('message',['titulo'=>'Aviso no encontrado','mensaje'=>'No tienes acceso a esta incidencia.']);return;}
        render('incidencia_detalle',['titulo'=>'Intervención asignada','incidencia'=>$i,'historial'=>Incident::history((int)$i['id'])]);
    }
    public static function progress():void {
        [$u,$t]=self::identity();if(!post()){http_response_code(405);return;}check_csrf();$id=int_param('id');
        try {Incident::progress($id,(int)$t['id'],(int)$u['id'],(string)($_POST['destino']??''));flash('ok','Estado actualizado.');}
        catch(DomainException $e){flash('error',$e->getMessage());}
        redirect('tecnico_detalle',['id'=>$id]);
    }
}
