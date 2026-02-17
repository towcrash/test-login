<?php

namespace App\Services\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use InvalidArgumentException;

/**
 * @author Samuel Reyes <samuel.reyes@swdd.cl>
 */

class LogService
{
	protected $funcionesValidas = [
		'debug',
		'info',
		'notice',
		'warning',
		'error',
		'critical',
		'alert',
		'emergency',
	];

	private function writeLog($disc, $level, $channel, $message)
	{
		if( !in_array($level, $this->funcionesValidas) )
			throw new InvalidArgumentException('Nivel usado invalido.');

		if (!$message)
			throw new InvalidArgumentException('Mensaje ausente.');

		$handler = new StreamHandler(storage_path('logs/' . $disc . date('/Ym') . '.log' ));
		$handler->setFormatter(new LineFormatter("[%datetime%] [$disc] [%level_name%] (%channel%): %message%\n"));

		$logger = new Logger($channel);
		$logger->pushHandler($handler)->{$level}($message);
	}

	function __call($function, $parametros)
	{
		if (count($parametros) < 2)
			throw new InvalidArgumentException('Argumentos insuficientes');

		if (count($parametros) == 2)
			return $this->writeLog(strtoupper($function), 'info', $parametros[0], $parametros[1]);

		$this->writeLog(strtoupper($function), $parametros[0], $parametros[1], $parametros[2]);
	}
}
