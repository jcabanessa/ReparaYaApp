@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Calendario <span>Global de Servicios</span></h1>
            <p class="hero-subtitle">Planificación y seguimiento temporal de todas las incidencias programadas.</p>
        </div>
    </div>

    <div class="dashboard-card">
        <h3>Próximas Citas y Servicios</h3>
        @php
            $incidenciasCalendario = App\Models\Incidencia::with(['tecnico', 'comunidad'])->orderBy('fecha_servicio', 'asc')->get();
        @endphp

        @if($incidenciasCalendario->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No hay servicios programados en el calendario.</p>
        @else
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Fecha y Hora</th>
                            <th>Código</th>
                            <th>Comunidad / Dirección</th>
                            <th>Técnico Asignado</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incidenciasCalendario as $inc)
                            <tr>
                                <td class="table-date" style="font-weight: bold; color: #ff6600;">
                                    {{ date('d/m/Y H:i', strtotime($inc->fecha_servicio)) }}
                                </td>
                                <td class="table-code">{{ $inc->localizador }}</td>
                                <td>{{ $inc->comunidad->nombre ?? 'N/A' }}<br><small style="color: #9ca3af;">{{ $inc->direccion }}</small></td>
                                <td>{{ $inc->tecnico->nombre ?? 'Sin asignar' }}</td>
                                <td>
                                    <span class="badge-status badge-{{ strtolower(str_replace(' ', '', $inc->estado)) }}">
                                        {{ $inc->estado }}
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