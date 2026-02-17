<?php

namespace App\Models\Recurso;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Documento\Disco;
use App\Models\Recurso\Recurso;

class TipoRecurso extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'TipoRecurso';
	protected $fillable = [
		'Disco_id',
		'nombre',
		'codigo',
		'color',
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
			'TipoRecurso_id',
			'id'
		);
	}
}