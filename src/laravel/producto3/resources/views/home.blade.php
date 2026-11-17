@extends('layouts.app')

@section('content')
<div class="home-container">
    <h1 class="hero-title">Bienvenido a <span>ReparaYaApp</span></h1>
    <p class="hero-subtitle">Plataforma de gestión, mantenimiento y resolución de incidencias del hogar.</p>

    <div class="home-cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-top: 25px;">
        
        <!-- Tarjeta 1: ¿Cómo funciona? -->
        <div class="dashboard-card">
            <h3>¿CÓMO FUNCIONA?</h3>
            <p style="margin-top: 10px;"><strong>Regístrate:</strong> Accede a la plataforma de forma gratuita.</p>
            <p><strong>Solicita un aviso:</strong> Estándar (48h) o de carácter Urgente.</p>
            <p><strong>Asignación rápida:</strong> Nuestro administrador asigna al técnico ideal.</p>
            <p><strong>Seguimiento:</strong> Consulta el estado de tu avería en tiempo real.</p>
        </div>

        <!-- Tarjeta 2: Estado del Sistema -->
        <div class="dashboard-card">
            <h3>ESTADO DEL SISTEMA</h3>
            <div class="info-row" style="display: flex; justify-content: space-between; margin: 10px 0; border-bottom: 1px solid #222; padding-bottom: 8px;">
                <span>Entorno:</span>
                <strong>{{ str_contains(request()->getHost(), 'uoc.edu') ? 'producción (UOC)' : 'local' }}</strong>
            </div>
            <div class="info-row" style="display: flex; justify-content: space-between; margin: 10px 0; border-bottom: 1px solid #222; padding-bottom: 8px;">
                <span>Versión PHP:</span>
                <strong>{{ phpversion() }}</strong>
            </div>
            <div class="info-row" style="display: flex; justify-content: space-between; margin: 10px 0; border-bottom: 1px solid #222; padding-bottom: 8px;">
                <span>Seguridad:</span>
                <strong style="color: #ff6600;">Eloquent & Bcrypt Hash</strong>
            </div>
            <div class="info-row" style="display: flex; justify-content: space-between; margin: 10px 0;">
                <span>Base de Datos:</span>
                <strong style="color: #10b981;">Conectada Correctamente</strong>
            </div>
        </div>

        <!-- Tarjeta 3: Información del Proyecto -->
        <div class="dashboard-card">
            <h3>INFORMACIÓN DEL PROYECTO & SERVIDOR</h3>
            <div class="info-grid-2" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px;">
                <div>
                    <span style="color: #9ca3af; font-size: 0.85rem; display: block;">Grupo:</span>
                    <strong>backeners</strong>
                </div>
                <div>
                    <span style="color: #9ca3af; font-size: 0.85rem; display: block;">Fecha y Hora:</span>
                    <strong>{{ now()->format('Y-m-d H:i:s') }}</strong>
                </div>
                <div>
                    <span style="color: #9ca3af; font-size: 0.85rem; display: block;">Estudiante:</span>
                    <strong>{{ env('STUDENT_NAME') }}</strong>
                </div>
                <div>
                    <span style="color: #9ca3af; font-size: 0.85rem; display: block;">Patrón Arquitectura:</span>
                    <strong>Laravel MVC</strong>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection