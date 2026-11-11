@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Mis <span>Avisos</span></h1>
            <p class="hero-subtitle">Consulta el estado actual y el historial de tus solicitudes de reparación.</p>
        </div>
        <div>
            <a href="{{ route('incidencias.create') }}" class="btn-submit">+ Nueva Solicitud</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success" style="color: #22c55e; font-weight: 600; margin-bottom: 25px; background: #052e16; padding: 14px 18px; border-radius: 8px; border: 1px solid #22c55e; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <div class="dashboard-card">
        <h3>Listado de Tus Solicitudes</h3>
        @if($incidencias->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No has registrado ninguna incidencia todavía. ¡Crea una nueva solicitud para comenzar!</p>
        @else
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Urgencia</th>
                            <th>Fecha Cita</th>
                            <th>Dirección</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incidencias as $inc)
                            <tr>
                                <td class="table-code">{{ $inc->localizador }}</td>
                                <td class="table-urgency {{ strtolower($inc->tipo_urgencia) === 'urgente' ? 'urgente' : 'estandar' }}">
                                    {{ $inc->tipo_urgencia }}
                                </td>
                                <td class="table-date">{{ date('d/m/Y H:i', strtotime($inc->fecha_servicio)) }}</td>
                                <td>{{ $inc->direccion }}</td>
                                <td class="table-subtext">{{ Str::limit($inc->descripcion, 40) }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($inc->estado) {
                                            'Pendiente'  => 'badge-pendiente',
                                            'Asignada'   => 'badge-asignada',
                                            'En curso'   => 'badge-encurso',
                                            'Finalizada' => 'badge-finalizada',
                                            'Cancelada'  => 'badge-cancelada',
                                            default      => ''
                                        };
                                    @endphp
                                    <span class="badge-status {{ $badgeClass }}">
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