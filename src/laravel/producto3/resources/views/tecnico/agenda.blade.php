@extends('layouts.app')

@section('content')
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

    <div class="dashboard-card">
        <h3>Incidencias Asignadas</h3>
        @if($incidencias->isEmpty())
            <p class="text-empty" style="margin-top: 15px;">No tienes incidencias asignadas en este momento.</p>
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