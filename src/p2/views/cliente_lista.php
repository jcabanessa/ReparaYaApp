<p><a class="button" href="<?=h(url('cliente_nueva'))?>">+ Solicitar reparación</a></p>
<?php if (!$incidencias):?><section class="card"><p>Aún no tienes avisos.</p></section><?php else: ?>
<div class="table-scroll"><table><thead><tr><th>Código</th><th>Cita</th><th>Servicio</th><th>Urgencia</th><th>Estado</th><th>Detalle</th></tr></thead><tbody>
<?php foreach($incidencias as $i):?><tr><td><?=h($i['localizador'])?></td><td><?=h(date_service($i['fecha_servicio']))?></td><td><?=h($i['nombre_especialidad'])?></td><td><?=h($i['tipo_urgencia'])?></td><td><?=label_estado($i['estado'])?></td><td><a href="<?=h(url('cliente_detalle',['id'=>$i['id']]))?>">Ver aviso</a></td></tr><?php endforeach; ?>
</tbody></table></div><?php endif; ?>
