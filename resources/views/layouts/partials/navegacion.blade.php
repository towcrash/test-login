<style>
    .menu-open > .nav-link {
        background-color: #40464B !important;
    }
</style>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="{{ route('dashboard') }}" class="brand-link" style="background-color: #FFF;">
        <img class="brand-logo" src="{{ Storage::disk('logos')->url('EngineeringPR.jpeg') }}" style="width:100%;">
    </a>

    <div class="sidebar">
        <nav class="mt-3">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                @php $current = request()->route()?->getName() ?? ''; @endphp

                {{-- ── Administración ─────────────────────────────────── --}}
                @sisadmin
                <li class="nav-header">ADMINISTRACIÓN</li>

                <li class="nav-item {{ str_starts_with($current, 'usuario.') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <p>Usuarios <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-2">
                            <a href="{{ route('usuario.usuario.index') }}"
                               class="nav-link {{ request()->routeIs('usuario.usuario.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Usuarios</p>
                            </a>
                        </li>
                        <li class="nav-item ml-2">
                            <a href="{{ route('rol.rol.index') }}"
                               class="nav-link {{ request()->routeIs('rol.rol.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-alt"></i>
                                <p>Roles</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endsisadmin

                {{-- ── Clientes ────────────────────────────────────────── --}}
                @anyrole('SisAdmin', 'Cliente', 'Evaluador')
                <li class="nav-header">CLIENTES</li>

                <li class="nav-item {{ str_starts_with($current, 'cliente.') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <p>Clientes <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-2">
                            <a href="{{ route('cliente.cliente.index') }}"
                               class="nav-link {{ request()->routeIs('cliente.cliente.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-building"></i>
                                <p>Clientes</p>
                            </a>
                        </li>
                        <li class="nav-item ml-2">
                            <a href="{{ route('cliente.evaluador.index') }}"
                               class="nav-link {{ request()->routeIs('cliente.evaluador.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-tie"></i>
                                <p>Evaluadores</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endanyrole

                {{-- ── Contratistas ────────────────────────────────────── --}}
                @anyrole('SisAdmin', 'Contratista', 'Evaluador')
                <li class="nav-header">CONTRATISTAS</li>

                <li class="nav-item {{ str_starts_with($current, 'contratista.') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <p>Contratistas <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-2">
                            <a href="{{ route('contratista.contratista.index') }}"
                               class="nav-link {{ request()->routeIs('contratista.contratista.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-hard-hat"></i>
                                <p>Contratistas</p>
                            </a>
                        </li>
                        <li class="nav-item ml-2">
                            <a href="{{ route('contratista.colaborador.index') }}"
                               class="nav-link {{ request()->routeIs('contratista.colaborador.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Colaboradores</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endanyrole

                {{-- ── Evaluaciones ────────────────────────────────────── --}}
                @anyrole('SisAdmin', 'Evaluador', 'Cliente')
                <li class="nav-header">EVALUACIONES</li>

                <li class="nav-item {{ str_starts_with($current, 'evaluacion.') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <p>Evaluaciones <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item ml-2">
                            <a href="{{ route('evaluacion.evaluacion.index') }}"
                               class="nav-link {{ request()->routeIs('evaluacion.evaluacion.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>Evaluaciones</p>
                            </a>
                        </li>
                        <li class="nav-item ml-2">
                            <a href="{{ route('evaluacion.pregunta.index') }}"
                               class="nav-link {{ request()->routeIs('evaluacion.pregunta.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-question-circle"></i>
                                <p>Preguntas</p>
                            </a>
                        </li>
                        <li class="nav-item ml-2">
                            <a href="{{ route('evaluacion.alternativa.index') }}"
                               class="nav-link {{ request()->routeIs('evaluacion.alternativa.*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-list-ul"></i>
                                <p>Alternativas</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endanyrole

                {{-- ── Aplicaciones ────────────────────────────────────── --}}
                @anyrole('SisAdmin', 'Evaluador', 'Colaborador')
                <li class="nav-header">APLICACIONES</li>

                <li class="nav-item">
                    <a href="{{ route('aplicacion.aplicacion.index') }}"
                       class="nav-link {{ request()->routeIs('aplicacion.aplicacion.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tasks"></i>
                        <p>Aplicaciones</p>
                    </a>
                </li>
                @endanyrole

                {{-- ── Recursos ────────────────────────────────────────── --}}
                @anyrole('SisAdmin', 'Evaluador')
                <li class="nav-header">RECURSOS</li>

                <li class="nav-item">
                    <a href="{{ route('recurso.recurso.index') }}"
                       class="nav-link {{ request()->routeIs('recurso.recurso.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-folder-open"></i>
                        <p>Recursos</p>
                    </a>
                </li>
                @endanyrole

            </ul>
        </nav>
    </div>
</aside>