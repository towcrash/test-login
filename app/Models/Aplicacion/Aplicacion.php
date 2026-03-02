<?php

namespace App\Models\Aplicacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente\Evaluador;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Contratista\Colaborador;

class Aplicacion extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Aplicacion';
	protected $fillable = [
		'Evaluador_id',
		'Evaluacion_id',
		'Colaborador_id',
		'token',
		'fecha',
		'submitdate',
		'bloqueado',
	];

	protected $casts = [
		'fecha' => 'datetime',
		'submitdate' => 'datetime'
	];

	/*
	 * Relaciones
	 */
	function evaluador()
	{
		return $this->belongsTo(
			Evaluador::class,
			'Evaluador_id',
			'id'
		);
	}

	function evaluacion()
	{
		return $this->belongsTo(
			Evaluacion::class,
			'Evaluacion_id',
			'id'
		);
	}

	function colaborador()
	{
		return $this->belongsTo(
			Colaborador::class,
			'Colaborador_id',
			'id'
		);
	}
}