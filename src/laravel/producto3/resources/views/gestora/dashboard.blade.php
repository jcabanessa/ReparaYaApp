@extends('layouts.app')
@section('title','Panel de gestora')
@section('content')<p class="eyebrow">Empresas administradoras de fincas</p><div class="heading"><div><h1>{{ $gestora->nombre }}</h1>
<p class="muted">Comisión acordada: {{ $gestora->porcentaje_comision }} %</p></div>
<div class="actions"><a class="btn" href="{{ route('gestora.crear') }}">Nueva asistencia</a><a class="btn secondary" href="{{ route('gestora.liquidaciones') }}">Liquidaciones</a></div></div>
<div class="card"><h2>Mis comunidades</h2><div class="grid cols3">@foreach($gestora->comunidades as $c)<div class="mini-card"><b>{{ $c->nombre }}</b>
<p>{{ $c->direccion }}</p><span class="chip">{{ $c->zona }}</span></div>@endforeach</div></div>
<div class="card table-scroll"><h2>Incidencias de mis comunidades</h2><table><thead><tr><th>Localizador</th><th>Comunidad</th><th>Servicio</th><th>Fecha</th><th>Estado</th><th></th></tr></thead><tbody>
@forelse($avisos as $i)<tr><td>{{ $i->localizador }}</td><td>{{ $i->comunidad?->nombre }}</td>
<td>{{ $i->especialidad?->nombre_especialidad }}</td><td>{{ $i->fecha_servicio?->format('d/m/Y H:i') }}</td>
<td><span class="chip">{{ $i->estado }}</span></td><td><a href="{{ route('gestora.ver',$i) }}">Detalle</a></td></tr>
@empty<tr><td colspan="6">No existen incidencias de esta gestora.</td></tr>@endforelse</tbody></table></div>@endsection
