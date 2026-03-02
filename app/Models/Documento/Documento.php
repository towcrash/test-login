<?php

namespace App\Models\Documento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Documento\Disco;
use App\Models\Recurso\Recurso;
use App\Models\Contratista\Colaborador_Documento;
use App\Models\Contratista\Colaborador;

class Documento extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Documento';
	protected $fillable = [
		'Disco_id',
		'nombre',
		'fecha',
		'bloqueado',
	];

	protected $casts = [
		'fecha' => 'datetime',
	];

	/*
	 * Relaciones
	 */
	function disco()
	{
		return $this->belongsTo(
			Disco::class,
			'Disco_id',
			'id'
		);
	}

	function recursos()
	{
		return $this->hasMany(
			Recurso::class,
			'Documento_id',
			'id'
		);
	}

	function colaboradores()
	{
		return $this->belongsToMany(
			Colaborador::class,
			'Colaborador_Documento',
			'Documento_id',
			'Colaborador_id'
		)
		->using(Colaborador_Documento::class)
		->withPivot('pAprobacion', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}
}