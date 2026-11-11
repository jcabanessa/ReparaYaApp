@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Panel de <span>Gestión</span></h1>
            <p class="hero-subtitle">Bienvenida, {{ $gestora->nombre_empresa ?? auth()->user()->nombre }} (CIF: {{ $gestora->cif ?? 'N/A' }}).</p>
        </div>
        <div>
            <span class="badge-status badge-asignada" style="font-size: 1rem; padding: 8px 15px;">
                Comisión Aplicada: <strong>{{ number_format($gestora->porcentaje_comision ?? 0, 2) }}%</strong>
            </span>
        </div>
    </div>

    <!-- Resumen de Comisiones -->
    <div class="dashboard-card">
        <h3>Historial de Comisiones e Incidencias Derivadas</h3>

        @if(!$gestora || $gestora->comisiones->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No hay comisiones registradas para esta empresa gestora todavía.</p>
        @else
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Localizador Incidencia</th>
                            <th>Fecha Servicio</th>
                            <th>Importe Comisión</th>
                            <th>Estado Comisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gestora->comisiones as $comision)
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