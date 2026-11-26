@extends('layouts.app')

@section('content')
<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Panel de <span>Administración</span></h1>
            <p class="hero-subtitle">Gestión centralizada de avisos, filtrado por estados y asignación de técnicos.</p>
        </div>
        <div>
            <a href="{{ route('incidencias.create') }}" class="btn-submit">+ Crear Aviso Manual</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success" style="color: #22c55e; font-weight: 600; margin-bottom: 25px; background: #052e16; padding: 14px 18px; border-radius: 8px; border: 1px solid #22c55e; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="filter-bar">
        <span class="filter-label">Filtrar por estado:</span>
        <a href="{{ route('admin.dashboard', request()->except('estado')) }}" class="filter-link {{ empty($filtroEstado) ? 'active' : '' }}">Todos</a>
        <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['estado' => 'Pendiente'])) }}" class="filter-link {{ $filtroEstado === 'Pendiente' ? 'active' : '' }}">Pendientes</a>
        <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['estado' => 'Asignada'])) }}" class="filter-link {{ $filtroEstado === 'Asignada' ? 'active' : '' }}">Asignadas</a>
        <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['estado' => 'En curso'])) }}" class="filter-link {{ $filtroEstado === 'En curso' ? 'active' : '' }}">En curso</a>
        <a href="{{ route('admin.dashboard', array_merge(request()->query(), ['estado' => 'Finalizada'])) }}" class="filter-link {{ $filtroEstado === 'Finalizada' ? 'active' : '' }}">Finalizadas</a>
    </div>

    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 20px; padding: 12px; background: #111; border: 1px solid #262626; border-radius: 8px;">
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <span style="color: #9ca3af; font-size: 0.85rem; font-weight: 600;">Urgencia / Asignación:</span>
            <a href="{{ route('admin.dashboard', request()->except(['urgencia', 'asignacion'])) }}" class="filter-link {{ empty($filtroUrgencia) && empty($filtroAsignacion) ? 'active' : '' }}">Todas</a>
            <a href="{{ route('admin.dashboard', array_merge(request()->except('asignacion'), ['urgencia' => 'urgente'])) }}" class="filter-link {{ ($filtroUrgencia ?? '') === 'urgente' ? 'active' : '' }}">Urgentes</a>
            <a href="{{ route('admin.dashboard', array_merge(request()->except('asignacion'), ['urgencia' => 'estandar'])) }}" class="filter-link {{ ($filtroUrgencia ?? '') === 'estandar' ? 'active' : '' }}">Estándar</a>
            <a href="{{ route('admin.dashboard', array_merge(request()->except('urgencia'), ['asignacion' => 'sin-asignar'])) }}" class="filter-link {{ ($filtroAsignacion ?? '') === 'sin-asignar' ? 'active' : '' }}">Sin técnico</a>
            <a href="{{ route('admin.dashboard', array_merge(request()->except('urgencia'), ['asignacion' => 'asignado'])) }}" class="filter-link {{ ($filtroAsignacion ?? '') === 'asignado' ? 'active' : '' }}">Asignados</a>
        </div>

        <form action="{{ route('admin.dashboard') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            @if(!empty($filtroEstado))
                <input type="hidden" name="estado" value="{{ $filtroEstado }}">
            @endif
            @if(!empty($filtroUrgencia))
                <input type="hidden" name="urgencia" value="{{ $filtroUrgencia }}">
            @endif
            @if(!empty($filtroAsignacion))
                <input type="hidden" name="asignacion" value="{{ $filtroAsignacion }}">
            @endif
            <input type="text" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Buscar código, servicio, técnico..." class="form-control btn-sm" style="min-width: 220px;">
            <button type="submit" class="btn-submit btn-sm">Buscar</button>
            <a href="{{ route('admin.dashboard') }}" class="filter-link">Limpiar</a>
        </form>
    </div>

    <div class="dashboard-card">
        <h3>Listado Global de Incidencias</h3>
        @if($incidencias->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No hay incidencias registradas con este filtro.</p>
        @else
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Servicio</th>
                            <th>Urgencia</th>
                            <th>Fecha Cita</th>
                            <th>Estado</th>
                            <th>Técnico</th>
                            <th class="action-cell">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incidencias as $inc)
                            <tr>
                                <td class="table-code">{{ $inc->localizador }}</td>
                                <td>{{ $inc->especialidad->nombre_especialidad ?? 'General' }}</td>
                                <td class="table-urgency {{ strtolower($inc->tipo_urgencia) === 'urgente' ? 'urgente' : 'estandar' }}">
                                    {{ $inc->tipo_urgencia }}
                                </td>
                                <td class="table-date">{{ date('d/m/Y H:i', strtotime($inc->fecha_servicio)) }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($inc->estado) {
                                            'Pendiente'  => 'badge-pendiente',
                                            'Asignada'   => 'badge-asignada',
                                            'En curso'   => 'badge-encurso',
                                            'Finalizada' => 'badge-finalizada',
                                            'Cancelada'  => 'badge-cancelada',
                                            default      => ''
                                        };
                                    @endphp
                                    <span class="badge-status {{ $badgeClass }}">
                                        {{ $inc->estado }}
                                    </span>
                                </td>

                                <td class="table-subtext">
                                    {{ $inc->tecnico->nombre ?? 'Sin asignar' }} -
                                    {{ $inc->tecnico->perfilTecnico->especialidad->nombre_especialidad ?? 'General' }}
                                </td>

                                <td class="action-cell">
                                    @if(in_array($inc->estado, ['Pendiente', 'Asignada']))
                                        <div class="action-group">
                                            <form action="{{ route('admin.incidencias.asignar', $inc->id) }}" method="POST" class="action-group">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="incidencia_id" value="{{ $inc->id }}">

                                                <select name="tecnico_id" required class="form-control btn-sm">
                                                    <option value="">Técnico...</option>
                                                    @foreach ($tecnicos as $tec)
                                                        <option value="{{ $tec->id }}" {{ $inc->tecnico_id == $tec->id ? 'selected' : '' }}>
                                                            {{ $tec->nombre }} - {{ $tec->perfilTecnico->especialidad->nombre_especialidad ?? 'General' }}
                                                        </option>
                                                    @endforeach
                                                </select>

                                                <button type="submit" class="btn-submit btn-sm">Asignar</button>
                                            </form>

                                            <form action="{{ route('incidencias.estado', $inc->id) }}" method="POST" onsubmit="return confirm('¿Cancelar esta incidencia?');">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="estado" value="Cancelada">
                                                <button type="submit" class="btn-danger" style="background: #ef4444; color: #fff; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer;">Cancelar</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="table-subtext">No modificable</span>
                                    @endif
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