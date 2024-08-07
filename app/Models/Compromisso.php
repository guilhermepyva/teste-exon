<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compromisso extends Model
{
    protected $table = 'compromissos';
    public $timestamps = false;

    protected $fillable = [
        'consultor_codigo', 'data', 'hora_inicio', 'hora_fim', 'intervalo'
    ];
}
