@extends('layouts.app')
@section('title','Registro')
@section('content')<div class="auth"><div class="card"><h1>Crear cuenta particular</h1>
<form method="post" action="{{ route('register.post') }}">@csrf
<label>Nombre<input name="nombre" value="{{ old('nombre') }}" required maxlength="100"></label>
<label>Email<input name="email" type="email" value="{{ old('email') }}" required></label>
<label>Teléfono<input name="telefono" value="{{ old('telefono') }}" maxlength="20"></label>
<label>Contraseña (10 caracteres como mínimo)<input type="password" name="password" required minlength="10"></label>
<label>Repetir contraseña<input type="password" name="password_confirmation" required></label>
<button class="btn">Crear cuenta</button></form></div></div>@endsection
