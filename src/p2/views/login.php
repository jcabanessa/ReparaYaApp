<form class="card form narrow" method="post" action="<?=h(url('login'))?>"><?=csrf_field()?>
<label>Correo electrónico <input type="email" name="email" required maxlength="100" autocomplete="email"></label>
<label>Contraseña <input type="password" name="password" required autocomplete="current-password"></label>
<button type="submit">Entrar</button><p>¿No tienes cuenta? <a href="<?=h(url('registro'))?>">Regístrate</a></p></form>
