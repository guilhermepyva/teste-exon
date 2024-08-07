<?php

namespace App\Http\Controllers;

use App\Models\Compromisso;
use App\Models\Consultor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 *
 */
class ConsultorController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cadastrar(Request $request): JsonResponse
    {
        $validado = $request->validate([
            'nome' => 'required|string',
            'valor_hora' => 'numeric'
        ]);

        $consultor = Consultor::create([
            'nome' => $validado['nome'],
            'valor_hora' => array_key_exists('valor_hora', $validado) ? $validado['valor_hora'] : '0.0',
        ]);

        return new JsonResponse($consultor, 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function atualizar(Request $request): JsonResponse
    {
        $validado = $request->validate([
            ['id' => 'required|integer'],
            ['nome' => 'nullable|string'],
            ['valor_hora' => 'nullable|numeric']], [
            'id.required' => 'O id é obrigatório']);

        if (!array_key_exists('id', $validado)) {
            return new JsonResponse(['erro' => 'id não informado'], 400);
        }

        $consultor = Consultor::find($validado['id']);

        if ($consultor == null) {
            return new JsonResponse(['erro' => 'id não encontrado', 400]);
        }

        $strings = ['nome', 'valor_hora'];
        foreach ($strings as $string) {
            if (array_key_exists($string, $validado)) {
                $consultor->$string = $validado[$string];
            }
        }

        $consultor->save();
        return new JsonResponse($consultor, 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deletar(Request $request): JsonResponse {
        $validado = $request->validate(['id' => 'integer']);

        if (!array_key_exists('id', $validado)) {
            return new JsonResponse(['erro' => 'id não informado'], 400);
        }

        $compromissos = Compromisso::query()->where('consultor_codigo', $validado['id'])->get();

        if ($compromissos->count() > 0) {
            return new JsonResponse(['erro' => 'o consultor ainda tem compromissos', 400]);
        }

        Consultor::destroy($validado['id']);
        return new JsonResponse([], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function pesquisar(Request $request): JsonResponse
    {
        $validado = $request->validate([
            'query' => 'string',
            'page' => 'integer'
        ]);

        $query = $validado['query'] ?? '';
        $page = $validado['page'] ?? 1;

        if ($page < 1) {
            return new JsonResponse(['erro' => 'página abaixo de 1'], 400);
        }

        if ($query == '') {
            return new JsonResponse(Consultor::query()->skip(10 * ($page - 1))->limit(10)->get());
        }

        return new JsonResponse(
            Consultor::query()
                ->whereLike('nome', $query)
                ->orWhereLike('valor_hora', $query)
                ->skip(10 * ($page - 1))
                ->limit(10)
                ->get(['id', 'nome', 'valor_hora'])
        );
    }
}
