@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Liquidaciones a <span>Empresas Gestoras</span></h1>
            <p class="hero-subtitle">Desglose mensual de comisiones y totales a liquidar por cada empresa gestora.</p>
        </div>
    </div>

    <div class="dashboard-card">
        <h3>Resumen de Liquidaciones por Empresa</h3>
        
        @php
            $gestoras = App\Models\EmpresaGestora::with(['comisiones.incidencia.especialidad', 'comisiones.incidencia.comunidad'])->get();
        @endphp

        @if($gestoras->isEmpty())
            <p class="text-empty">No hay empresas gestoras registradas.</p>
        @else
            @foreach($gestoras as $gestora)
                <div style="margin-top: 30px; background: #0c0c0c; padding: 20px; border-radius: 8px; border: 1px solid #333;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="color: #ff6600; margin: 0;">{{ $gestora->nombre_empresa }} <small style="color: #9ca3af; font-size: 0.85rem;">(CIF: {{ $gestora->cif }} - Coms: {{ $gestora->porcentaje_comision }}%)</small></h3>
                    </div>

                    @php
                        $comisionesGestora = $gestora->comisiones->groupBy(fn($c) => $c->updated_at ? $c->updated_at->format('Y-m') : 'Sin fecha');
                    @endphp

                    @if($comisionesGestora->isEmpty())
                        <p class="text-empty">Sin comisiones generadas para esta gestora.</p>
                    @else
                        @foreach($comisionesGestora as $mes => $comisionesMes)
                            <div style="margin-top: 15px; background: #141414; padding: 12px; border-radius: 6px; border: 1px solid #222;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                    <strong style="color: #fff;">Mes: {{ $mes }}</strong>
                                    <span style="color: #10b981; font-weight: bold;">Total a Liquidar: {{ number_format($comisionesMes->sum('importe'), 2) }} €</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="admin-table" style="font-size: 0.85rem;">
                                        <thead>
                                            <tr>
                                                <th>Incidencia</th>
                                                <th>Fecha Fin</th>
                                                <th>Comunidad</th>
                                                <th>Servicio</th>
                                                <th>Precio Base</th>
                                                <th>Comisión</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($comisionesMes as $com)
                                                <tr>
                                                    <td class="table-code">{{ $com->incidencia->localizador ?? 'N/A' }}</td>
                                                    <td>{{ $com->updated_at ? $com->updated_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                                    <td>{{ $com->incidencia->comunidad->nombre ?? 'N/A' }}</td>
                                                    <td>{{ $com->incidencia->especialidad->nombre_especialidad ?? 'N/A' }}</td>
                                                    <td>{{ number_format($com->incidencia->especialidad->precio_base ?? 0, 2) }} €</td>
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
            @endforeach
        @endif
    </div>
</div>
@endsection