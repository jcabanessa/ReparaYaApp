@extends('layouts.app')
@section('content')
<div class="p3-module home-container">

<h1>Liquidaciones mensuales · Administración</h1>
<form method="GET" action="{{ route('admin.liquidaciones') }}">
<label>Empresa gestora<select name="gestora_id"><option value="">Todas</option>
@foreach($gestoras as $g)<option value="{{ $g->id }}" @selected((string)$id === (string)$g->id)>{{ $g->nombre }}</option>@endforeach
</select></label><button>Filtrar</button></form>
<section class="card"><h2>Resumen mensual</h2>
@forelse($porMes as $mes=>$datos)<p>{{ $mes }} — {{ $datos['servicios'] }} servicios — <strong>{{ number_format($datos['importe'],2,',','.') }} €</strong></p>
@empty<p>Todavía no existen comisiones de servicios finalizados.</p>@endforelse
<p><strong>Total: {{ number_format($total,2,',','.') }} €</strong></p></section>
<section class="card"><h2>Detalle</h2><table><thead><tr><th>Mes</th><th>Gestora</th><th>Comunidad</th><th>Servicio</th><th>Precio</th><th>% actual</th><th>Comisión</th></tr></thead><tbody>
@forelse($comisiones as $c)
@php $fecha=$c->incidencia?->finalizada_at ?? $c->incidencia?->updated_at ?? $c->created_at; @endphp
<tr><td>{{ $fecha ? \Carbon\Carbon::parse($fecha)->format('Y-m') : '-' }}</td>
<td>{{ $c->empresaGestora?->nombre }}</td><td>{{ $c->incidencia?->comunidad?->nombre }}</td>
<td>{{ $c->incidencia?->especialidad?->nombre_especialidad }}</td>
<td>{{ number_format((float)($c->incidencia?->precio_aplicado ?? $c->incidencia?->especialidad?->precio_base ?? 0),2,',','.') }} €</td>
<td>{{ $c->empresaGestora?->porcentaje_comision }} %</td><td>{{ number_format((float)$c->importe,2,',','.') }} €</td></tr>
@empty<tr><td colspan="7">Sin liquidaciones.</td></tr>@endforelse
</tbody></table><small>Para incidencias antiguas sin fecha explícita de finalización, se utiliza la fecha de la última actualización como referencia. El porcentaje mostrado es el actual de la gestora.</small></section>

</div>
@endsection
