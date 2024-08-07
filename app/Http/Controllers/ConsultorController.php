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
class ConsultorController extends Controller
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

        return ConsultorController::json_response($consultor);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function atualizar(Request $request): JsonResponse
    {
        //o $request->validate() não funcionou aqui
        $validado = $request->validate([
            ['id' => 'required|integer'],
            ['nome' => 'nullable|string'],
            ['valor_hora' => 'nullable|numeric']], [
            'id.required' => 'O id é obrigatório']);

        if (!array_key_exists('id', $validado)) {
            return ConsultorController::json_response(['erro' => 'id não informado'], 406);
        }

        $consultor = Consultor::find($validado['id']);

        if ($consultor == null) {
            return ConsultorController::json_response(['erro' => 'id não encontrado']);
        }

        $strings = ['nome', 'valor_hora'];
        foreach ($strings as $string) {
            if (array_key_exists($string, $validado)) {
                $consultor->$string = $request[$string];
            }
        }

        $consultor->save();
        return new JsonResponse($consultor, 200);
    }

    public function deletar(Request $request): JsonResponse {
        $validado = $request->validate(['id' => 'integer']);

        if (!array_key_exists('id', $validado)) {
            return ConsultorController::json_response(['erro' => 'id não informado'], 406);
        }

        $compromissos = Compromisso::query()->where('consultor_codigo', $validado['id'])->get();

        if ($compromissos->count() > 0) {
            return ConsultorController::json_response(['erro' => 'o consultor ainda tem compromissos']);
        }

        Consultor::destroy($validado['id']);
        return ConsultorController::json_response(200);
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
            return ConsultorController::json_response(['erro' => 'página abaixo de 1'], 406);
        }

        if ($query == '') {
            return ConsultorController::json_response(Consultor::query()->skip(10 * ($page - 1))->limit(10)->get());
        }

        return ConsultorController::json_response(
            Consultor::query()
                ->whereLike('nome', $query)
                ->orWhereLike('valor_hora', $query)
                ->skip(10 * ($page - 1))
                ->limit(10)
                ->get(['id', 'nome', 'valor_hora'])
        );
    }
}
