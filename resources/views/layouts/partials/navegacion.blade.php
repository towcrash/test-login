<aside class="main-sidebar sidebar-dark-primary elevation-4">
    
    <a href="{{ route('dashboard') }}" class="brand-link" style="background-color:#fff; padding: 8px 15px;">
        <img src="{{ Storage::disk('logos')->url('logo_epr.png') }}"
             alt="Logo"
             style="width:100%; max-height:50px; object-fit:contain;">
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <span class="d-block text-white">
                    {{ Auth::guard('usuario')->user()->nombre }}
                </span>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu"
                data-accordion="false">

                @php $current = request()->route()?->getName() ?? ''; @endphp

                {{-- ── Dashboard ────────────────────────────────────────── --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="fa-solid fa-gauge nav-icon"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- ── Clientes ─────────────────────────────────────────── --}}
                @anyrole('SisAdmin', 'Cliente', 'Evaluador')
                <li class="nav-header">CLIENTES</li>
                <li class="nav-item {{ str_starts_with($current, 'cliente.') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-building"></i>
                        <p>Clientes <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('cliente.cliente.index') }}"
                               class="nav-link {{ request()->routeIs('cliente.cliente.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Clientes</p>
                            </a>
                        </li>
                        @sisadmin
                        <li class="nav-item">
                            <a href="{{ route('cliente.evaluador.index') }}"
                               class="nav-link {{ request()->routeIs('cliente.evaluador.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Evaluadores</p>
                            </a>
                        </li>
                        @endsisadmin
                    </ul>
                </li>
                @endanyrole

                {{-- ── Contratistas ─────────────────────────────────────── --}}
                @anyrole('SisAdmin', 'Contratista', 'Evaluador', 'Cliente')
                <li class="nav-header">CONTRATISTAS</li>
                <li class="nav-item {{ str_starts_with($current, 'contratista.') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-hard-hat"></i>
                        <p>Contratistas <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('contratista.contratista.index') }}"
                               class="nav-link {{ request()->routeIs('contratista.contratista.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Contratistas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('contratista.colaborador.index') }}"
                               class="nav-link {{ request()->routeIs('contratista.colaborador.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Colaboradores</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endanyrole

                {{-- ── Evaluaciones ─────────────────────────────────────── --}}
                <li class="nav-header">EVALUACIONES</li>
                <li class="nav-item {{ str_starts_with($current, 'evaluacion.') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Evaluaciones <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('evaluacion.evaluacion.index') }}"
                               class="nav-link {{ request()->routeIs('evaluacion.evaluacion.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Evaluaciones</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- ── Aplicaciones ─────────────────────────────────────── --}}
                
                <li class="nav-header">APLICACIONES</li>
                <li class="nav-item {{ str_starts_with($current, 'aplicacion.') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-tasks"></i>
                        <p>Aplicaciones <i class="right fas fa-angle-left"></i></p>
                    </a>
                    @anyrole('SisAdmin', 'Evaluador', 'Colaborador')
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('aplicacion.aplicacion.index') }}"
                               class="nav-link {{ request()->routeIs('aplicacion.aplicacion.index') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Aplicaciones</p>
                            </a>
                        </li>
                    </ul>
                    @endanyrole
                    @anyrole('SisAdmin', 'Cliente', 'Contratista')
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('aplicacion.aplicacion.documentos') }}"
                               class="nav-link {{ request()->routeIs('aplicacion.aplicacion.documentos') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Resultados</p>
                            </a>
                        </li>
                    </ul>
                    @endanyrole
                </li>

                
                {{-- ── Administración ──────────────────────────────────── --}}
                @sisadmin
                <li class="nav-header">ADMINISTRACIÓN</li>

                <li class="nav-item {{ str_starts_with($current, 'usuario.') ? 'menu-is-opening menu-open' : '' }}">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>Usuarios <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('usuario.usuario.index') }}"
                               class="nav-link {{ request()->routeIs('usuario.usuario.*') ? 'active' : '' }}">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Usuarios</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endsisadmin
            </ul>
        </nav>
    </div>
</aside>