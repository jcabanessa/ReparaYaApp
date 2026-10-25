<?php $rol=me()['rol'];$editable=in_array($incidencia['estado'],['Pendiente','Asignada'],true); ?>
<section class="card"><div class="space-between"><h2><?=h($incidencia['localizador'])?></h2><?=label_estado($incidencia['estado'])?></div>
<dl class="data-grid"><dt>Cliente</dt><dd><?=h($incidencia['cliente_nombre'])?></dd>
<dt>Servicio</dt><dd><?=h($incidencia['nombre_especialidad'])?></dd>
<dt>Urgencia</dt><dd><span class="<?= $incidencia['tipo_urgencia']==='Urgente'?'urgent':'standard' ?>"><?=h($incidencia['tipo_urgencia'])?></span></dd>
<dt>Fecha y hora</dt><dd><?=h(date_service($incidencia['fecha_servicio']))?></dd>
<dt>Franja horaria</dt><dd><?=h($incidencia['franja_horaria'])?></dd>
<dt>Dirección</dt><dd><?=h($incidencia['direccion'])?></dd>
<dt>Teléfono</dt><dd><?=h($incidencia['telefono_contacto'])?></dd>
<dt>Técnico asignado</dt><dd><?=h($incidencia['tecnico_nombre']??'Pendiente de asignación')?></dd>
<dt>Precio aplicado</dt><dd><?=h($incidencia['precio_aplicado'])?> €</dd>
<dt>Descripción</dt><dd><?=nl2br(h($incidencia['descripcion']))?></dd></dl>
<?php if ($rol==='particular'&&$editable):?>
<form method="post" action="<?=h(url('cliente_cancelar',['id'=>$incidencia['id']]))?>" onsubmit="return confirm('¿Cancelar esta solicitud?');"><?=csrf_field()?>
<button class="danger" type="submit">Solicitar cancelación</button><p class="muted">Solo cuando faltan más de 48 horas para la cita. Si no se cumple, el servidor rechazará la acción.</p></form>
<?php endif;?>
<?php if ($rol==='admin'&&$editable):?>
<h3>Administrar incidencia</h3><p><a class="button secondary" href="<?=h(url('admin_editar',['id'=>$incidencia['id']]))?>">Editar datos</a></p>
<form method="post" action="<?=h(url('admin_asignar',['id'=>$incidencia['id']]))?>" class="form compact"><?=csrf_field()?><label>Asignar/reasignar técnico de la especialidad
<select name="tecnico_id" required><option value="">Selecciona</option><?php foreach($tecnicos as $t):?><option value="<?=h($t['id'])?>"><?=h($t['nombre_completo'])?></option><?php endforeach;?></select></label><button type="submit">Guardar asignación</button></form>
<form method="post" action="<?=h(url('admin_cancelar',['id'=>$incidencia['id']]))?>" onsubmit="return confirm('¿Cancelar el aviso?');"><?=csrf_field()?><button class="danger" type="submit">Cancelar incidencia</button></form>
<?php endif;?>
<?php if ($rol==='tecnico'&&in_array($incidencia['estado'],['Asignada','En curso'],true)):?>
<form method="post" action="<?=h(url('tecnico_estado',['id'=>$incidencia['id']]))?>"><?=csrf_field()?><input type="hidden" name="destino" value="<?=h($incidencia['estado']==='Asignada'?'En curso':'Finalizada')?>"><button type="submit"><?= $incidencia['estado']==='Asignada'?'Iniciar intervención':'Marcar como finalizada' ?></button></form>
<?php endif;?>
</section>
<section class="card"><h2>Historial de estados</h2>
<?php if(!$historial):?><p>Sin cambios registrados.</p><?php else: ?><ol class="timeline"><?php foreach($historial as $h):?><li><strong><?=h($h['estado_anterior']??'Inicio')?> → <?=h($h['nuevo_estado'])?></strong><br><small><?=h($h['created_at'])?> · <?=h($h['autor'])?></small><p><?=h($h['observaciones'])?></p></li><?php endforeach;?></ol><?php endif;?></section>
