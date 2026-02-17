<?php

namespace App\Models\Contratista;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Contratista_Usuario extends Pivot
{
	public $timestamps   = false;
	public $incrementing = true;

	protected $table = 'Contratista_Usuario';
	protected $casts = [
		'fecha' => 'datetime',
	];
}