<?php

namespace App\Models\Contratista;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente\Cliente;
use App\Models\Usuario\Usuario;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Contratista\Colaborador;
use App\Models\Cliente\Cliente_Contratista;
use App\Models\Contratista\Contratista_Usuario;
use App\Models\Aplicacion\Contratista_Evaluacion;


class Contratista extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Contratista';
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
			'Contratista_Usuario',
			'Contratista_id',
			'Usuario_id'
		)
		->using(Contratista_Usuario::class)
		->withPivot('fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function clientes()
	{
		return $this->belongsToMany(
			Cliente::class,
			'Cliente_Contratista',
			'Contratista_id',
			'Cliente_id'
		)
		->using(Cliente_Contratista::class)
		->withPivot('Usuario_id', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function evaluaciones()
	{
		return $this->belongsToMany(
			Evaluacion::class,
			'Contratista_Evaluacion',
			'Contratista_id',
			'Evaluacion_id'
		)
		->using(Contratista_Evaluacion::class)
		->withPivot('Usuario_id', 'fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function colaboradores()
	{
		return $this->hasMany(
			Colaborador::class,
			'Contratista_id',
			'id'
		);
	}
}