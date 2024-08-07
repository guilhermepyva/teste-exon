<?php

use App\Models\Consultor;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/teste', function () {
    print(Consultor::create(['nome' => 'Jessica', 'valor_hora' => '45.0']));
});
