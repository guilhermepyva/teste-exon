<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultor extends Model
{
    protected $table = 'consultores';
    public $timestamps = false;

    protected $fillable = [
        'nome', 'valor_hora'
    ];
}
