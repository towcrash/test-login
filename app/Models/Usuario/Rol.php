<?php

namespace App\Models\Usuario;

use Illuminate\Database\Eloquent\Model;
use App\Models\Usuario\Usuario_Rol;
use App\Models\Usuario\Usuario;

class Rol extends Model
{
    protected $table = 'Rol';
    public $timestamps = false;
    protected $fillable = [
        'nombre',
        'descripcion',
        'fecha',
        'bloqueado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    function usuarios()
    {
        return $this->belongsToMany(
            Usuario::class,
            'Usuario_Rol',
            'Rol_id',
            'Usuario_id'
        )->using(Usuario_Rol::class)
        ->withPivot('fecha', 'bloqueado')
        ->wherePivot('bloqueado', 0);
    }
}