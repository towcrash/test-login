<?php

namespace App\Models\Cliente;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\Usuario\Usuario;

class Cliente_Contratista extends Pivot
{
	public $timestamps   = false;
	public $incrementing = true;

	protected $table = 'Cliente_Contratista';
	protected $casts = [
		'fecha' => 'datetime',
	];

	/**
	 * Relación al Usuario
	 */
	public function usuario()
	{
		return $this->belongsTo(Usuario::class, 'Usuario_id');
	}
}