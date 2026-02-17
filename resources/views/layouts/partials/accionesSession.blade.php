@php
    if( $errors->any() ) SessionService::form();
@endphp

{{-- Mensajes de Session --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Esperar a que toastr esté disponible (lo carga Vite de forma async)
        var intentos = 0;
        var intervalo = setInterval(function () {
            intentos++;
            if (typeof window.toastr !== 'undefined') {
                clearInterval(intervalo);

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

            } else if (intentos > 50) {
                // Timeout tras ~2.5s, no mostrar mensajes
                clearInterval(intervalo);
            }
        }, 50);
    });
</script>
{{ SessionService::forget() }}