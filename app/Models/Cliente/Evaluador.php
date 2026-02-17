<?php

namespace App\Models\Cliente;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario\Usuario;
use App\Models\Cliente\Cliente;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Aplicacion\Aplicacion;
use App\Models\Aplicacion\Evaluador_Evaluacion;


class Evaluador extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Evaluador';
	protected $fillable = [
		'Cliente_id',
		'Usuario_id',
		'fecha',
		'bloqueado',
	];

	protected $casts = [
		'fecha' => 'datetime',
	];

	/*
	 * Relaciones
	 */
	function cliente()
	{
		return $this->belongsTo(
			Cliente::class,
			'Cliente_id',
			'id'
		);
	}

	function usuario()
	{
		return $this->belongsTo(
			Usuario::class,
			'Usuario_id',
			'id'
		);
	}

	function evaluaciones()
	{
		return $this->belongsToMany(
			Evaluacion::class,
			'Evaluador_Evaluacion',
			'Evaluador_id',
			'Evaluacion_id'
		)
		->using(Evaluador_Evaluacion::class)
		->withPivot('Usuario_id', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function aplicaciones()
	{
		return $this->hasMany(
			Aplicacion::class,
			'Evaluador_id',
			'id'
		);
	}
}