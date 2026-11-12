@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Calendario de <span>Intervenciones</span></h1>
            <p class="hero-subtitle">Tus servicios asignados organizados por fecha.</p>
        </div>
    </div>

    <div class="dashboard-card">
        <h3>Próximos Trabajos Programados</h3>
        @php
            $misCitas = App\Models\Incidencia::where('tecnico_id', auth()->id())->orderBy('fecha_servicio', 'asc')->get();
        @endphp

        @if($misCitas->isEmpty())
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
                        @foreach ($misCitas as $cita)
                            <tr>
                                <td class="table-date" style="font-weight: bold; color: #ff6600;">
                                    {{ date('d/m/Y H:i', strtotime($cita->fecha_servicio)) }}
                                </td>
                                <td class="table-code">{{ $cita->localizador }}</td>
                                <td>{{ $cita->direccion }}</td>
                                <td class="table-urgency {{ strtolower($cita->tipo_urgencia) === 'urgente' ? 'urgente' : 'estandar' }}">
                                    {{ $cita->tipo_urgencia }}
                                </td>
                                <td>
                                    <span class="badge-status badge-{{ strtolower(str_replace(' ', '', $cita->estado)) }}">
                                        {{ $cita->estado }}
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