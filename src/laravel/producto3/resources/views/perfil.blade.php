@extends('layouts.app')
@section('title','Mi perfil')
@section('content')<h1>Mi perfil</h1><div class="grid cols2">
<div class="card"><h2>Datos personales</h2><form method="post" action="{{ route('perfil.actualizar') }}">@csrf @method('PUT')
<label>Nombre<input name="nombre" value="{{ old('nombre',auth()->user()->nombre) }}" required></label>
<label>Correo<input name="email" type="email" value="{{ old('email',auth()->user()->email) }}" required></label>
<label>Teléfono<input name="telefono" value="{{ old('telefono',auth()->user()->telefono) }}"></label>
<button class="btn">Guardar datos</button></form></div>
<div class="card"><h2>Cambiar contraseña</h2><form method="post" action="{{ route('perfil.password') }}">@csrf @method('PUT')
<label>Contraseña actual<input name="actual" type="password" required></label>
<label>Nueva contraseña<input name="password" type="password" minlength="10" required></label>
<label>Confirmar<input name="password_confirmation" type="password" minlength="10" required></label>
<button class="btn">Actualizar contraseña</button></form></div></div>@endsection
