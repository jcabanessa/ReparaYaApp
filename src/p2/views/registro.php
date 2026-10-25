<form class="card form narrow" method="post" action="<?=h(url('registro'))?>"><?=csrf_field()?>
<label>Nombre <input name="nombre" required minlength="2" maxlength="100" autocomplete="name"></label>
<label>Correo electrónico <input type="email" name="email" required maxlength="100" autocomplete="email"></label>
<label>Teléfono (opcional) <input name="telefono" maxlength="20" autocomplete="tel"></label>
<label>Contraseña (mínimo 10 caracteres) <input type="password" name="password" minlength="10" required autocomplete="new-password"></label>
<label>Repite la contraseña <input type="password" name="confirmacion" minlength="10" required autocomplete="new-password"></label>
<p class="muted">El registro público crea únicamente cuentas particulares. Los técnicos los crea el administrador.</p>
<button type="submit">Crear cuenta</button></form>
