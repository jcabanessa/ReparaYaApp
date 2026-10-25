<section class="hero"><div><p class="eyebrow">Tu hogar, en buenas manos</p><h2>Solicita una reparación de forma sencilla</h2><p>ReparaYa conecta a clientes con profesionales de fontanería, electricidad y otros servicios domésticos. Consulta la evolución de cada aviso desde tu panel.</p>
<?php if(!me()): ?><p><a class="button" href="<?=h(url('registro'))?>">Crear una cuenta</a> <a class="button secondary" href="<?=h(url('login'))?>">Ya tengo cuenta</a></p>
<?php elseif(me()['rol']==='particular'): ?><p><a class="button" href="<?=h(url('cliente_nueva'))?>">Solicitar asistencia</a></p>
<?php elseif(me()['rol']==='admin'): ?><p><a class="button" href="<?=h(url('admin_avisos'))?>">Gestionar incidencias</a></p>
<?php else: ?><p><a class="button" href="<?=h(url('tecnico_agenda'))?>">Ver mis intervenciones</a></p><?php endif; ?>
</div></section>
<div class="cards"><article class="card"><h3>1. Describe la avería</h3><p>Selecciona el servicio, la dirección, la fecha deseada, una franja horaria y un teléfono de contacto.</p></article>
<article class="card"><h3>2. Asignamos un técnico</h3><p>El administrador revisa el aviso y asigna a un profesional de la especialidad adecuada.</p></article>
<article class="card"><h3>3. Consulta el progreso</h3><p>Visualiza los estados Pendiente, Asignada, En curso, Finalizada o Cancelada y su historial.</p></article></div>
<section class="card"><h2>¿Cómo funcionan las citas?</h2><p>Las solicitudes <strong>estándar</strong> necesitan al menos 48 horas de antelación. Las <strong>urgentes</strong> pueden solicitarse con menor margen. Los clientes solo pueden cancelar sus avisos Pendientes o Asignados cuando faltan <strong>más de 48 horas</strong> para la cita.</p></section>
