@extends('layouts.app')

@section('content')
<div class="home-container" style="max-width: 500px;">
    <div class="dashboard-card">
        <h2 class="hero-title" style="font-size: 1.8rem; text-align: center; margin-bottom: 10px;">Iniciar <span>Sesión</span></h2>
        <p class="hero-subtitle" style="text-align: center; margin-bottom: 25px;">Accede a tu panel de gestión en ReparaYa</p>

        @if($errors->any())
            <div class="alert-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="form-control" placeholder="tu@correo.com">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" id="password" name="password" required class="form-control" placeholder="••••••••">
            </div>

            <button type="submit" class="btn-submit">Entrar al Sistema</button>
        </form>

        <p style="text-align: center; margin-top: 20px; color: #9ca3af; font-size: 0.9rem;">
            ¿No tienes una cuenta? <a href="{{ route('register') }}" style="color: #ff6600; font-weight: 600;">Regístrate aquí</a>
        </p>
    </div>
</div>
@endsection