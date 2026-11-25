@extends('layouts.app')
@section('title','Iniciar sesión')
@section('content')<div class="auth"><div class="card"><h1>Iniciar sesión</h1><p class="muted">Accede al área de clientes, técnicos, administradores o gestoras.</p>
<form method="post" action="{{ route('login.post') }}">@csrf
<label>Correo electrónico<input name="email" type="email" value="{{ old('email') }}" autocomplete="username" required></label>
<label>Contraseña<input name="password" type="password" autocomplete="current-password" required></label>
<button class="btn">Entrar</button></form><p>¿Eres particular? <a href="{{ route('register') }}">Regístrate</a>.</p></div></div>@endsection
