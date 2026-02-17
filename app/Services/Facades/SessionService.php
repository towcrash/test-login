<?php

/**
 * @author Samuel Reyes <samuel.reyes@swdd.cl>
 */

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class SessionService extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'sessionService';
	}
}
