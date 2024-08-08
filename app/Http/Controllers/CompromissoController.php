<?php

namespace App\Http\Controllers;

use App\Models\Compromisso;
use App\Models\Consultor;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controlador da tabela e das rotas dos compromissos
 */
class CompromissoController
{
    /**
     * Este é o POST do /compromisso
     *
     * Exemplo de uso:
     *
     * POST /compromisso
     * body {
     *      'consultor_codigo' = 2
     *      'data_inicio' = '2024-08-01'
     *      'data_fim' = '2024-08-08'
     *      'hora_inicio' = '08:00'
     *      'hora_fim' = '23:59'
     *      'intervalo' = '00:30'
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
                'consultor_codigo' => 'required|integer',
                'data_inicio' => 'required|date_format:Y-m-d',
                'data_fim' => 'required|date_format:Y-m-d',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fim' => 'required|date_format:H:i',
                'intervalo' => 'required|date_format:H:i'
            ])->validate();
        } catch (ValidationException $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        //Procura o consultor referenciado, caso não seja encontrado será retornado um erro
        if (Consultor::find($validado['consultor_codigo']) == null)
            return new JsonResponse(['erro' => 'consultor não encontrado'], 400);

        return new JsonResponse(Compromisso::create([
            'consultor_codigo' => $validado['consultor_codigo'],
            'data_inicio' => $this->formatar_data($validado['data_inicio']),
            'data_fim' => $this->formatar_data($validado['data_fim']),
            'hora_inicio' => $this->formatar_hora($validado['hora_inicio']),
            'hora_fim' => $this->formatar_hora($validado['hora_fim']),
            'intervalo' => $this->formatar_hora($validado['intervalo'])
        ]));
    }

    /**
     * Este é o PUT do /compromisso
     *
     * Exemplo de uso:
     *
     * PUT /compromisso
     * body {
     *      'id' = 1
     *      'consultor_codigo' = 2 (Se não informado não será atualizado)
     *      'data_inicio' = '2024-12-05' (Se não informado não será atualizado)
     *      'data_fim' = '2024-12-24' (Se não informado não será atualizado)
     *      'hora_inicio' = '17:00'(Se não informado não será atualizado)
     *      'hora_fim' = '22:00' (Se não informado não será atualizado)
     *      'intervalo' = '01:00'(Se não informado não será atualizado)
     * }
     *
     * Retorna os dados da instância atualizada
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function atualizar(Request $request): JsonResponse
    {
        //Valida os dados
        try {
            $validado = validator($request->all(), [
                'id' => 'required|integer',
                'consultor_codigo' => 'integer',
                'data_inicio' => 'date_format:Y-m-d',
                'data_fim' => 'date_format:Y-m-d',
                'hora_inicio' => 'date_format:H:i',
                'hora_fim' => 'date_format:H:i',
                'intervalo' => 'date_format:H:i'
            ])->validate();
        } catch (ValidationException $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        //Tenta buscar pelo id do compromisso
        $compromisso = Compromisso::find($validado['id']);

        //Caso não seja encontrado, retornará erro
        if ($compromisso == null) {
            return new JsonResponse(['erro' => 'id não encontrado'], 400);
        }

        //Atualiza os dados do compromisso
        $strings = ['consultor_codigo', 'data_inicio', 'data_fim', 'hora_inicio', 'hora_fim', 'intervalo'];
        foreach ($strings as $string) {
            if (array_key_exists($string, $validado)) {
                if ($string == 'consultor_codigo' && Consultor::find($validado['consultor_codigo']) == null)
                    return new JsonResponse(['erro' => 'consultor não encontrado'], 400);
                $compromisso->$string = $validado[$string];
            }
        }

        //Salva os dados do compromisso na tabela
        $compromisso->save();

        //Formata as datas para elas aparecerem no modelo brasileiro e bonitas no retorno e retorna
        if (array_key_exists('data_inicio', $validado))
            $compromisso->data_inicio = $this->formatar_data($validado['data_inicio']);
        if (array_key_exists('data_fim', $validado))
            $compromisso->data_inicio = $this->formatar_data($validado['data_fim']);
        return new JsonResponse($compromisso);
    }

    /**
     * Este é o DELETE do /compromisso
     *
     * Exemplo de uso:
     *
     * DELETE /compromisso
     *      ?id=1
     *
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
            $validado = validator($request->all(), [
                'id' => 'required|integer',
            ])->validate();
        } catch (ValidationException $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        //Deleta os compromissos
        Compromisso::destroy($validado['id']);
        return new JsonResponse(200);
    }

    /**
     * Este é o GET do /compromisso
     *
     * Exemplo de uso:
     *
     * GET /compromisso
     *      ?consultor_codigo=2 (opcional)
     *      &data_inicio=2024-12-01 (opcional)
     *      &data_fim=2024-12-24 (opcional)
     *
     * Exemplo de retorno:
     *
     * {
     *     'dados': [
     *          {
     *              "dados": {
     *                  "id": 1,
     *                  "consultor_codigo": 1,
     *                  "data_inicio": "2024-12-04",
     *                  "data_fim": "2024-12-05",
     *                  "hora_inicio": "20:44:00",
     *                  "hora_fim": "23:44:00",
     *                  "intervalo": "00:30:00"
     *              },
     *              "total_horas": "03:00",
     *              "total_valor": 37.5
     *          }
     *     ]
     *     'soma_valores': 37.5
     * }
     *
     * Retorno em JSON da busca
     * @param Request $request
     * @return JsonResponse
     */
    public function pesquisar(Request $request): JsonResponse
    {
        //Valida os dados
        try {
            $validado = validator($request->all(), [
                'data_inicio' => 'date_format:Y-m-d',
                'data_fim' => 'date_format:Y-m-d',
                'consultor_codigo' => 'integer',
                'pagina' => 'integer'
            ])->validate();
        } catch (ValidationException $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        //Inicia a query e define a página padrão
        $query = Compromisso::query();
        $pagina = $validado['pagina'] ?? 1;

        //Caso a página seja abaixo de 1, retornará erro
        if ($pagina < 1)
            return new JsonResponse(['erro' => 'página abaixo de 1'], 400);

        //Filtros
        if (array_key_exists('data_inicio', $validado))
            $query = $query->whereDate('data_inicio', '>=', $validado['data_inicio']);
        if (array_key_exists('data_fim', $validado))
            $query = $query->whereDate('data_fim', '<=', $validado['data_fim']);
        if (array_key_exists('consultor_codigo', $validado)) {
            $consultor = Consultor::find($validado['consultor_codigo']);
            if ($consultor != null)
                $query = $query->where('consultor_codigo', $validado['consultor_codigo']);
            //Se o consultor do filtro não existir, retornará erro
            else
                return new JsonResponse(['consultor não encontrado'], 400);
        }

        //Aplica o skip das páginas e pega os 10 itens do filtro
        $compromissos = $query->skip(10 * ($pagina - 1))->limit(10)->get();

        //Inicializa os valores para o array
        $array = [];
        $soma_valores = 0;
        foreach ($compromissos as $compromisso) {
            $sub_array = [];
            $consultor = Consultor::find($compromisso->consultor_codigo);

            $sub_array['dados'] = $compromisso;

            //Pega o timestamp da hora_inicio e da hora_fim para usa-lás em calcúlos
            $hora_inicio = (new DateTime($compromisso->hora_inicio))->getTimestamp();
            $hora_fim = (new DateTime($compromisso->hora_fim))->getTimestamp();

            //Calcula e formata o total de horas
            $total_millisegundos = $hora_fim - $hora_inicio;
            $sub_array['total_horas'] = DateTime::createFromFormat('U', $total_millisegundos)->format('H:i');

            //Calcula o valor das horas
            $total_valor = sprintf("%.2f", $total_millisegundos * (float)$consultor->valor_hora) / 60.0 / 60.0;
            $sub_array['total_valor'] = $total_valor;

            //Soma ao total dos filtros da listagem e anexação ao array maior
            $soma_valores += $total_valor;
            $array[] = $sub_array;
        }

        //Retorna um array com os dados coletados e com a soma dos valores de toda a listagem
        return new JsonResponse(['dados' => $array, 'soma_valores' => $soma_valores], 200);
    }

    /**
     *
     * Usado para formatar a data em um modelo brasileiro
     * (não consegui usar YYYY/MM/DD, quando eu usava ficava YYYY\/MM\/DD)
     *
     * @param string $string
     * @return string
     * @throws \Exception
     */
    public function formatar_data(string $string): string
    {
        return (new DateTime($string))->format('Y-m-d');
    }

    /**
     * Usado para formatar a hora
     *
     * @param string $string
     * @return string
     * @throws \Exception
     */
    public function formatar_hora(string $string): string
    {
        return (new DateTime($string))->format('H:i');
    }
}
