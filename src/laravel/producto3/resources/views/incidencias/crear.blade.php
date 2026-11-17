@extends('layouts.app')

@section('content')
<div class="home-container" style="max-width: 700px;">
    <h1 class="hero-title">Nueva <span>Incidencia</span></h1>
    <p class="hero-subtitle">Completa los datos del aviso de servicio.</p>

    @if(session('error_incidencia'))
        <div style="background: #2b0b0b; border: 1px solid #ef4444; color: #ef4444; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
            {{ session('error_incidencia') }}
        </div>
    @endif

    <div class="dashboard-card">
        <form action="{{ route('incidencias.store') }}" method="POST">
            @csrf

            {{-- CAMPOS EXCLUSIVOS PARA EL ADMINISTRADOR --}}
            @if(auth()->user()->rol === 'admin')
                <div style="background: #141414; padding: 15px; border-radius: 6px; border: 1px solid #2a2a2a; margin-bottom: 20px;">
                    <h4 style="color: #ff6600; margin-top: 0; margin-bottom: 12px; font-size: 0.95rem;">Panel de Administración - Asignación de Cliente y Técnico</h4>
                    
                    <!-- Selector de Cliente -->
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="cliente_id" class="form-label" style="color: #fff; display: block; margin-bottom: 5px;">Cliente Titular *</label>
                        <select name="cliente_id" id="cliente_id" class="form-control" required style="width: 100%; padding: 8px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                            <option value="">Selecciona el cliente que solicita el aviso...</option>
                            @foreach($clientes as $cli)
                                <option value="{{ $cli->id }}" {{ old('cliente_id') == $cli->id ? 'selected' : '' }}>
                                    {{ $cli->nombre }} ({{ $cli->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Selector opcional de Técnico -->
                    <div class="form-group">
                        <label for="tecnico_id" class="form-label" style="color: #fff; display: block; margin-bottom: 5px;">Preasignar Técnico (Opcional)</label>
                        <select name="tecnico_id" id="tecnico_id" class="form-control" style="width: 100%; padding: 8px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                            <option value="">Sin preasignar (El aviso quedará en estado Pendiente)</option>
                            @foreach($tecnicos as $tec)
                                <option value="{{ $tec->usuario_id }}" {{ old('tecnico_id') == $tec->usuario_id ? 'selected' : '' }}>
                                    {{ $tec->nombre_completo }} - Especialidad: {{ $tec->especialidad->nombre_especialidad ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            <!-- Selector de Comunidad (Opcional) -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="comunidad_id" class="form-label" style="color: #fff; display: block; margin-bottom: 5px;">Comunidad (Si aplica)</label>
                <select name="comunidad_id" id="comunidad_id" class="form-control" style="width: 100%; padding: 8px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                    <option value="">Servicio particular (Sin comunidad asociada)</option>
                    @foreach($comunidades as $com)
                        <option value="{{ $com->id }}" {{ old('comunidad_id') == $com->id ? 'selected' : '' }}>
                            {{ $com->nombre }} ({{ $com->zona }}) - {{ $com->direccion }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Especialidad / Tipo de Servicio -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="especialidad_id" class="form-label" style="color: #fff; display: block; margin-bottom: 5px;">Tipo de Servicio *</label>
                <select name="especialidad_id" id="especialidad_id" class="form-control" required style="width: 100%; padding: 8px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                    <option value="">Selecciona una especialidad...</option>
                    @foreach($especialidades as $esp)
                        <option value="{{ $esp->id }}" {{ old('especialidad_id') == $esp->id ? 'selected' : '' }}>
                            {{ $esp->nombre_especialidad }} (Precio Base: {{ number_format($esp->precio_base, 2) }} €)
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tipo de Urgencia -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="tipo_urgencia" class="form-label" style="color: #fff; display: block; margin-bottom: 5px;">Urgencia *</label>
                <select name="tipo_urgencia" id="tipo_urgencia" class="form-control" required style="width: 100%; padding: 8px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
                    <option value="Estándar">Estándar (Requiere 48h de antelación)</option>
                    <option value="Urgente">Urgente</option>
                </select>
            </div>

            <!-- Fecha de Servicio -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="fecha_solicitud" class="form-label" style="color: #fff; display: block; margin-bottom: 5px;">Fecha Solicitada para la Cita *</label>
                <input type="date" name="fecha_solicitud" id="fecha_solicitud" value="{{ old('fecha_solicitud') }}" required style="width: 100%; padding: 8px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
            </div>

            <!-- Dirección -->
            <div class="form-group" style="margin-bottom: 15px;">
                <label for="direccion" class="form-label" style="color: #fff; display: block; margin-bottom: 5px;">Dirección del Servicio *</label>
                <input type="text" name="direccion" id="direccion" value="{{ old('direccion') }}" placeholder="Calle, número, piso..." required style="width: 100%; padding: 8px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">
            </div>

            <!-- Descripción -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="descripcion" class="form-label" style="color: #fff; display: block; margin-bottom: 5px;">Descripción de la Avería *</label>
                <textarea name="descripcion" id="descripcion" rows="4" placeholder="Detalla brevemente el problema..." required style="width: 100%; padding: 8px; background: #0b0b0b; border: 1px solid #2a2a2a; color: #fff; border-radius: 4px;">{{ old('descripcion') }}</textarea>
            </div>

            <button type="submit" style="background: #ff6600; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-weight: bold;">Registrar Incidencia</button>
        </form>
    </div>
</div>
@endsection