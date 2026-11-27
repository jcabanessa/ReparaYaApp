@extends('layouts.app')
@section('title','Detalle de aviso')
@section('content')
<div class="p3-module home-container">
<div class="heading"><div><p class="eyebrow">Detalle de incidencia</p><h1>{{ $incidencia->localizador }}</h1></div><span class="chip large">{{ $incidencia->estado }}</span></div>
<div class="grid cols2"><div class="card"><h2>Información</h2><dl>
<dt>Servicio</dt><dd>{{ $incidencia->especialidad?->nombre_especialidad }}</dd>
<dt>Urgencia</dt><dd>{{ $incidencia->tipo_urgencia }}</dd>
<dt>Fecha</dt><dd>{{ $incidencia->fecha_servicio?->format('d/m/Y H:i') }}</dd>
<dt>Franja</dt><dd>{{ $incidencia->franja_horaria }}</dd>
<dt>Dirección</dt><dd>{{ $incidencia->direccion }}</dd>
<dt>Contacto</dt><dd>{{ $incidencia->telefono_contacto }}</dd>
<dt>Precio aplicado</dt><dd>{{ number_format((float)$incidencia->precio_aplicado,2,',','.') }} €</dd>
<dt>Descripción</dt><dd>{{ $incidencia->descripcion }}</dd>
</dl>
@if(in_array($incidencia->estado,['Pendiente','Asignada'],true) && in_array(auth()->user()->rol,['particular','gestora','admin'],true))
@php $cancelRoute=match(auth()->user()->rol){ 'particular'=>route('cliente.cancelar',$incidencia),'gestora'=>route('gestora.cancelar',$incidencia),default=>route('admin.cancelar',$incidencia)}; @endphp
<form method="post" action="{{ $cancelRoute }}" onsubmit="return confirm('¿Cancelar esta incidencia?')">@csrf<button class="btn danger">Solicitar cancelación</button></form>
@endif</div><div class="card"><h2>Historial de estados</h2><ol class="timeline">
@forelse($incidencia->historial as $h)<li><b>{{ $h->nuevo_estado }}</b> <small>{{ $h->created_at?->format('d/m/Y H:i') }}</small><div class="muted">{{ $h->autor?->nombre }} · {{ $h->observaciones }}</div></li>
@empty<li>Aún no hay movimientos de estado.</li>@endforelse</ol></div></div>
</div>
@endsection
