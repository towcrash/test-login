<?php

namespace App\Models\Contratista;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contratista\Contratista;
use App\Models\Usuario\Usuario;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Aplicacion\Aplicacion;
use App\Models\Aplicacion\Colaborador_Evaluacion;

class Colaborador extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Colaborador';
	protected $fillable = [
		'Contratista_id',
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
	function contratista()
	{
		return $this->belongsTo(
			Contratista::class,
			'Contratista_id',
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
			'Colaborador_Evaluacion',
			'Colaborador_id',
			'Evaluacion_id'
		)
		->using(Colaborador_Evaluacion::class)
		->withPivot('token', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function aplicaciones()
	{
		return $this->hasMany(
			Aplicacion::class,
			'Colaborador_id',
			'id'
		);
	}
}