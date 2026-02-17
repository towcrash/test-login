<?php

namespace App\Models\Aplicacion;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Colaborador_Evaluacion extends Pivot
{
	public $timestamps   = false;
	public $incrementing = true;

	protected $table = 'Colaborador_Evaluacion';
	protected $casts = [
		'fecha' => 'datetime',
	];
}