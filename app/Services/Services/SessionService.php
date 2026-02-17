<?php

namespace App\Services\Services;

use Carbon\Carbon;
use stdClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * @author Samuel Reyes <samuel.reyes@swdd.cl>
 */

class SessionService
{
	CONST CLAVE = 'mensajesSession';

	// Generales
	public function success($titulo, $mensaje)
	{
		$this->putMensaje('success', $titulo, $mensaje);
	}
	public function warning($titulo, $mensaje)
	{
		$this->putMensaje('warning', $titulo, $mensaje);
	}
	public function error($titulo, $mensaje)
	{
		$this->putMensaje('error', $titulo, $mensaje);
	}
	public function info($titulo, $mensaje)
	{
		$this->putMensaje('info', $titulo, $mensaje);
	}
	public function form()
	{
		$this->error('Validación', 'Formulario presenta errores');
	}
	public function delete($titulo)
	{
		$this->error($titulo, 'No es posible eliminar debido a que posee elementos asociados');
	}

	private function putMensaje($tipo, $titulo, $mensaje)
	{
		$mensajes = Session::get(self::CLAVE, new stdClass);

		if ( !property_exists($mensajes, $tipo) )
			$mensajes->{$tipo} = [];

		$mensajes->{$tipo}[] = [
			'titulo'  => $titulo,
			'mensaje' => $mensaje,
		];

		Session::flash(self::CLAVE, $mensajes);
	}


	public function getTiempos()
	{
		$cant = 0;

		if ($mensajes = Session::get(self::CLAVE))
			foreach ($mensajes as $item)
				$cant += count($item);

		if ($cant > 3) return 0;

		return 3500 + $cant * 1000;
	}

	public function getMensajes()
	{
		return Session::get(self::CLAVE) ?? new stdClass;
	}

	public function forget()
	{
		return Session::forget(self::CLAVE);
	}
}
