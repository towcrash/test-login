@extends('layouts.login')

@section('content')
    <div class="login-box">
        <div class="card">
            <div class="card-body login-card-body">
                <div class="login-logo">
                    <img src="{{ Storage::disk('logos')->url('MDLL.jpeg') }}" alt="Cliente" style="width:30%">
                </div>
                <div class="login-logo" style="margin-bottom:1em">
                    INDUCCIONES
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    {{-- Usuario --}}
                    <div class="input-group mb-3">
                        <input
                            id="user"
                            name="user"
                            type="text"
                            class="form-control @error('user') is-invalid @enderror"
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
                        @error('user')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Contraseña --}}
                    <div class="input-group mb-3">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Contraseña"
                            required
                        >
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
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

            <div class="login-logo" style="margin: .5em 1.5em;">
                <img src="{{ Storage::disk('logos')->url('EngineeringPR.jpeg') }}" alt="EngineeringPR" style="width:100%">
            </div>
        </div>
    </div>
@endsection