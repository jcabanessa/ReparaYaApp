@extends('layouts.app')
@section('title','Incidencias de administración')
@section('content')
<div class="p3-module home-container">
<div class="heading"><h1>Incidencias</h1><a class="btn" href="{{ route('admin.nueva') }}">Crear aviso</a></div>
<form class="filter" method="get"><label>Estado<select name="estado"><option value="">Todos</option>@foreach(['Pendiente','Asignada','En curso','Finalizada','Cancelada'] as $e)
<option @selected(request('estado')===$e)>{{ $e }}</option>@endforeach</select></label><button class="btn secondary">Filtrar</button></form>
<div class="card table-scroll"><table><thead><tr><th>Localizador</th><th>Cliente</th><th>Servicio</th><th>Fecha</th><th>Estado</th><th>Gestión</th></tr></thead><tbody>
@forelse($avisos as $i)<tr><td><a href="{{ route('admin.ver',$i) }}">{{ $i->localizador }}</a></td><td>{{ $i->cliente?->nombre }}</td>
<td>{{ $i->especialidad?->nombre_especialidad }}</td><td>{{ $i->fecha_servicio?->format('d/m/Y H:i') }}</td><td><span class="chip">{{ $i->estado }}</span></td><td>
@if(in_array($i->estado,['Pendiente','Asignada'],true))<div class="actions"><a href="{{ route('admin.editar',$i) }}">Editar</a><form method="post" action="{{ route('admin.asignar',$i) }}">@csrf
<select name="tecnico_id" aria-label="Asignar técnico" required><option value="">Técnico…</option>@foreach($tecnicos->where('especialidad_id',$i->especialidad_id) as $t)<option value="{{ $t->id }}">{{ $t->nombre_completo }}</option>@endforeach</select>
<button class="btn small">Asignar</button></form>
<form method="post" action="{{ route('admin.cancelar',$i) }}" onsubmit="return confirm('¿Cancelar incidencia?')">@csrf<button class="btn danger small">Cancelar</button></form></div>@endif
</td></tr>@empty<tr><td colspan="6">No hay incidencias.</td></tr>@endforelse</tbody></table></div><div class="pagination">{{ $avisos->links() }}</div>
</div>
@endsection
