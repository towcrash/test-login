@php
    if ($errors->any()) {
        $esLogin = request()->routeIs('login') || request()->routeIs('login.post');
        if ($esLogin) {
            foreach ($errors->all() as $error) {
                SessionService::error('Acceso denegado', $error);
            }
        } else {
            SessionService::form();
        }
    }
@endphp

<script>
    $(document).ready(function () {
        toastr.options = {
            closeButton  : true,
            progressBar  : true,
            showDuration : 500,
            hideDuration : 1000,
            timeOut      : {{ SessionService::getTiempos() }},
            showMethod   : 'fadeIn',
            hideMethod   : 'fadeOut',
        };

        @foreach(SessionService::getMensajes() as $tipo => $mensajes)
            @foreach ($mensajes as $mensaje)
                toastr['{{ $tipo }}']('{!! $mensaje['mensaje'] !!}', '{{ $mensaje['titulo'] }}');
            @endforeach
        @endforeach
    });
</script>
{{ SessionService::forget() }}