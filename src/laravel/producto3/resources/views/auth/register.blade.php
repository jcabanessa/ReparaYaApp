@extends('layouts.app')

@section('content')
<div class="home-container" style="max-width: 500px;">
    <div class="dashboard-card">
        <h2 class="hero-title" style="font-size: 1.8rem; text-align: center; margin-bottom: 10px;">Crear <span>Cuenta</span></h2>
        <p class="hero-subtitle" style="text-align: center; margin-bottom: 25px;">Regístrate como nuevo cliente en ReparaYa</p>

        @if($errors->any())
            <div class="alert-error">
                <ul style="list-style-type: none; margin: 0; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="nombre" class="form-label">Nombre Completo</label>
                <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required autofocus class="form-control" placeholder="Tu Nombre">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="form-control" placeholder="tu@correo.com">
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" required class="form-control" placeholder="••••••••">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required class="form-control" placeholder="••••••••">
            </div>

            <button type="submit" class="btn-submit">Completar Registro</button>
        </form>

        <p style="text-align: center; margin-top: 20px; color: #9ca3af; font-size: 0.9rem;">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" style="color: #ff6600; font-weight: 600;">Inicia sesión</a>
        </p>
    </div>
</div>
@endsection