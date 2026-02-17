<?php

namespace App\Models\Cliente;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Cliente_Usuario extends Pivot
{
	public $timestamps   = false;
	public $incrementing = true;

	protected $table = 'Cliente_Usuario';
	protected $casts = [
		'fecha' => 'datetime',
	];
}