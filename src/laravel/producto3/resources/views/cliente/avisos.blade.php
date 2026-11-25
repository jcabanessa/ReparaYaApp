@extends('layouts.app')
@section('title','Mis avisos')
@section('content')<div class="heading"><div><p class="eyebrow">Panel del cliente</p><h1>Mis avisos</h1><p class="muted">Consulta tus incidencias y su historial.</p></div><a class="btn" href="{{ route('cliente.crear') }}">Nueva solicitud</a></div>
<div class="card table-scroll"><table><thead><tr><th>Localizador</th><th>Servicio</th><th>Fecha</th><th>Urgencia</th><th>Estado</th><th></th></tr></thead><tbody>
@forelse($avisos as $i)<tr><td><strong>{{ $i->localizador }}</strong></td><td>{{ $i->especialidad?->nombre_especialidad }}</td>
<td>{{ $i->fecha_servicio?->format('d/m/Y H:i') }}</td><td>{{ $i->tipo_urgencia }}</td>
<td><span class="chip">{{ $i->estado }}</span></td><td><a href="{{ route('cliente.ver',$i) }}">Detalle</a></td></tr>
@empty<tr><td colspan="6">Todavía no tienes avisos registrados.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $avisos->links() }}</div>@endsection
