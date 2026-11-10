@extends('layouts.app')

@section('content')
<div class="home-container" style="max-width: 900px;">
    <h1 class="hero-title">Mi <span>Perfil</span></h1>
    <p class="hero-subtitle">Gestiona tu información personal y la seguridad de tu cuenta.</p>

    @if(session('success'))
        <p style="color: #10b981; font-weight: 600; margin-bottom: 20px;">{{ session('success') }}</p>
    @endif
    @if(session('error'))
        <p style="color: #ef4444; font-weight: 600; margin-bottom: 20px;">{{ session('error') }}</p>
    @endif

    <div class="dashboard-grid">
        <!-- Información Personal -->
        <div class="dashboard-card">
            <h3>Información Personal</h3>
            
            <form action="{{ route('perfil.actualizar') }}" method="POST" style="margin-top: 15px;">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 15px;">
                    <label style="color: #9ca3af; font-size: 0.85rem; display: block; margin-bottom: 5px;">Rol de Usuario</label>
                    <input type="text" value="{{ strtoupper(auth()->user()->rol) }}" disabled class="form-control" style="background: #1a1a1a; color: #ff6600; font-weight: bold; cursor: not-allowed;">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="nombre" class="form-label">Nombre Completo</label>
                    <input type="text" name="nombre" id="nombre" value="{{ auth()->user()->nombre }}" required class="form-control">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" id="email" value="{{ auth()->user()->email }}" required class="form-control">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="telefono" class="form-label">Teléfono de Contacto</label>
                    <input type="text" name="telefono" id="telefono" value="{{ auth()->user()->telefono }}" class="form-control">
                </div>

                <button type="submit" class="btn-submit" style="width: 100%;">Guardar Cambios</button>
            </form>
        </div>

        <!-- Cambiar Contraseña -->
        <div class="dashboard-card">
            <h3>Cambiar Contraseña</h3>
            
            <form action="{{ route('perfil.password') }}" method="POST" style="margin-top: 15px;">
                @csrf
                @method('PUT')

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="password_actual" class="form-label">Contraseña Actual</label>
                    <input type="password" name="password_actual" id="password_actual" required class="form-control">
                </div>

                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="password_nueva" class="form-label">Nueva Contraseña</label>
                    <input type="password" name="password_nueva" id="password_nueva" required class="form-control">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmar" id="password_confirmar" required class="form-control">
                </div>

                <button type="submit" style="width: 100%; padding: 12px; background: #2a2a2a; border: 1px solid #ff6600; color: #ff6600; font-weight: bold; border-radius: 6px; cursor: pointer; transition: background 0.2s ease;">Actualizar Contraseña</button>
            </form>
        </div>
    </div>
</div>
@endsection