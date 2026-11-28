@extends('layouts.app')

@section('content')
@php
    $estadoActual = $estado ?? '';
    $urgenciaActual = $urgencia ?? '';
    $buscarActual = $buscar ?? '';

    $buildUrl = function (array $overrides = [], array $remove = []) use ($estadoActual, $urgenciaActual, $buscarActual) {
        $params = array_filter([
            'estado'   => $estadoActual,
            'urgencia' => $urgenciaActual,
            'buscar'   => $buscarActual,
        ], fn($v) => $v !== '' && $v !== null);

        foreach ($remove as $key) {
            unset($params[$key]);
        }

        foreach ($overrides as $key => $val) {
            if ($val === '' || $val === null) {
                unset($params[$key]);
            } else {
                $params[$key] = $val;
            }
        }

        return route('tecnico.agenda', $params);
    };
@endphp

<div class="home-container">
    <div class="page-header">
        <div>
            <h1 class="hero-title">Mi <span>Agenda de Trabajos</span></h1>
            <p class="hero-subtitle">Consulta y actualiza el estado de las incidencias que tienes asignadas.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success" style="color: #22c55e; font-weight: 600; margin-bottom: 25px; background: #052e16; padding: 14px 18px; border-radius: 8px; border: 1px solid #22c55e; text-align: center;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 20px; padding: 12px; background: #111; border: 1px solid #262626; border-radius: 8px;">
        <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            <a href="{{ route('tecnico.agenda') }}" class="filter-link {{ $estadoActual === '' && $urgenciaActual === '' && $buscarActual === '' ? 'active' : '' }}">Todos</a>
            <a href="{{ $buildUrl(['estado' => 'Asignada']) }}" class="filter-link {{ strcasecmp($estadoActual, 'Asignada') === 0 ? 'active' : '' }}">Asignadas</a>
            <a href="{{ $buildUrl(['estado' => 'En curso']) }}" class="filter-link {{ strcasecmp($estadoActual, 'En curso') === 0 ? 'active' : '' }}">En curso</a>
            <a href="{{ $buildUrl(['estado' => 'Finalizada']) }}" class="filter-link {{ strcasecmp($estadoActual, 'Finalizada') === 0 ? 'active' : '' }}">Finalizadas</a>
            <a href="{{ $buildUrl(['urgencia' => 'urgente']) }}" class="filter-link {{ $urgenciaActual === 'urgente' ? 'active' : '' }}">Urgentes</a>
            <a href="{{ $buildUrl(['urgencia' => 'estandar']) }}" class="filter-link {{ $urgenciaActual === 'estandar' ? 'active' : '' }}">Estándar</a>
        </div>

        <form action="{{ route('tecnico.agenda') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
            @if($estadoActual !== '')
                <input type="hidden" name="estado" value="{{ $estadoActual }}">
            @endif
            @if($urgenciaActual !== '')
                <input type="hidden" name="urgencia" value="{{ $urgenciaActual }}">
            @endif
            <input type="text" name="buscar" value="{{ $buscarActual }}" placeholder="Buscar código, dirección o servicio..." class="form-control btn-sm" style="min-width: 210px;">
            <button type="submit" class="btn-submit btn-sm">Buscar</button>
            <a href="{{ route('tecnico.agenda') }}" class="filter-link">Limpiar</a>
        </form>
    </div>

    <div class="dashboard-card">
        <h3>Incidencias Asignadas</h3>
        @if($incidencias->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No tienes incidencias asignadas con este filtro.</p>
        @else
            <div class="table-responsive" style="margin-top: 20px;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Urgencia</th>
                            <th>Fecha Cita</th>
                            <th>Dirección</th>
                            <th>Estado Actual</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incidencias as $inc)
                            <tr>
                                <td class="table-code">{{ $inc->localizador }}</td>
                                <td class="table-urgency {{ strtolower($inc->tipo_urgencia) === 'urgente' ? 'urgente' : 'estandar' }}">
                                    {{ $inc->tipo_urgencia }}
                                </td>
                                <td class="table-date">{{ date('d/m/Y H:i', strtotime($inc->fecha_servicio)) }}</td>
                                <td>{{ $inc->direccion }}</td>
                                <td>
                                    <span class="badge-status badge-{{ strtolower(str_replace(' ', '', $inc->estado)) }}">
                                        {{ $inc->estado }}
                                    </span>
                                </td>
                                <td>
                                    <form action="{{ route('incidencias.estado', $inc->id) }}" method="POST" style="display: flex; gap: 8px; align-items: center;">
                                        @csrf
                                        @method('PUT')
                                        <select name="estado" class="form-control btn-sm" required>
                                            <option value="Asignada" {{ $inc->estado === 'Asignada' ? 'selected' : '' }}>Asignada</option>
                                            <option value="En curso" {{ $inc->estado === 'En curso' ? 'selected' : '' }}>En curso</option>
                                            <option value="Finalizada" {{ $inc->estado === 'Finalizada' ? 'selected' : '' }}>Finalizada</option>
                                        </select>
                                        <button type="submit" class="btn-submit btn-sm">Actualizar</button>
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