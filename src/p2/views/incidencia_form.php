<?php $edit=isset($incidencia);$isAdmin=me()['rol']==='admin';$destino=$edit?url('admin_editar',['id'=>$incidencia['id']]):url($accion); ?>
<form class="card form" method="post" action="<?=h($destino)?>"><?=csrf_field()?>
<?php if($isAdmin&&!$edit):?><label>Cliente particular <select name="cliente_id" required><option value="">Selecciona cliente</option><?php foreach($clientes as $c):?><option value="<?=h($c['id'])?>"><?=h($c['nombre'].' · '.$c['email'])?></option><?php endforeach;?></select></label><?php endif;?>
<div class="two-col"><label>Especialidad <select name="especialidad_id" required><option value="">Elige un servicio</option>
<?php foreach($especialidades as $e): ?><option value="<?=h($e['id'])?>" <?= $edit&&(int)$incidencia['especialidad_id']===(int)$e['id']?'selected':'' ?>><?=h($e['nombre_especialidad'])?> · <?=h($e['precio_base'])?> €</option><?php endforeach; ?></select></label>
<label>Urgencia <select name="tipo_urgencia" required><option value="Estándar" <?= $edit && $incidencia['tipo_urgencia']==='Estándar'?'selected':'' ?>>Estándar</option><option value="Urgente" <?= $edit&&$incidencia['tipo_urgencia']==='Urgente'?'selected':'' ?>>Urgente</option></select></label></div>
<div class="two-col"><label>Fecha y hora deseadas <input type="datetime-local" name="fecha_servicio" required value="<?= $edit?h(str_replace(' ','T',substr($incidencia['fecha_servicio'],0,16))):'' ?>"></label>
<label>Franja horaria <select name="franja_horaria" required><option value="">Selecciona franja</option><?php foreach(['Mañana 09:00-13:00','Tarde 15:00-19:00','Noche 19:00-22:00'] as $franja):?><option <?= $edit&&$incidencia['franja_horaria']===$franja?'selected':'' ?>><?=h($franja)?></option><?php endforeach;?></select></label></div>
<div class="two-col"><label>Dirección completa <input name="direccion" required minlength="5" maxlength="255" value="<?=h($incidencia['direccion']??'')?>"></label>
<label>Teléfono de contacto <input type="tel" name="telefono_contacto" required minlength="6" maxlength="20" value="<?=h($incidencia['telefono_contacto']??'')?>"></label></div>
<label>Descripción de la avería <textarea name="descripcion" required minlength="10" maxlength="5000" rows="5"><?=h($incidencia['descripcion']??'')?></textarea></label>
<p class="muted">Estándar: al menos 48 horas de antelación. Urgente: fecha futura, incluso con menos de 48 horas. Se validará nuevamente en PHP.</p>
<button type="submit"><?= $edit?'Guardar cambios':'Registrar solicitud' ?></button></form>
