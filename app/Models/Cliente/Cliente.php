<?php

namespace App\Models\Cliente;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario\Usuario;
use App\Models\Contratista\Contratista;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Aplicacion\Cliente_Evaluacion;
use App\Models\Cliente\Cliente_Contratista;
use App\Models\Cliente\Evaluador;
use App\Models\Cliente\Cliente_Usuario;

class Cliente extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Cliente';
	protected $fillable = [
		'nombre',
		'rut',
		'fecha',
		'bloqueado',
	];

	protected $casts = [
		'fecha' => 'datetime',
	];

	/*
	 * Relaciones
	 */
	function usuarios()
	{
		return $this->belongsToMany(
			Usuario::class,
			'Cliente_Usuario',
			'Cliente_id',
			'Usuario_id'
		)
		->using(Cliente_Usuario::class)
		->withPivot('fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function contratistas()
	{
		return $this->belongsToMany(
			Contratista::class,
			'Cliente_Contratista',
			'Cliente_id',
			'Contratista_id'
		)
		->using(Cliente_Contratista::class)
		->withPivot('Usuario_id', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function evaluaciones()
	{
		return $this->belongsToMany(
			Evaluacion::class,
			'Cliente_Evaluacion',
			'Cliente_id',
			'Evaluacion_id'
		)
		->using(Cliente_Evaluacion::class)
		->withPivot('Usuario_id', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function evaluadores()
	{
		return $this->hasMany(
			Evaluador::class,
			'Cliente_id',
			'id'
		);
	}
}