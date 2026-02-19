@extends('layouts.login')

@section('content')
    <div class="login-box">
        <div class="card">
            <div class="card-body login-card-body">
                <div class="login-logo">
                    <img src="{{ Storage::disk('logos')->url('logo_epr.png') }}" alt="Cliente" style="width:30%">
                </div>
                <div class="login-logo" style="margin-bottom:1em">
                    EVALUACIONES
                </div>

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    {{-- Usuario --}}
                    <div class="input-group mb-3">
                        <input
                            id="user"
                            name="user"
                            type="text"
                            class="form-control"
                            placeholder="Usuario"
                            value="{{ old('user') }}"
                            required
                            autofocus
                        >
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Contraseña --}}
                    <div class="input-group mb-3">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="form-control"
                            placeholder="Contraseña"
                            required
                        >
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Botón --}}
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-sign-in-alt mr-1"></i> Ingresar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection