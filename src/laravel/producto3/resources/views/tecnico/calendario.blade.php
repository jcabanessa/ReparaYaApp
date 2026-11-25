@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Calendario de <span>Intervenciones</span></h1>
            <p class="hero-subtitle">Tus servicios asignados organizados por fecha.</p>
        </div>
    </div>

    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('tecnico.calendario', ['filtro' => 'todos']) }}" class="filter-link {{ $filtro === 'todos' ? 'active' : '' }}">Todos</a>
        <a href="{{ route('tecnico.calendario', ['filtro' => 'mensual']) }}" class="filter-link {{ $filtro === 'mensual' ? 'active' : '' }}">Mensual</a>
        <a href="{{ route('tecnico.calendario', ['filtro' => 'semanal']) }}" class="filter-link {{ $filtro === 'semanal' ? 'active' : '' }}">Semanal</a>
        <a href="{{ route('tecnico.calendario', ['filtro' => 'diaria']) }}" class="filter-link {{ $filtro === 'diaria' ? 'active' : '' }}">Diaria</a>
    </div>

    <div class="dashboard-card" style="margin-bottom: 25px;">
        <h3>Calendario de Trabajos ({{ ucfirst($filtro) }})</h3>
        <div id="tecnico-fullcalendar" style="margin-top: 15px;"></div>

        <div id="tecnico-evento-detalle" style="display: none; margin-top: 18px; background: #111; border: 1px solid #2f2f2f; border-left: 4px solid #ff6600; padding: 14px; border-radius: 6px; color: #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                <strong id="tec-detalle-titulo" style="color: #fff; font-size: 1rem;"></strong>
                <button type="button" onclick="document.getElementById('tecnico-evento-detalle').style.display='none'" style="background: #222; color: #fff; border: 1px solid #444; border-radius: 4px; padding: 2px 8px; cursor: pointer;">Cerrar</button>
            </div>
            <p id="tec-detalle-meta" style="margin: 8px 0 4px; font-size: 0.9rem; color: #9ca3af;"></p>
            <p id="tec-detalle-desc" style="margin: 0; font-size: 0.88rem; color: #d1d5db;"></p>
        </div>
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

@php
    $eventosTecnico = $incidencias->map(function ($inc) {
        $esUrgente = strtolower($inc->tipo_urgencia) === 'urgente';
        $color = $esUrgente ? '#ef4444' : '#3182ce';
        $servicio = $inc->especialidad->nombre_especialidad ?? 'Servicio';

        return [
            'id' => $inc->id,
            'title' => "{$inc->localizador} · {$servicio}",
            'start' => \Carbon\Carbon::parse($inc->fecha_servicio)->toIso8601String(),
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => '#ffffff',
            'extendedProps' => [
                'localizador' => $inc->localizador,
                'urgencia' => $inc->tipo_urgencia,
                'estado' => $inc->estado,
                'direccion' => $inc->direccion,
                'descripcion' => $inc->descripcion ?? '',
            ],
        ];
    })->values();
@endphp

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<style>
    #tecnico-fullcalendar {
        --fc-border-color: #262626;
        --fc-page-bg-color: #0e0e0e;
        --fc-neutral-bg-color: #161616;
        --fc-list-event-hover-bg-color: #1c1c1c;
        --fc-today-bg-color: rgba(255, 102, 0, 0.12);
        --fc-button-bg-color: #1f2937;
        --fc-button-border-color: #374151;
        --fc-button-hover-bg-color: #374151;
        --fc-button-active-bg-color: #ff6600;
        --fc-button-active-border-color: #ff6600;
        color: #e5e7eb;
    }
    #tecnico-fullcalendar .fc-col-header-cell-cushion,
    #tecnico-fullcalendar .fc-daygrid-day-number,
    #tecnico-fullcalendar .fc-timegrid-slot-label-cushion {
        color: #d1d5db;
        text-decoration: none;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('tecnico-fullcalendar');
    if (!calendarEl || typeof FullCalendar === 'undefined') return;

    const filtroActual = @json($filtro);
    const vistaInicial = {
        'mensual': 'dayGridMonth',
        'semanal': 'timeGridWeek',
        'diaria': 'timeGridDay',
        'todos': 'dayGridMonth'
    }[filtroActual] || 'dayGridMonth';

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: vistaInicial,
        locale: 'es',
        firstDay: 1,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            day: 'Día'
        },
        events: @json($eventosTecnico),
        eventClick: function (info) {
            const p = info.event.extendedProps;
            const panel = document.getElementById('tecnico-evento-detalle');
            document.getElementById('tec-detalle-titulo').textContent =
                `${p.localizador} — ${info.event.title} [${p.estado}]`;
            document.getElementById('tec-detalle-meta').textContent =
                `Urgencia: ${p.urgencia} | Dirección: ${p.direccion}`;
            document.getElementById('tec-detalle-desc').textContent = p.descripcion;
            panel.style.display = 'block';
        }
    });

    calendar.render();
});
</script>
@endsection