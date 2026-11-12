@extends('layouts.app')

@section('content')
<div class="home-container" style="max-width: 700px;">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Nueva <span>Solicitud de Reparación</span></h1>
            <p class="hero-subtitle">Completa los detalles de la incidencia para tramitarla con nuestros técnicos.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert-error">
            <ul style="list-style: none; margin: 0; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dashboard-card">
        <form action="{{ route('incidencias.store') }}" method="POST">
            @csrf

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="tipo_urgencia" class="form-label">Tipo de Urgencia *</label>
                <select name="tipo_urgencia" id="tipo_urgencia" class="form-control" required>
                    <option value="Estándar">Estándar</option>
                    <option value="Urgente">Urgente</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="fecha_servicio" class="form-label">Fecha y Hora Preferida para la Cita *</label>
                <input type="datetime-local" name="fecha_servicio" id="fecha_servicio" class="form-control" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="direccion" class="form-label">Dirección del Inmueble *</label>
                <input type="text" name="direccion" id="direccion" class="form-control" placeholder="Calle, Número, Piso, Ciudad" required>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="descripcion" class="form-label">Descripción de la Avería / Incidencia *</label>
                <textarea name="descripcion" id="descripcion" rows="4" class="form-control" placeholder="Describe brevemente el problema..." required></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label for="especialidad_id" class="form-label">Especialidad / Tipo de Servicio *</label>
                <select name="especialidad_id" id="especialidad_id" class="form-control" required>
                    <option value="">Selecciona una especialidad...</option>
                    @foreach(\App\Models\Especialidad::all() as $esp)
                        <option value="{{ $esp->id }}">
                            {{ $esp->nombre }} ({{ number_format($esp->precio_base, 2) }} €)
                        </option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->rol === 'admin')
                <div class="form-group" style="margin-bottom: 20px;">
                    <label for="empresa_gestora_id" class="form-label">Empresa Gestora Asociada (Opcional)</label>
                    <select name="empresa_gestora_id" id="empresa_gestora_id" class="form-control">
                        <option value="">Ninguna / Directa</option>
                        @foreach(App\Models\EmpresaGestora::all() as $gestora)
                            <option value="{{ $gestora->id }}">{{ $gestora->nombre_empresa }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="submit" class="btn-submit" style="width: 100%;">Registrar Incidencia</button>
        </form>
    </div>
</div>
@endsection