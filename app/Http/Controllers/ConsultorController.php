<?php

namespace App\Http\Controllers;

use App\Models\Consultor;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class ConsultorController extends Controller
{
    public function cadastrar(Request $request): Application|Response|ResponseFactory
    {
        $consultor = Consultor::create([
            'nome' => $request['nome'],
            'valor_hora' => $request['valor_hora'],
        ]);

        return response(json_encode($consultor), 200);
    }

    public function pesquisar(Request $request)
    {
        $validado = $request->validate([
            'query' => 'string',
            'page' => 'integer'
        ]);

        $query = $validado['query'] ?? '';
        $page = $validado['page'] ?? 1;

        if ($page < 1) {
            return response('Página não declarada', 406);
        }

        return json_encode(Consultor::query()
                ->whereLike('nome', $query)
                ->orWhereLike('valor_hora', $query)
                ->skip(10 * ($page - 1))
                ->limit(10)
                ->get(['id', 'nome', 'valor_hora']));
    }
}
