<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reserva';

    protected $primaryKey = 'id_reserva';

    public $timestamps = false;

    protected $fillable = [
        'id_huesped',
        'id_usuario',
        'fecha_reserva',
        'fecha_entrada',
        'fecha_salida',
        'estado'
    ];
}