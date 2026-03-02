<?php

namespace App\Models\Documento;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Documento\Documento;
use App\Models\Recurso\TipoRecurso;

class Disco extends Model
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Disco';
	protected $fillable = [
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
	function documentos()
	{
		return $this->hasMany(
			Documento::class,
			'Disco_id',
			'id'
		);
	}
}