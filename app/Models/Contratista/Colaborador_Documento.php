<?php

namespace App\Models\Contratista;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Colaborador_Documento extends Pivot
{
	public $timestamps   = false;
	public $incrementing = true;

	protected $table    = 'Colaborador_Documento';
	protected $fillable = [
		'Colaborador_id',
		'Documento_id',
		'pAprobacion',
		'bloqueado',
	];

	protected $casts = [
		'pAprobacion' => 'float',
	];
}