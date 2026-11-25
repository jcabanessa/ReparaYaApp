@extends('layouts.app')
@section('title','Liquidaciones mensuales')
@section('content')<p class="eyebrow">Comisiones por servicios finalizados</p><h1>Liquidaciones mensuales</h1>
@if(isset($gestoras))<form class="filter" method="get"><label>Empresa gestora<select name="gestora_id"><option value="">Todas</option>
@foreach($gestoras as $g)<option value="{{ $g->id }}" @selected($id===$g->id)>{{ $g->nombre }}</option>@endforeach</select></label><button class="btn">Filtrar</button></form>@endif
<div class="card"><h2>Resumen por mes</h2><div class="grid cols3">@forelse($data['totales'] as $mes=>$t)<div class="mini-card stat"><span>{{ $mes }}</span>
<b>{{ number_format($t['importe'],2,',','.') }} €</b><p class="muted">{{ $t['servicios'] }} intervenciones</p></div>
@empty<p>Sin comisiones generadas.</p>@endforelse</div><p class="total">Total acumulado: {{ number_format($data['total'],2,',','.') }} €</p></div>
<div class="card table-scroll"><h2>Detalle de comisiones</h2><table><thead><tr><th>Finalización</th><th>Gestora</th><th>Comunidad</th><th>Servicio</th><th>Precio base</th><th>%</th><th>Importe</th></tr></thead><tbody>
@forelse($data['items'] as $c)<tr><td>{{ $c->fecha_finalizacion?->format('d/m/Y') }}</td><td>{{ $c->gestora?->nombre }}</td>
<td>{{ $c->incidencia?->comunidad?->nombre }}</td><td>{{ $c->incidencia?->especialidad?->nombre_especialidad }}</td>
<td>{{ number_format((float)$c->precio_base_aplicado,2,',','.') }} €</td><td>{{ number_format((float)$c->porcentaje_aplicado,2,',','.') }} %</td>
<td><b>{{ number_format((float)$c->importe,2,',','.') }} €</b></td></tr>
@empty<tr><td colspan="7">Aún no hay servicios finalizados con comisión.</td></tr>@endforelse</tbody></table></div>@endsection
