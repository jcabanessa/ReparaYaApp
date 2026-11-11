@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Panel de <span>Administración</span></h1>
            <p class="hero-subtitle">Bienvenido al sistema de control de ReparaYa.</p>
        </div>
        <div>
            <a href="{{ route('admin.calendario') }}" class="btn-orange">Ver Calendario de Incidencias</a>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>Resumen del Sistema</h3>
            <div class="info-row">
                <span class="info-label">Total de Incidencias</span>
                <span class="table-code">{{ $totalIncidencias }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Técnicos Registrados</span>
                <span class="table-code">{{ $totalTecnicos }}</span>
            </div>
        </div>
    </div>
</div>
@endsection