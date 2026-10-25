<form class="card form narrow" method="post" action="<?=h(url('perfil'))?>"><?=csrf_field()?>
<label>Nombre <input name="nombre" required minlength="2" maxlength="100" value="<?=h($perfil['nombre'])?>"></label>
<label>Correo <input type="email" name="email" required maxlength="100" value="<?=h($perfil['email'])?>"></label>
<label>Teléfono <input name="telefono" maxlength="20" value="<?=h($perfil['telefono'])?>"></label>
<fieldset><legend>Cambiar contraseña (opcional)</legend>
<label>Contraseña actual <input type="password" name="actual" autocomplete="current-password"></label>
<label>Nueva contraseña <input type="password" name="nueva" minlength="10" autocomplete="new-password"></label></fieldset>
<button type="submit">Guardar cambios</button></form>
