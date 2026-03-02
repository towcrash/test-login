<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ Storage::disk('logos')->url('logo_epr_min.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'EPR Evaluaciones') }}</title>

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">

    @stack('estilos')
    <style>
        .menu-open > .nav-link { background-color: #40464B !important; }
        /* ── Banners de rol ── */
    .rol-banner {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        border-radius: 6px 6px 0 0;
        margin-top: 28px;
        font-weight: 700;
        font-size: 0.85rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-left: 5px solid;
    }
    .rol-banner i { font-size: 1rem; }

    .rol-cliente     { background: #e8f0fe; color: #1a56db; border-color: #1a56db; }
    .rol-evaluador   { background: #e6f9f2; color: #0e9f6e; border-color: #0e9f6e; }
    .rol-contratista { background: #fef3c7; color: #d97706; border-color: #d97706; }
    .rol-colaborador { background: #fce7f3; color: #e4008d; border-color: #e4008d; }

    /* ── Tarjetas de estadística ── */
    .stat-box {
        border-radius: 6px;
        padding: 18px 20px 14px;
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 16px;
    }
    .stat-box .stat-num {
        font-size: 2.4rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-box .stat-lbl {
        font-size: 0.8rem;
        opacity: 0.88;
        font-weight: 500;
    }
    .stat-box .stat-icon {
        position: absolute;
        right: 16px;
        bottom: 10px;
        font-size: 3rem;
        opacity: 0.18;
    }
    .stat-blue   { background: linear-gradient(135deg, #1c7ed6, #4dabf7); }
    .stat-green  { background: linear-gradient(135deg, #099268, #38d9a9); }
    .stat-orange { background: linear-gradient(135deg, #e67700, #ffd43b); }
    .stat-pink   { background: linear-gradient(135deg, #c2255c, #f783ac); }
    .stat-teal   { background: linear-gradient(135deg, #0c8599, #66d9e8); }
    .stat-grape  { background: linear-gradient(135deg, #6741d9, #b197fc); }

    /* ── Cards de contenido ── */
    .dash-card {
        border: none;
        border-radius: 0 0 6px 6px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        margin-bottom: 4px;
    }
    .dash-card .card-header {
        background: #f8f9fa;
        font-weight: 600;
        font-size: 0.88rem;
        border-bottom: 1px solid #e9ecef;
        padding: 10px 16px;
    }

    /* ── Tablas ── */
    .dash-table {
        border-collapse: collapse;
        width: 100%;
    }
    .dash-table thead th {
        background: #f1f3f5;
        font-size: 0.72rem;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 700;
        border-top: none !important;
        border-bottom: 2px solid #dee2e6 !important;
        border-right: 1px solid #dee2e6;
        padding: 8px 12px;
        white-space: nowrap;
    }
    .dash-table thead th:last-child { border-right: none; }
    .dash-table td {
        vertical-align: middle;
        font-size: 0.875rem;
        padding: 8px 12px;
        border-top: 1px solid #f0f0f0 !important;
        border-bottom: none !important;
        border-left: none !important;
        border-right: none !important;
    }
    .dash-table tbody tr:last-child td { border-bottom: none !important; }
    .dash-table tbody tr:hover td { background: #f8f9fa; }

    /* ── Estado vacío ── */
    .empty-msg {
        text-align: center;
        padding: 24px 16px;
        color: #adb5bd;
        font-size: 0.875rem;
    }
    .empty-msg i { font-size: 1.8rem; display: block; margin-bottom: 6px; }

    /* ── Badges de estado ── */
    .badge-pendiente  { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
    .badge-realizada  { background: #d1e7dd; color: #0a5c36; border: 1px solid #20c997; }
    .badge-permanente { background: #cff4fc; color: #055160; border: 1px solid #0dcaf0; }

    /* ── Separador entre secciones de rol ── */
    .rol-section + .rol-section { margin-top: 36px; }

    /* ── Paginación dentro de cards ── */
    .px-3.py-2 .pagination {
        margin-bottom: 0;
        flex-wrap: wrap;
    }
    .px-3.py-2 .pagination .page-link {
        font-size: 0.8rem;
        padding: 4px 10px;
        color: #495057;
        border-color: #dee2e6;
    }
    .px-3.py-2 .pagination .page-item.active .page-link {
        background-color: #1c7ed6;
        border-color: #1c7ed6;
        color: #fff;
    }
    .px-3.py-2 .pagination .page-item.disabled .page-link {
        color: #adb5bd;
    }

    /* ── Responsive mobile ── */
    .table-responsive-dash {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    @media (max-width: 575px) {
        .stat-box .stat-num { font-size: 1.8rem; }
        .dash-card .card-header { font-size: 0.82rem; }
        .dash-table thead th { font-size: 0.68rem; padding: 6px 8px; }
        .dash-table td { font-size: 0.82rem; padding: 6px 8px; }
        .rol-banner { font-size: 0.78rem; padding: 8px 12px; }
    }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    @include('layouts.partials.top')
    @include('layouts.partials.navegacion')

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-8">
                        <h1 class="m-0">@yield('tituloPagina')</h1>
                    </div>
                    <div class="col-4 text-right">
                        @yield('accionGlobal')
                    </div>
                </div>
            </div>
        </div>
        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title">
                                    @yield('cabecera')<strong>@yield('cabecera2')</strong>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                @yield('contenido')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials.footer')

</div><!-- /.wrapper -->

<!-- jQuery -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE 3 -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
<!-- Toastr -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script>

@include('layouts.partials.acciones')
@stack('acciones')
</body>
</html>