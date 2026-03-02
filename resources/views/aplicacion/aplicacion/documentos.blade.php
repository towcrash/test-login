@extends('layouts.app')
@section('tituloPagina', 'Documentos de Colaboradores')
@section('cabecera', 'Documentos de Colaboradores')

@push('estilos')
<style>
    .select2-container--default .select2-selection--multiple .select2-selection__choice,
    .select2-container--default .select2-selection--multiple .select2-selection__rendered,
    .select2-container--default .select2-search--inline .select2-search__field {
        color: #000 !important;
    }
</style>
@endpush

@section('contenido')

<form id="formFiltros" method="GET" action="{{ route('aplicacion.aplicacion.documentos') }}">

    <div class="card card-outline card-secondary mb-3">
        <div class="card-body pb-2">
            <div class="row align-items-end">

                {{-- Fecha desde --}}
                <div class="col-auto mb-2">
                    <label class="small text-muted mb-1 d-block">Desde</label>
                    <input type="date"
                           name="desde"
                           value="{{ $desde }}"
                           class="form-control form-control-sm"
                           style="width:150px;">
                </div>

                {{-- Fecha hasta --}}
                <div class="col-auto mb-2">
                    <label class="small text-muted mb-1 d-block">Hasta</label>
                    <input type="date"
                           name="hasta"
                           value="{{ $hasta }}"
                           class="form-control form-control-sm"
                           style="width:150px;">
                </div>

                {{-- Filtro contratistas--}}
                @if ($contratistasAccesibles->count() > 1)
                <div class="col-auto mb-2">
                    <label class="small text-muted mb-1 d-block">Contratista</label>
                    <select name="contratistas[]"
                            id="selectContratistas"
                            class="form-control form-control-sm select2-multiple"
                            multiple
                            style="min-width:200px; max-width:300px;">
                        @foreach ($contratistasAccesibles as $c)
                            <option value="{{ $c->id }}"
                                {{ in_array($c->id, $filtroContratistas) ? 'selected' : '' }}>
                                {{ $c->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Filtro evaluaciones --}}
                @if ($evaluacionesAccesibles->isNotEmpty())
                <div class="col-auto mb-2">
                    <label class="small text-muted mb-1 d-block">Evaluación</label>
                    <select name="evaluaciones[]"
                            id="selectEvaluaciones"
                            class="form-control form-control-sm select2-multiple"
                            multiple
                            style="min-width:200px; max-width:300px;">
                        @foreach ($evaluacionesAccesibles as $e)
                            <option value="{{ $e->id }}"
                                {{ in_array($e->id, $filtroEvaluaciones) ? 'selected' : '' }}>
                                {{ $e->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Botones --}}
                <div class="col-auto mb-2 d-flex" style="gap:.4rem;">
                    <button type="submit" class="btn btn-sm btn-secondary">
                        <i class="fas fa-filter mr-1"></i> Filtrar
                    </button>
                    <a href="{{ route('aplicacion.aplicacion.documentos') }}"
                       class="btn btn-sm btn-outline-secondary"
                       title="Limpiar filtros / mes actual">
                        <i class="fas fa-times"></i>
                    </a>
                </div>

            </div>

            {{-- Resumen de filtros activos --}}
            @php
                $hayFiltroContratista = !empty($filtroContratistas);
                $hayFiltroEvaluacion  = !empty($filtroEvaluaciones);
            @endphp
            @if ($hayFiltroContratista || $hayFiltroEvaluacion)
                <div class="mt-1" style="font-size:.8rem; color:#6c757d;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Filtrando por:
                    @if ($hayFiltroContratista)
                        <strong>contratistas</strong>
                        ({{ $contratistasAccesibles->whereIn('id', $filtroContratistas)->pluck('nombre')->implode(', ') }})
                    @endif
                    @if ($hayFiltroContratista && $hayFiltroEvaluacion) · @endif
                    @if ($hayFiltroEvaluacion)
                        <strong>evaluaciones</strong>
                        ({{ $evaluacionesAccesibles->whereIn('id', $filtroEvaluaciones)->pluck('nombre')->implode(', ') }})
                    @endif
                </div>
            @endif

        </div>
    </div>

</form>

{{-- Tabla de resultados --}}
@if ($filas->isNotEmpty())
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm mb-2">
            <thead class="thead-light">
                <tr>
                    <th>Colaborador</th>
                    @if ($mostrarContratista)
                        <th>Contratista</th>
                    @endif
                    <th class="text-center">Fecha</th>
                    <th class="text-center">% Aprobación</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($filas as $fila)
                    @php $aprobacion = $fila['doc']->pivot->pAprobacion ?? null; @endphp
                    <tr>
                        <td>{{ $fila['col']->usuario->nombre }}</td>
                        @if ($mostrarContratista)
                            <td>{{ $fila['col']->contratista->nombre }}</td>
                        @endif
                        <td class="text-center">
                            {{ $fila['doc']->fecha ? $fila['doc']->fecha->format('d/m/Y') : '—' }}
                        </td>
                        <td class="text-center">
                            @if (!is_null($aprobacion))
                                <span class="badge badge-{{ $aprobacion >= 70 ? 'success' : ($aprobacion >= 40 ? 'warning' : 'danger') }}">
                                    {{ number_format($aprobacion, 1) }}%
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('documento.documento.view', $fila['doc']) }}"
                               target="_blank"
                               class="btn btn-xs btn-primary"
                               title="Ver documento">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <p class="text-muted">
        <i class="fas fa-inbox mr-1"></i>
        No hay documentos para el período
        <strong>{{ \Carbon\Carbon::parse($desde)->format('d/m/Y') }}</strong>
        al
        <strong>{{ \Carbon\Carbon::parse($hasta)->format('d/m/Y') }}</strong>.
    </p>
@endif

@endsection

@push('acciones')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Auto-submit al cambiar fechas
    document.querySelectorAll('#formFiltros input[type="date"]').forEach(function (input) {
        input.addEventListener('change', function () {
            document.getElementById('formFiltros').submit();
        });
    });

    // Inicializar Select2 si está disponible
    if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
        $('.select2-multiple').select2({
            placeholder: 'Todos',
            allowClear:  true,
            width:       'resolve',
            language: {
                noResults: function () { return 'Sin resultados'; }
            }
        });

        // Auto-submit al cambiar selects
        $('.select2-multiple').on('change', function () {
            document.getElementById('formFiltros').submit();
        });
    }

});
</script>
@endpush
