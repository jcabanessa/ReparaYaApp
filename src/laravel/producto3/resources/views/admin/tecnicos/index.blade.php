@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Gestión de <span>Personal Técnico</span></h1>
            <p class="hero-subtitle">Alta, asignación de especialidad y disponibilidad operativa de técnicos.</p>
        </div>
        <div>
            <a href="{{ route('admin.especialidades.index') }}" class="btn-orange">Ir a Servicios y Tarifas</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <ul style="list-style: none; margin: 0; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="dashboard-card" style="margin-bottom: 35px;">
        <h3>Dar de Alta Nuevo Técnico</h3>
        <form action="{{ route('admin.tecnicos.store') }}" method="POST">
            @csrf
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label highlight">Nombre Completo</label>
                    <input type="text" name="nombre_completo" required class="form-control" placeholder="Ej. Laura Gómez" value="{{ old('nombre_completo') }}">
                </div>
                <div class="form-group">
                    <label class="form-label highlight">Especialidad Asignada</label>
                    <select name="especialidad_id" required class="form-control">
                        <option value="">-- Selecciona especialidad --</option>
                        @foreach($especialidades as $esp)
                            <option value="{{ $esp->id }}" {{ old('especialidad_id') == $esp->id ? 'selected' : '' }}>
                                {{ $esp->nombre_especialidad }} ({{ number_format($esp->precio_base, 2) }} €)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label highlight">Correo Electrónico (Acceso)</label>
                    <input type="email" name="email" required class="form-control" placeholder="tecnico@reparaya.com" value="{{ old('email') }}">
                </div>
                <div class="form-group">
                    <label class="form-label highlight">Contraseña Inicial</label>
                    <input type="password" name="password" required minlength="6" class="form-control" placeholder="Mínimo 6 caracteres">
                </div>
                <div class="form-group">
                    <label class="form-label highlight">Teléfono (Opcional)</label>
                    <input type="text" name="telefono" class="form-control" placeholder="Ej. 600123456" value="{{ old('telefono') }}">
                </div>
                <div class="form-group" style="display: flex; align-items: center; gap: 10px; padding-top: 25px;">
                    <input type="checkbox" id="disponible" name="disponible" value="1" checked style="width: 18px; height: 18px; accent-color: #ff6600;">
                    <label for="disponible" class="form-label highlight" style="margin: 0; cursor: pointer;">Disponible para asignación inmediata</label>
                </div>
            </div>
            <button type="submit" class="btn-submit">Guardar Técnico</button>
        </form>
    </div>

    <div class="dashboard-card">
        <h3>Plantilla de Técnicos</h3>
        @if($tecnicos->isEmpty())
            <p class="text-empty">No hay técnicos registrados actualmente.</p>
        @else
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre Completo</th>
                            <th>Email Acceso</th>
                            <th>Especialidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tecnicos as $tec)
                            <tr>
                                <td class="table-subtext">#{{ $tec->id }}</td>
                                <form action="{{ route('admin.tecnicos.update', $tec->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td>
                                        <input type="text" name="nombre_completo" value="{{ $tec->nombre_completo }}" required class="form-control" style="padding: 6px 10px;">
                                    </td>
                                    <td class="table-subtext">
                                        {{ $tec->usuario->email ?? 'Sin cuenta vinculada' }}
                                    </td>
                                    <td>
                                        <select name="especialidad_id" required class="form-control" style="padding: 6px 10px;">
                                            @foreach($especialidades as $esp)
                                                <option value="{{ $esp->id }}" {{ $tec->especialidad_id == $esp->id ? 'selected' : '' }}>
                                                    {{ $esp->nombre_especialidad }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="disponible" class="form-control" style="padding: 6px 10px; color: {{ $tec->disponible ? '#2ecc71' : '#e74c3c' }}; font-weight: bold;">
                                            <option value="1" {{ $tec->disponible ? 'selected' : '' }}>Disponible</option>
                                            <option value="0" {{ !$tec->disponible ? 'selected' : '' }}>No disponible</option>
                                        </select>
                                    </td>
                                    <td style="display: flex; gap: 8px; align-items: center;">
                                        <button type="submit" class="btn-orange" style="padding: 6px 12px; font-size: 0.85rem;">Actualizar</button>
                                </form>

                                <form action="{{ route('admin.tecnicos.destroy', $tec->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este técnico?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-submit" style="padding: 6px 12px; font-size: 0.85rem; background-color: #c0392b; margin: 0;">Eliminar</button>
                                </form>
                                    </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection