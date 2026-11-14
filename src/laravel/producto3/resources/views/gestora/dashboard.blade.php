@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Panel de <span>Gestión y Liquidaciones</span></h1>
            <p class="hero-subtitle">Bienvenida, {{ $gestora->nombre_empresa }} (CIF: {{ $gestora->cif }}).</p>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <span class="badge-status badge-asignada" style="font-size: 1rem; padding: 8px 15px;">
                Comisión Aplicada: <strong>{{ number_format($gestora->porcentaje_comision, 2) }}%</strong>
            </span>
            <a href="{{ route('incidencias.create') }}" class="btn-submit">+ Nueva Incidencia</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success" style="color: #22c55e; font-weight: 600; margin-bottom: 25px; background: #052e16; padding: 14px; border-radius: 8px; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Mis Comunidades -->
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
                                <td><span style="color: #38bdf8;">{{ $comunidad->zona }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Incidencias Registradas -->
    <div class="dashboard-card" style="margin-bottom: 30px;">
        <h3>Incidencias de Tus Comunidades</h3>
        @if($incidencias->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No hay incidencias registradas todavía.</p>
        @else
            <div class="table-responsive" style="margin-top: 15px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Comunidad (Zona)</th>
                            <th>Urgencia</th>
                            <th>Fecha Servicio</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incidencias as $inc)
                            <tr>
                                <td class="table-code">{{ $inc->localizador }}</td>
                                <td>
                                    {{ $inc->comunidad->nombre ?? 'N/A' }} 
                                    @if($inc->comunidad?->zona)
                                        <small style="color: #38bdf8;">({{ $inc->comunidad->zona }})</small>
                                    @endif
                                </td>
                                <td class="table-urgency {{ strtolower($inc->tipo_urgencia) === 'urgente' ? 'urgente' : 'estandar' }}">
                                    {{ $inc->tipo_urgencia }}
                                </td>
                                <td class="table-date">{{ date('d/m/Y H:i', strtotime($inc->fecha_servicio)) }}</td>
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

    <!-- Liquidaciones Mensuales y Comisiones -->
    <div class="dashboard-card">
        <h3>Liquidaciones y Comisiones por Servicios Finalizados</h3>

        @if($comisionesPorMes->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No hay comisiones generadas todavía. Se generan automáticamente al finalizarse una incidencia.</p>
        @else
            @foreach($comisionesPorMes as $mes => $comisionesMes)
                <div style="margin-top: 25px; background: #111; padding: 15px; border-radius: 8px; border: 1px solid #2a2a2a;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; border-bottom: 1px solid #222; padding-bottom: 8px;">
                        <h4 style="color: #ff6600; margin: 0;">Periodo: {{ $mes }}</h4>
                        <span style="color: #10b981; font-weight: bold;">
                            Total Acumulado Mes: {{ number_format($comisionesMes->sum('importe'), 2) }} €
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Incidencia</th>
                                    <th>Comunidad (Zona)</th>
                                    <th>Tipo Servicio</th>
                                    <th>Precio Base</th>
                                    <th>% Aplicado</th>
                                    <th>Importe Comisión</th>
                                    <th>updated_at</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($comisionesMes as $com)
                                    <tr>
                                        <td class="table-code">{{ $com->incidencia->localizador ?? 'N/A' }}</td>
                                        <td class="table-date">{{ $com->updated_at ? $com->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                        <td>
                                            {{ $com->incidencia->comunidad->nombre ?? 'N/A' }}
                                            @if($com->incidencia?->comunidad?->zona)
                                                <small style="color: #38bdf8;">({{ $com->incidencia->comunidad->zona }})</small>
                                            @endif
                                        </td>
                                        <td>{{ $com->incidencia->especialidad->nombre ?? 'N/A' }}</td>
                                        <td>{{ number_format($com->incidencia->especialidad->precio_base ?? 0, 2) }} €</td>
                                        <td>{{ number_format($gestora->porcentaje_comision, 2) }}%</td>
                                        <td><strong style="color: #ff6600;">{{ number_format($com->importe, 2) }} €</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
@endsection