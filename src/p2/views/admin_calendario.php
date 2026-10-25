<?php
$start=$modo==='mes'?$fecha->modify('first day of this month')->modify('monday this week'):($modo==='semana'?$fecha->modify('monday this week'):$fecha);
$end=$modo==='mes'?$fecha->modify('last day of this month')->modify('sunday this week'):($modo==='semana'?$start->modify('+6 days'):$fecha);
$prev=$modo==='mes'?$fecha->modify('-1 month'):($modo==='semana'?$fecha->modify('-7 days'):$fecha->modify('-1 day'));
$next=$modo==='mes'?$fecha->modify('+1 month'):($modo==='semana'?$fecha->modify('+7 days'):$fecha->modify('+1 day'));
$grouped=[];
foreach($incidencias as $i){$dia=substr($i['fecha_servicio'],0,10);$grouped[$dia][]=$i;}
?>
<div class="toolbar"><div class="controls"><a class="button secondary" href="<?=h(url('admin_calendario',['modo'=>$modo,'fecha'=>$prev->format('Y-m-d')]))?>">‹ Anterior</a>
<a class="button secondary" href="<?=h(url('admin_calendario',['modo'=>$modo,'fecha'=>date('Y-m-d')]))?>">Hoy</a>
<a class="button secondary" href="<?=h(url('admin_calendario',['modo'=>$modo,'fecha'=>$next->format('Y-m-d')]))?>">Siguiente ›</a></div>
<div class="controls"><?php foreach(['mes'=>'Mes','semana'=>'Semana','dia'=>'Día'] as $id=>$nombre):?><a class="button <?=$id===$modo?'':'secondary'?>" href="<?=h(url('admin_calendario',['modo'=>$id,'fecha'=>$fecha->format('Y-m-d')]))?>"><?=h($nombre)?></a><?php endforeach;?></div></div>
<p><strong><?=h($start->format('d/m/Y'))?> – <?=h($end->format('d/m/Y'))?></strong> · <span class="standard">■ Estándar</span> · <span class="urgent">■ Urgente</span> · Cada evento muestra también el estado.</p>
<?php if($modo!=='dia'):?>
<div class="calendar"><div class="calendar-head">Lunes</div><div class="calendar-head">Martes</div><div class="calendar-head">Miércoles</div><div class="calendar-head">Jueves</div><div class="calendar-head">Viernes</div><div class="calendar-head">Sábado</div><div class="calendar-head">Domingo</div>
<?php for($dia=$start;$dia<=$end;$dia=$dia->modify('+1 day')):?>
<div class="calendar-day <?= $dia->format('m')!==$fecha->format('m') && $modo==='mes'?'outside':'' ?>"><div class="day-num"><a href="<?=h(url('admin_calendario',['modo'=>'dia','fecha'=>$dia->format('Y-m-d')]))?>"><?=h($dia->format('j'))?></a></div>
<?php foreach(($grouped[$dia->format('Y-m-d')]??[]) as $i):?><a class="cal-event <?=$i['tipo_urgencia']==='Urgente'?'urgent-event':'standard-event'?>" href="<?=h(url('admin_detalle',['id'=>$i['id']]))?>"><strong><?=h(substr($i['fecha_servicio'],11,5))?> <?=h($i['localizador'])?></strong><small><?=h($i['tipo_urgencia'])?> · <?=h($i['estado'])?></small></a><?php endforeach;?></div>
<?php endfor;?></div>
<?php else:?>
<section class="card"><h2>Agenda del <?=h($fecha->format('d/m/Y'))?></h2>
<?php if(empty($grouped[$fecha->format('Y-m-d')])):?><p>No hay avisos para este día.</p><?php else:?>
<?php foreach($grouped[$fecha->format('Y-m-d')] as $i):?><p><a class="cal-event <?=$i['tipo_urgencia']==='Urgente'?'urgent-event':'standard-event'?>" href="<?=h(url('admin_detalle',['id'=>$i['id']]))?>"><?=h(substr($i['fecha_servicio'],11,5))?> · <?=h($i['localizador'])?> · <?=h($i['nombre_especialidad'])?> · <?=h($i['tipo_urgencia'])?> · Estado: <?=h($i['estado'])?></a></p><?php endforeach;?>
<?php endif;?></section><?php endif;?>
