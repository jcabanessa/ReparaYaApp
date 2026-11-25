@extends('layouts.app')
@section('title','Mi agenda')
@section('content')<p class="eyebrow">Panel del técnico</p><h1>Mi agenda</h1><div class="card table-scroll"><table>
<thead><tr><th>Incidencia</th><th>Fecha</th><th>Cliente</th><th>Especialidad</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
@forelse($incidencias as $i)<tr><td>{{ $i->localizador }}<div class="muted small">{{ $i->descripcion }}</div></td>
<td>{{ $i->fecha_servicio?->format('d/m/Y H:i') }}</td><td>{{ $i->cliente?->nombre }}</td>
<td>{{ $i->especialidad?->nombre_especialidad }}</td><td><span class="chip">{{ $i->estado }}</span></td><td>
@if(in_array($i->estado,['Asignada','En curso'],true))<form method="post" action="{{ route('tecnico.cambiar_estado') }}">@csrf
<input type="hidden" name="incidencia_id" value="{{ $i->id }}">
<input type="hidden" name="nuevo_estado" value="{{ $i->estado==='Asignada'?'En curso':'Finalizada' }}">
<button class="btn small">{{ $i->estado==='Asignada'?'Iniciar intervención':'Finalizar intervención' }}</button></form>@else—@endif
</td></tr>@empty<tr><td colspan="6">No tienes intervenciones asignadas.</td></tr>@endforelse</tbody></table></div>@endsection
