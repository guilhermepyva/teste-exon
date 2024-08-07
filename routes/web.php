<?php

use App\Http\Controllers\ConsultorController;
use App\Models\Consultor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

session_start();
Route::get('/', function () {
    return view('welcome');
});

Route::get('/teste', function () {
    print(Consultor::create(['nome' => 'Jessica', 'valor_hora' => '45.0']));
});

Route::post('consultor', function (Request $request) {
    return (new ConsultorController)->cadastrar($request);
});

Route::put('consultor', function () {

});

Route::get("/consultor", function (Request $request) {
    return (new ConsultorController)->pesquisar($request);
});
