@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Gestión de <span>Servicios y Tarifas</span></h1>
            <p class="hero-subtitle">Mantenimiento del catálogo de especialidades y precios base de referencia.</p>
        </div>
        <div>
            <a href="{{ route('admin.tecnicos.index') }}" class="btn-orange">Ir a Técnicos</a>
        </div>
    </div>

    {{-- Mensajes flash de éxito o error --}}
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
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

    {{-- Formulario de Alta (Create) --}}
    <div class="dashboard-card" style="margin-bottom: 35px;">
        <h3>Añadir Nuevo Tipo de Servicio</h3>
        <form action="{{ route('admin.especialidades.store') }}" method="POST">
            @csrf

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label highlight">Nombre de la Especialidad</label>
                    <input type="text" name="nombre_especialidad" required class="form-control" placeholder="Ej. Fontanería, Electricidad..." value="{{ old('nombre_especialidad') }}">
                </div>
                <div class="form-group">
                    <label class="form-label highlight">Precio Base (€)</label>
                    <input type="number" step="0.01" min="0" name="precio_base" required class="form-control" placeholder="Ej. 45.00" value="{{ old('precio_base') }}">
                </div>
            </div>

            <button type="submit" class="btn-submit">Guardar Especialidad</button>
        </form>
    </div>

    {{-- Listado con Edición Inline y Baja (Read, Update, Delete) --}}
    <div class="dashboard-card">
        <h3>Especialidades Registradas</h3>
        @if($especialidades->isEmpty())
            <p class="text-empty">No hay tipos de servicio dados de alta actualmente.</p>
        @else
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Servicio</th>
                            <th>Precio Base (€)</th>
                            <th>Técnicos Asignados</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($especialidades as $esp)
                            <tr>
                                <td class="table-subtext">#{{ $esp->id }}</td>
                                {{-- Formulario de actualización directa (PUT) --}}
                                <form action="{{ route('admin.especialidades.update', $esp->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td>
                                        <input type="text" name="nombre_especialidad" value="{{ $esp->nombre_especialidad }}" required class="form-control" style="padding: 6px 10px;">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="precio_base" value="{{ number_format($esp->precio_base, 2, '.', '') }}" required class="form-control" style="padding: 6px 10px; max-width: 130px; color: #ff6600; font-weight: bold;">
                                    </td>
                                    <td>
                                        <span class="table-code">{{ $esp->tecnicos_count }}</span>
                                    </td>
                                    <td style="display: flex; gap: 8px; align-items: center;">
                                        <button type="submit" class="btn-orange" style="padding: 6px 12px; font-size: 0.85rem;">Actualizar</button>
                                </form>

                                {{-- Formulario de borrado controlado (DELETE) --}}
                                <form action="{{ route('admin.especialidades.destroy', $esp->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta especialidad?');">
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