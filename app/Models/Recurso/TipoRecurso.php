<?php

namespace App\Models\Recurso;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Recurso\Recurso;

class TipoRecurso extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'TipoRecurso';
	protected $fillable = [
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

	function recursos()
	{
		return $this->hasMany(
			Recurso::class,
			'TipoRecurso_id',
			'id'
		);
	}
}