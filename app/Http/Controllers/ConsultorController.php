<?php

namespace App\Http\Controllers;

use App\Models\Compromisso;
use App\Models\Consultor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controlador da tabela e das rotas dos consultores
 */
class ConsultorController
{
    /**
     * Este é o POST do /consultor
     *
     * Exemplo de uso:
     *
     * POST /consultor
     * body {
     *      'nome' = 'Joana'
     *      'valor_hora' = 20.55 (opcional, se não informado será 0)
     * }
     *
     * Retorna os dados da nova instância
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function cadastrar(Request $request): JsonResponse
    {
        //Valida os dados
        try {
            $validado = validator($request->all(), [
                'nome' => 'required|string',
                'valor_hora' => 'numeric'
            ])->validate();
        } catch (ValidationException $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        //Cria o consultor
        $consultor = Consultor::create([
            'nome' => $validado['nome'],
            'valor_hora' => array_key_exists('valor_hora', $validado) ? $validado['valor_hora'] : '0.0',
        ]);

        return new JsonResponse($consultor, 200);
    }

    /**
     * Este é o PUT do /consultor
     *
     * Exemplo de uso:
     * PUT /consultor
     * body {
     *      'id' = 2
     *      'nome' = 'Geraldo' (Se não informado não será atualizado)
     *      'valor_hora' = 24.00 (Se não informado não será atualizado)
     * }
     *
     * Retorna os dados da instância atualizados
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function atualizar(Request $request): JsonResponse
    {
        //Valida os dados
        try {
            $validado = validator($request->all(), [
                ['id' => 'required|integer'],
                ['nome' => 'nullable|string'],
                ['valor_hora' => 'nullable|numeric']], [
                'id.required' => 'O id é obrigatório'])->validate();;
        } catch (ValidationException $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        //Obtém o consultor para atualizar
        $consultor = Consultor::find($validado['id']);

        //Checa se consultor é nulo
        if ($consultor == null) {
            return new JsonResponse(['erro' => 'id não encontrado', 400]);
        }

        //Atualiza os dados do consultor
        $strings = ['nome', 'valor_hora'];
        foreach ($strings as $string) {
            if (array_key_exists($string, $validado)) {
                $consultor->$string = $validado[$string];
            }
        }

        //Salva as mudanças e retorna
        $consultor->save();
        return new JsonResponse($consultor, 200);
    }

    /**
     * Este é o DELETE do /consultor
     *
     * Exemplo de uso:
     * DELETE /consultor
     * body {
     *      'id' = 2
     * }
     *
     * Retorna código HTTP 200
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deletar(Request $request): JsonResponse
    {
        //Valida os dados
        try {
            $validado = validator($request->all(), ['required|id' => 'integer'])->validate();;
        } catch (ValidationException $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        //Procura no banco de dados se o consultor está registrado em um compromisso
        $compromissos = Compromisso::query()->where('consultor_codigo', $validado['id'])->get();

        //Caso esteja, retorna erro
        if ($compromissos->count() > 0)
            return new JsonResponse(['erro' => 'o consultor ainda tem compromissos', 400]);

        //Caso contrário, ele será deletado
        Consultor::destroy($validado['id']);
        return new JsonResponse([], 200);
    }

    /**
     * Este é o GET do /consultor
     *
     * Para usá-lo será opcional o uso de uma string 'query' onde o banco de dados irá pesquisar o nome do consultor
     * tanto quanto o valor_hora do consultor
     *
     * Exemplo de uso:
     * GET /consultor
     * body {
     *      'query' = 'geraldo 22' (opcional)
     *      'página' = 2 (opcional) (cada página tem 10 itens)
     * }
     *
     * Retorna código HTTP 200
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pesquisar(Request $request): JsonResponse
    {
        //Valida os dados
        try {
            $validado = validator($request->all(), [
                'query' => 'string',
                'pagina' => 'integer'
            ])->validate();;
        } catch (ValidationException $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        //Troca para valores padrão caso os dados não forem informados
        $query = $validado['query'] ?? '';
        $pagina = $validado['pagina'] ?? 1;

        //Retorna um erro se a página for menor que 0
        if ($pagina < 1)
            return new JsonResponse(['erro' => 'página abaixo de 1'], 400);

        //Caso o campo 'query' não seja informado, ele fará apenas uma busca dos 10 itens da página informada
        if ($query == '')
            return new JsonResponse(Consultor::query()->skip(10 * ($pagina - 1))->limit(10)->get());

        //Faz a busca e a retorna
        return new JsonResponse(
            Consultor::query()
                ->whereLike('nome', $query)
                ->orWhereLike('valor_hora', $query)
                ->skip(10 * ($pagina - 1))
                ->limit(10)
                ->get(['id', 'nome', 'valor_hora'])
        );
    }
}
