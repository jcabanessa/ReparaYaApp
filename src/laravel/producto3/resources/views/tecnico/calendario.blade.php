@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Calendario de <span>Intervenciones</span></h1>
            <p class="hero-subtitle">Tus servicios asignados organizados por fecha.</p>
        </div>
    </div>
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('tecnico.calendario', ['filtro' => 'todos']) }}" class="filter-link {{ $filtro === 'todos' ? 'active' : '' }}">Todos</a>
        <a href="{{ route('tecnico.calendario', ['filtro' => 'mensual']) }}" class="filter-link {{ $filtro === 'mensual' ? 'active' : '' }}">Mensual</a>
        <a href="{{ route('tecnico.calendario', ['filtro' => 'semanal']) }}" class="filter-link {{ $filtro === 'semanal' ? 'active' : '' }}">Semanal</a>
        <a href="{{ route('tecnico.calendario', ['filtro' => 'diaria']) }}" class="filter-link {{ $filtro === 'diaria' ? 'active' : '' }}">Diaria</a>
    </div>

    <div class="dashboard-card">
        <h3>Próximos Trabajos Programados</h3>

        @if($incidencias->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No tienes intervenciones agendadas en tu calendario.</p>
        @else
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Código</th>
                            <th>Dirección</th>
                            <th>Urgencia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incidencias as $incidencia)
                            <tr>
                                <td class="table-date" style="font-weight: bold; color: #ff6600;">
                                    {{ date('d/m/Y H:i', strtotime($incidencia->fecha_servicio)) }}
                                </td>
                                <td class="table-code">{{ $incidencia->localizador }}</td>
                                <td>{{ $incidencia->direccion }}</td>
                                <td class="table-urgency {{ strtolower($incidencia->tipo_urgencia) === 'urgente' ? 'urgente' : 'estandar' }}">
                                    {{ $incidencia->tipo_urgencia }}
                                </td>
                                <td>
                                    <span class="badge-status badge-{{ strtolower(str_replace(' ', '', $incidencia->estado)) }}">
                                        {{ $incidencia->estado }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection