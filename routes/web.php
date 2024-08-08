<?php

use App\Http\Controllers\CompromissoController;
use App\Http\Controllers\ConsultorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Rotas da parte do consultor
Route::post('/consultor', function (Request $request) {
    return (new ConsultorController)->cadastrar($request);
});

Route::put('/consultor', function (Request $request) {
    return (new ConsultorController)->atualizar($request);
});

Route::delete('/consultor', function (Request $request) {
    return (new ConsultorController())->deletar($request);
});

Route::get("/consultor", function (Request $request) {
    return (new ConsultorController)->pesquisar($request);
});

//Rotas da parte do compromisso
Route::post('/compromisso', function (Request $request) {
   return (new CompromissoController())->cadastrar($request);
});

Route::put('/compromisso', function (Request $request) {
    return (new CompromissoController())->atualizar($request);
});

Route::delete('/compromisso', function (Request $request) {
    return (new CompromissoController())->deletar($request);
});

Route::get('/compromisso', function (Request $request) {
    return (new CompromissoController())->pesquisar($request);
});
