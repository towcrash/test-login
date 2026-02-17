<?php

namespace App\Models\Evaluacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Evaluacion\Pregunta;

class Alternativa extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Alternativa';
	protected $fillable = [
		'Pregunta_id',
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
	function pregunta()
	{
		return $this->belongsTo(
			Pregunta::class,
			'Pregunta_id',
			'id'
		);
	}
}