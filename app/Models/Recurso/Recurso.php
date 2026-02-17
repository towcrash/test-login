<?php

namespace App\Models\Recurso;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Documento\Documento;
use App\Models\Evaluacion\Evaluacion;
use App\Models\Usuario\Usuario;
use App\Models\Recurso\TipoRecurso;
use App\Models\Recurso\Recurso_Usuario;

class Recurso extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Recurso';
	protected $fillable = [
		'TipoRecurso_id',
		'Evaluacion_id',
		'Documento_id',
		'Usuario_id',
		'nombre',
		'descripcion',
		'fecha',
		'bloqueado',
	];

	protected $casts = [
		'fecha' => 'datetime',
	];

	/*
	 * Relaciones
	 */
	function tipoRecurso()
	{
		return $this->belongsTo(
			TipoRecurso::class,
			'TipoRecurso_id',
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

	function documento()
	{
		return $this->belongsTo(
			Documento::class,
			'Documento_id',
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

	function usuarios()
	{
		return $this->belongsToMany(
			Usuario::class,
			'Recurso_Usuario',
			'Recurso_id',
			'Usuario_id'
		)
		->using(Recurso_Usuario::class)
		->withPivot('fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}
}