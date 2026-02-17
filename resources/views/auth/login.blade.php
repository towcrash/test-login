@extends('layouts.login')

@section('content')
	<div class="login-box">
		<div class="card">
			<div class="card-body login-card-body">
				<div class="login-logo">
					<img src="{{ Storage::disk('logos')->url('MDLL.jpeg') }}" alt="Cliente" style="width:30%">
				</div>
				<div class="login-logo" style="margin-bottom:1em">
					Drill Campaign
				</div>
				<form method="POST" action="{{ route('auth.login') }}">
					@csrf
					<div class="input-group mb-3">
						<input id="user" name="user" type="user" class="form-control @error('user') is-invalid @enderror" placeholder="User" value="{{ old('user') }}" required autocomplete="user" autofocus>
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
					<div class="input-group mb-3">
						<input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Secret" required autocomplete="current-password">
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
					<div class="row">
						<div class="col-12">
							<button type="submit" class="btn btn-primary btn-block">Ingresar</button>
						</div>
					</div>
				</form>
			</div>
			<div class="login-logo" style="margin: .5em 1.5em;">
				<img src="{{ Storage::disk('logos')->url('EngineeringPR.jpeg') }}" alt="Cliente" style="width:100%">
				{{-- <img src="{{ Storage::disk('logos')->url('swdd_hcomplet.svg') }}" alt="Cliente" style="width:80%"> --}}
			</div>
		</div>
	</div>
@endsection
