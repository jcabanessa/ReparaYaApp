<div class="two-col"><section class="card"><h2>Especialidades y precios</h2>
<form method="post" action="<?=h(url('admin_servicio_guardar'))?>" class="form"><?=csrf_field()?>
<label>Nombre de servicio <input name="nombre" required minlength="2" maxlength="50" placeholder="Fontanería"></label>
<label>Precio base (€) <input name="precio" required type="number" min="0" step="0.01" placeholder="35.00"></label><button type="submit">Añadir servicio</button></form>
<?php foreach($especialidades as $e):?><div class="item"><form method="post" action="<?=h(url('admin_servicio_guardar'))?>" class="form compact"><?=csrf_field()?>
<input type="hidden" name="id" value="<?=h($e['id'])?>"><label>Nombre <input name="nombre" value="<?=h($e['nombre_especialidad'])?>" required></label>
<label>Precio € <input name="precio" type="number" min="0" step="0.01" value="<?=h($e['precio_base'])?>" required></label><button type="submit">Actualizar</button></form>
<form method="post" action="<?=h(url('admin_servicio_eliminar'))?>" onsubmit="return confirm('¿Eliminar especialidad no utilizada?');"><?=csrf_field()?><input type="hidden" name="id" value="<?=h($e['id'])?>"><button class="danger" type="submit">Eliminar</button></form></div><?php endforeach;?></section>
<section class="card"><h2>Dar de alta a un técnico</h2><form method="post" action="<?=h(url('admin_tecnico_crear'))?>" class="form"><?=csrf_field()?>
<label>Nombre <input name="nombre" required minlength="2"></label><label>Correo <input type="email" name="email" required></label><label>Contraseña inicial (mínimo 10 caracteres) <input type="password" name="password" minlength="10" required></label>
<label>Especialidad <select name="especialidad_id" required><option value="">Seleccionar</option><?php foreach($especialidades as $e):?><option value="<?=h($e['id'])?>"><?=h($e['nombre_especialidad'])?></option><?php endforeach;?></select></label><button type="submit">Crear técnico</button></form>
<h3>Técnicos registrados</h3><?php foreach($tecnicos as $t):?><div class="item"><strong><?=h($t['nombre_completo'])?></strong><p><?=h($t['nombre_especialidad'])?> · <?=h($t['email'])?> · <?= $t['disponible']?'Disponible':'No disponible' ?></p>
<form method="post" action="<?=h(url('admin_tecnico_disponibilidad'))?>"><?=csrf_field()?><input type="hidden" name="id" value="<?=h($t['id'])?>"><input type="hidden" name="disponible" value="<?= $t['disponible']?'0':'1' ?>"><button class="secondary" type="submit"><?= $t['disponible']?'Marcar no disponible':'Marcar disponible' ?></button></form></div><?php endforeach;?></section></div>
