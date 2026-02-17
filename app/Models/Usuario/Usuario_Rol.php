<?php

namespace App\Models\Usuario;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Usuario_Rol extends Pivot
{
    public $timestamps   = false;
	public $incrementing = true;

	protected $table = 'Usuario_Rol';
	protected $casts = [
		'fecha' => 'datetime',
	];
}
