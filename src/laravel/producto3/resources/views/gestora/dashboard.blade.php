@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Panel de <span>Gestión</span></h1>
            <p class="hero-subtitle">Bienvenida, {{ $gestora->nombre_empresa ?? auth()->user()->nombre }} (CIF: {{ $gestora->cif ?? 'N/A' }}).</p>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <span class="badge-status badge-asignada" style="font-size: 1rem; padding: 8px 15px;">
                Comisión Aplicada: <strong>{{ number_format($gestora->porcentaje_comision ?? 0, 2) }}%</strong>
            </span>
            <a href="{{ route('incidencias.create') }}" class="btn-submit">+ Nueva Incidencia</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success" style="color: #22c55e; font-weight: 600; margin-bottom: 25px; background: #052e16; padding: 14px 18px; border-radius: 8px; border: 1px solid #22c55e; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <!-- 1. Mis Comunidades Asignadas -->
    <div class="dashboard-card" style="margin-bottom: 30px;">
        <h3>Comunidades Bajo Tu Gestión</h3>
        @if($comunidades->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No tienes comunidades asignadas actualmente.</p>
        @else
            <div class="table-responsive" style="margin-top: 15px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nombre Comunidad</th>
                            <th>Dirección</th>
                            <th>Zona</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($comunidades as $comunidad)
                            <tr>
                                <td><strong>{{ $comunidad->nombre }}</strong></td>
                                <td>{{ $comunidad->direccion }}</td>
                                <td class="table-subtext">{{ $comunidad->zona->nombre ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- 2. Incidencias Creadas para tus Comunidades -->
    <div class="dashboard-card" style="margin-bottom: 30px;">
        <h3>Incidencias Registradas</h3>
        @if($incidencias->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No hay incidencias registradas en tus comunidades.</p>
        @else
            <div class="table-responsive" style="margin-top: 15px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Urgencia</th>
                            <th>Fecha Servicio</th>
                            <th>Dirección</th>
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

    <!-- 3. Historial de Comisiones -->
    <div class="dashboard-card">
        <h3>Historial de Comisiones Generadas (Incidencias Finalizadas)</h3>

        @if($comisones->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No hay comisiones generadas todavía (se generan automáticamente al finalizarse una incidencia).</p>
        @else
            <div class="table-responsive" style="margin-top: 15px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Localizador Incidencia</th>
                            <th>Fecha Servicio</th>
                            <th>Importe Comisión</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($comisones as $comision)
                            <tr>
                                <td class="table-code">{{ $comision->incidencia->localizador ?? 'N/A' }}</td>
                                <td class="table-date">{{ $comision->incidencia ? date('d/m/Y H:i', strtotime($comision->incidencia->fecha_servicio)) : 'N/A' }}</td>
                                <td><strong style="color: #ff6600;">{{ number_format($comision->importe, 2) }} €</strong></td>
                                <td>
                                    <span class="badge-status {{ $comision->estado === 'Pagada' ? 'badge-finalizada' : 'badge-pendiente' }}">
                                        {{ $comision->estado }}
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