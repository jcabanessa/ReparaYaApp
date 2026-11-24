@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Liquidaciones y <span>Comisiones B2B</span></h1>
            <p class="hero-subtitle">
                Empresa Gestora: <strong>{{ $gestora->nombre_empresa ?? 'Gestora' }}</strong> ·
                Porcentaje asignado: <span style="color: #ff6600; font-weight: bold;">{{ number_format($gestora->porcentaje_comision, 2) }}%</span>
            </p>
        </div>
        <div>
            <a href="{{ route('gestora.dashboard') }}" class="btn-orange">Volver al Panel</a>
        </div>
    </div>

    <div class="dashboard-card" style="margin-bottom: 35px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0;">Total Acumulado por Mes</h3>
            <span style="font-size: 1.1rem; font-weight: bold; color: #ff6600;">
                Total Histórico: {{ number_format($totalGlobalComisiones, 2) }} €
            </span>
        </div>

        @if($liquidacionesPorMes->isEmpty())
            <p class="text-empty">Aún no existen servicios finalizados que hayan generado comisión.</p>
        @else
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Mes de Finalización</th>
                            <th>Servicios Finalizados</th>
                            <th>Base Imponible Acumulada</th>
                            <th>% Aplicado</th>
                            <th>Total Comisión Mes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($liquidacionesPorMes as $mes)
                            <tr>
                                <td style="font-weight: bold;">{{ $mes['mes_etiqueta'] }}</td>
                                <td><span class="table-code">{{ $mes['total_servicios'] }}</span></td>
                                <td>{{ number_format($mes['total_base'], 2) }} €</td>
                                <td>{{ number_format($mes['porcentaje'], 2) }}%</td>
                                <td style="color: #2ecc71; font-weight: bold; font-size: 1.05rem;">
                                    {{ number_format($mes['total_comision'], 2) }} €
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="dashboard-card">
        <h3>Desglose de Servicios Finalizados con Comisión</h3>
        @if($comisiones->isEmpty())
            <p class="text-empty">No hay servicios finalizados registrados para tus comunidades.</p>
        @else
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Fecha Finalización</th>
                            <th>Comunidad</th>
                            <th>Tipo de Servicio</th>
                            <th>Precio Base</th>
                            <th>% Aplicado</th>
                            <th>Importe Comisión</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comisiones as $comision)
                            @php
                                $inc = $comision->incidencia;
                                $fechaFin = $inc->updated_at ?? $comision->created_at;
                                $precioBase = (float) ($inc->especialidad->precio_base ?? 0);
                            @endphp
                            <tr>
                                <td class="table-subtext">{{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y H:i') }}</td>
                                <td>{{ $inc->comunidad->nombre ?? 'Sin comunidad' }}</td>
                                <td>{{ $inc->especialidad->nombre_especialidad ?? 'Servicio general' }}</td>
                                <td>{{ number_format($precioBase, 2) }} €</td>
                                <td>{{ number_format($gestora->porcentaje_comision, 2) }}%</td>
                                <td style="color: #ff6600; font-weight: bold;">{{ number_format($comision->importe, 2) }} €</td>
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