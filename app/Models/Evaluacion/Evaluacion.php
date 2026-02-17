<?php

namespace App\Models\Evaluacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente\Cliente;
use App\Models\Cliente\Evaluador;
use App\Models\Contratista\Contratista;
use App\Models\Contratista\Colaborador;
use App\Models\Aplicacion\Aplicacion;
use App\Models\Aplicacion\Cliente_Evaluacion;
use App\Models\Aplicacion\Contratista_Evaluacion;
use App\Models\Aplicacion\Colaborador_Evaluacion;
use App\Models\Aplicacion\Evaluador_Evaluacion;
use App\Models\Evaluacion\Pregunta;
use App\Models\Recurso\Recurso;

class Evaluacion extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Evaluacion';
	protected $fillable = [
		'nombre',
		'descripcion',
		'sid',
		'byEvaluador',
		'permanent',
		'fecha',
		'bloqueado',
	];

	protected $casts = [
		'fecha' => 'datetime',
	];

	/*
	 * Relaciones
	 */
	function preguntas()
	{
		return $this->hasMany(
			Pregunta::class,
			'Evaluacion_id',
			'id'
		);
	}

	function clientes()
	{
		return $this->belongsToMany(
			Cliente::class,
			'Cliente_Evaluacion',
			'Evaluacion_id',
			'Cliente_id'
		)
		->using(Cliente_Evaluacion::class)
		->withPivot('Usuario_id', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function evaluadores()
	{
		return $this->belongsToMany(
			Evaluador::class,
			'Evaluador_Evaluacion',
			'Evaluacion_id',
			'Evaluador_id'
		)
		->using(Evaluador_Evaluacion::class)
		->withPivot('Usuario_id', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function contratistas()
	{
		return $this->belongsToMany(
			Contratista::class,
			'Contratista_Evaluacion',
			'Evaluacion_id',
			'Contratista_id'
		)
		->using(Contratista_Evaluacion::class)
		->withPivot('Usuario_id', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function colaboradores()
	{
		return $this->belongsToMany(
			Colaborador::class,
			'Colaborador_Evaluacion',
			'Evaluacion_id',
			'Colaborador_id'
		)
		->using(Colaborador_Evaluacion::class)
		->withPivot('token', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function aplicaciones()
	{
		return $this->hasMany(
			Aplicacion::class,
			'Evaluacion_id',
			'id'
		);
	}

	function recursos()
	{
		return $this->hasMany(
			Recurso::class,
			'Evaluacion_id',
			'id'
		);
	}
}