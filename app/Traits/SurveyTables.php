<?php

namespace App\Traits;

use App\Models\Surveys\Survey;
use App\Models\Surveys\Token;
use Exception;
use Illuminate\Support\Facades\Schema;

trait SurveyTables
{
	public static function code($code)
	{
		$tabla = self::getTableName($code);

		return (new static)
			->setTable($tabla)
			->newQuery();
	}

	public static function getTableName($code)
	{
		$tabla = self::defineTableName(self::class, $code);

		if (!Schema::connection('survey')->hasTable($tabla))
			throw new Exception(sprintf('La tabla "%s" no existe %s', $tabla, self::class));
		
		return $tabla;
	}

	private function defineTableName($instance, $code)
	{
		switch ($instance){
			case Token::class:
				return 'lime_tokens_' . $code;
			case Survey::class:
				return 'lime_responses_' . $code;
			default:
				throw new Exception('The case type set on model is invalid');
		}
	}
}
