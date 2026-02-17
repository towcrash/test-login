<?php

namespace App\Models\Aplicacion;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\Usuario\Usuario;

class Evaluador_Evaluacion extends Pivot
{
	public $timestamps   = false;
	public $incrementing = true;

	protected $table = 'Evaluador_Evaluacion';
	protected $casts = [
		'fecha' => 'datetime',
	];
	
	/**
	 * Relación al Usuario
	 */
	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'Usuario_id');
	}
}