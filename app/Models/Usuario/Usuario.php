<?php

namespace App\Models\Usuario;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente\Cliente;
use App\Models\Cliente\Cliente_Usuario;
use App\Models\Contratista\Contratista;
use App\Models\Contratista\Contratista_Usuario;
use App\Models\Cliente\Evaluador;
use App\Models\Contratista\Colaborador;
use App\Models\Recurso\Recurso;
use App\Models\Recurso\Recurso_Usuario;

class Usuario extends Authenticatable
{
	use HasFactory;

	public $timestamps  = false;
	protected $table    = 'Usuario';
	protected $fillable = [
		'user',
		'password',
		'rut',
		'nombre',
		'email',
		'fecha',
		'bloqueado',
		'vigencia',
	];

	protected $casts = [
		'fecha' => 'datetime',
		'vigencia' => 'datetime',
	];

	protected function password(): Attribute
	{
		return Attribute::make(
			set: fn ($value) => bcrypt($value),
		);
	}

	/*
     *  Roles
     */
    public function isSisAdmin(): bool
    {
        return $this->roles()
            ->where('nombre', 'SisAdmin')
            ->wherePivot('bloqueado', 0)
            ->exists();
    }

    public function hasRole(string $rol): bool
    {
        return $this->roles()
            ->where('nombre', $rol)
            ->wherePivot('bloqueado', 0)
            ->exists();
    }

    public function hasAnyRole(string ...$roles): bool
    {
        return $this->roles()
            ->whereIn('nombre', $roles)
            ->wherePivot('bloqueado', 0)
            ->exists();
    }

	/*
	 * Relaciones
	 */
	function clientes()
	{
		return $this->belongsToMany(
			Cliente::class,
			'Cliente_Usuario',
			'Usuario_id',
			'Cliente_id'
		)
		->using(Cliente_Usuario::class)
		->withPivot('fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function contratistas()
	{
		return $this->belongsToMany(
			Contratista::class,
			'Contratista_Usuario',
			'Usuario_id',
			'Contratista_id'
		)
		->using(Contratista_Usuario::class)
		->withPivot('fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function evaluadores()
	{
		return $this->hasMany(
			Evaluador::class,
			'Usuario_id',
			'id'
		);
	}

	function colaboradores()
	{
		return $this->hasMany(
			Colaborador::class,
			'Usuario_id',
			'id'
		);
	}

	function recursos()
	{
		return $this->belongsToMany(
			Recurso::class,
			'Recurso_Usuario',
			'Usuario_id',
			'Recurso_id'
		)
		->using(Recurso_Usuario::class)
		->withPivot('fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}

	function recursosCreados()
	{
		return $this->hasMany(
			Recurso::class,
			'Usuario_id',
			'id'
		);
	}
	function roles()
	{
		return $this->belongsToMany(
			Rol::class,
			'Usuario_Rol',
			'Usuario_id',
			'Rol_id'
		)
		->using(Usuario_Rol::class)
		->withPivot('fecha', 'bloqueado')
		->wherePivot('bloqueado', 0);
	}
}