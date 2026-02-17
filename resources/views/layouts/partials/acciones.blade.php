@include('layouts.partials.accionesSession')

<script>
	function obtenerValoresFiltros()
	{
		var data = {};
		$('#filtros input').map(function(){
			data[this.name] = this.value;
		});

		return data;
	}
</script>
