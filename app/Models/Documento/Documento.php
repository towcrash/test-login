<?php

namespace App\Models\Documento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Documento\Disco;
use App\Models\Recurso\Recurso;

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
}