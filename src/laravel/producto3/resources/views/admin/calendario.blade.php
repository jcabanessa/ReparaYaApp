@extends('layouts.app')

@section('content')
<div class="home-container">
    <h1 class="hero-title">Calendario de <span>Intervenciones</span></h1>
    <p class="hero-subtitle">Vista temporal diferenciada por colores (Urgente vs Estándar) y distintivos explícitos de estado.</p>
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('admin.calendario', ['filtro' => 'todos']) }}" class="filter-link {{ request('filtro', 'todos') === 'todos' ? 'active' : '' }}">Todos</a>
        <a href="{{ route('admin.calendario', ['filtro' => 'mensual']) }}" class="filter-link {{ request('filtro') === 'mensual' ? 'active' : '' }}">Mensual</a>
        <a href="{{ route('admin.calendario', ['filtro' => 'semanal']) }}" class="filter-link {{ request('filtro') === 'semanal' ? 'active' : '' }}">Semanal</a>
        <a href="{{ route('admin.calendario', ['filtro' => 'diaria']) }}" class="filter-link {{ request('filtro') === 'diaria' ? 'active' : '' }}">Diaria</a>
    </div>

    <div style="display: flex; gap: 20px; margin-bottom: 25px; background: #141414; padding: 15px; border-radius: 8px; border: 1px solid #2a2a2a;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 14px; height: 14px; background: #ef4444; border-radius: 3px; display: inline-block;"></span>
            <span style="color: #fff; font-size: 0.9rem;">Servicio Urgente</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 14px; height: 14px; background: #3182ce; border-radius: 3px; display: inline-block;"></span>
            <span style="color: #fff; font-size: 0.9rem;">Servicio Estándar</span>
        </div>
    </div>

    <div class="dashboard-card">
        <h3>Programación de Citas</h3>
        @php
            $incidencias = App\Models\Incidencia::with(['cliente', 'tecnico', 'especialidad'])->orderBy('fecha_servicio', 'asc')->get();
        @endphp

        @if($incidencias->isEmpty())
            <p style="color: #9ca3af; padding: 15px 0;">No hay servicios registrados en el calendario.</p>
        @else
            <div style="display: grid; gap: 15px; margin-top: 20px;">
                @foreach ($incidencias as $inc)
                    @php
                        $esUrgente = strtolower($inc->tipo_urgencia) === 'urgente';
                        $colorBorde = $esUrgente ? '#ef4444' : '#3182ce';
                    @endphp
                    <div style="background: #0e0e0e; border: 1px solid #222; border-left: 5px solid {{ $colorBorde }}; padding: 15px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                                <strong style="color: #fff; font-size: 1.1rem;">{{ $inc->localizador }}</strong>
                                <span style="background: {{ $colorBorde }}; color: #fff; font-size: 0.75rem; padding: 2px 6px; border-radius: 3px; text-transform: uppercase; font-weight: bold;">
                                    {{ $inc->tipo_urgencia }}
                                </span>
                                <span style="background: #2a2a2a; color: #e5e7eb; font-size: 0.75rem; padding: 2px 8px; border-radius: 3px; border: 1px solid #444;">
                                    Estado: <strong>{{ $inc->estado }}</strong>
                                </span>
                            </div>
                            <p style="color: #9ca3af; font-size: 0.9rem; margin: 0;">
                                <strong>Cliente:</strong> {{ $inc->cliente->nombre ?? 'N/A' }} | 
                                <strong>Dirección:</strong> {{ $inc->direccion }}
                            </p>
                            <p style="color: #d1d5db; font-size: 0.85rem; margin-top: 4px;">{{ $inc->descripcion }}</p>
                        </div>
                        <div style="text-align: right;">
                            <span style="color: #ff6600; font-weight: bold; font-size: 1rem; display: block;">
                                📅 {{ date('d/m/Y - H:i', strtotime($inc->fecha_servicio)) }}
                            </span>
                            <small style="color: #9ca3af; font-size: 0.85rem;">
                                Técnico: {{ $inc->tecnico->nombre ?? 'Sin asignar' }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection