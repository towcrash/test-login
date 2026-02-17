<?php

namespace App\Models\Recurso;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Recurso_Usuario extends Pivot
{
	public $timestamps   = false;
	public $incrementing = true;

	protected $table = 'Recurso_Usuario';
	protected $casts = [
		'fecha' => 'datetime',
	];
}