<?php

namespace App\Models\Evaluacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Evaluacion\Alternativa;

class Pregunta extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Pregunta';
	protected $fillable = [
		'Evaluacion_id',
		'texto',
		'codigo',
		'fecha',
		'bloqueado',
	];

	protected $casts = [
		'fecha' => 'datetime',
	];

	/*
	 * Relaciones
	 */
	function evaluacion()
	{
		return $this->belongsTo(
			Evaluacion::class,
			'Evaluacion_id',
			'id'
		);
	}

	function alternativas()
	{
		return $this->hasMany(
			Alternativa::class,
			'Pregunta_id',
			'id'
		);
	}
}