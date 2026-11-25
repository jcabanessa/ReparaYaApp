@extends('layouts.app')

@section('content')
<div class="home-container">
    <h1 class="hero-title">Calendario de <span>Intervenciones</span></h1>
    <p class="hero-subtitle">Vista temporal diferenciada por colores (Urgente vs Estándar) y distintivos explícitos de estado.</p>

    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        <a href="{{ route('admin.calendario', ['filtro' => 'todos']) }}" class="filter-link {{ $filtro === 'todos' ? 'active' : '' }}">Todos</a>
        <a href="{{ route('admin.calendario', ['filtro' => 'mensual']) }}" class="filter-link {{ $filtro === 'mensual' ? 'active' : '' }}">Mensual</a>
        <a href="{{ route('admin.calendario', ['filtro' => 'semanal']) }}" class="filter-link {{ $filtro === 'semanal' ? 'active' : '' }}">Semanal</a>
        <a href="{{ route('admin.calendario', ['filtro' => 'diaria']) }}" class="filter-link {{ $filtro === 'diaria' ? 'active' : '' }}">Diaria</a>
    </div>

    <div style="display: flex; gap: 20px; margin-bottom: 25px; background: #141414; padding: 15px; border-radius: 8px; border: 1px solid #2a2a2a; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 14px; height: 14px; background: #ef4444; border-radius: 3px; display: inline-block;"></span>
            <span style="color: #fff; font-size: 0.9rem;">Servicio Urgente</span>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="width: 14px; height: 14px; background: #3182ce; border-radius: 3px; display: inline-block;"></span>
            <span style="color: #fff; font-size: 0.9rem;">Servicio Estándar</span>
        </div>
    </div>

    {{-- Calendario interactivo FullCalendar --}}
    <div class="dashboard-card" style="margin-bottom: 25px;">
        <h3>Vista de Calendario ({{ ucfirst($filtro) }})</h3>
        <div id="admin-fullcalendar" style="margin-top: 15px;"></div>

        <div id="admin-evento-detalle" style="display: none; margin-top: 18px; background: #111; border: 1px solid #2f2f2f; border-left: 4px solid #ff6600; padding: 14px; border-radius: 6px; color: #e5e7eb;">
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                <strong id="detalle-titulo" style="color: #fff; font-size: 1rem;"></strong>
                <button type="button" onclick="document.getElementById('admin-evento-detalle').style.display='none'" style="background: #222; color: #fff; border: 1px solid #444; border-radius: 4px; padding: 2px 8px; cursor: pointer;">Cerrar</button>
            </div>
            <p id="detalle-meta" style="margin: 8px 0 4px; font-size: 0.9rem; color: #9ca3af;"></p>
            <p id="detalle-desc" style="margin: 0; font-size: 0.88rem; color: #d1d5db;"></p>
        </div>
    </div>

    {{-- Listado detallado --}}
    <div class="dashboard-card">
        <h3>Programación de Citas</h3>
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
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px; flex-wrap: wrap;">
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
                                {{ date('d/m/Y - H:i', strtotime($inc->fecha_servicio)) }}
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

@php
    $eventosAdmin = $incidencias->map(function ($inc) {
        $esUrgente = strtolower($inc->tipo_urgencia) === 'urgente';
        $color = $esUrgente ? '#ef4444' : '#3182ce';
        $servicio = $inc->especialidad->nombre_especialidad ?? 'General';

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
                'cliente' => $inc->cliente->nombre ?? 'N/A',
                'tecnico' => $inc->tecnico->nombre ?? 'Sin asignar',
                'direccion' => $inc->direccion,
                'descripcion' => $inc->descripcion ?? '',
            ],
        ];
    })->values();
@endphp

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<style>
    #admin-fullcalendar {
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
    #admin-fullcalendar .fc-col-header-cell-cushion,
    #admin-fullcalendar .fc-daygrid-day-number,
    #admin-fullcalendar .fc-timegrid-slot-label-cushion {
        color: #d1d5db;
        text-decoration: none;
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('admin-fullcalendar');
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
        events: @json($eventosAdmin),
        eventClick: function (info) {
            const p = info.event.extendedProps;
            const panel = document.getElementById('admin-evento-detalle');
            document.getElementById('detalle-titulo').textContent =
                `${p.localizador} — ${info.event.title} [${p.estado}]`;
            document.getElementById('detalle-meta').textContent =
                `Urgencia: ${p.urgencia} | Cliente: ${p.cliente} | Técnico: ${p.tecnico} | Dirección: ${p.direccion}`;
            document.getElementById('detalle-desc').textContent = p.descripcion;
            panel.style.display = 'block';
        }
    });

    calendar.render();
});
</script>
@endsection