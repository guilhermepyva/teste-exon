<?php

namespace App\Http\Controllers;

use App\Models\Compromisso;
use App\Models\Consultor;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CompromissoController
{
    public function cadastrar(Request $request): JsonResponse
    {
        try {
            $validado = validator($request->all(), [
                'consultor_codigo' => 'required|integer',
                'data' => 'required|date',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fim' => 'required|date_format:H:i',
                'intervalo' => 'required|date_format:H:i'
            ])->validate();
        } catch (Throwable $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        if (Consultor::find($validado['consultor_codigo']) == null) {
            return new JsonResponse(['erro' => 'consultor não encontrado'], 400);
        }

        return new JsonResponse(Compromisso::create([
            'consultor_codigo' => $validado['consultor_codigo'],
            'data' => $this->formatar_data($validado['data']),
            'hora_inicio' => $this->formatar_hora($validado['hora_inicio']),
            'hora_fim' => $this->formatar_hora($validado['hora_fim']),
            'intervalo' => $this->formatar_hora($validado['intervalo'])
        ]));
    }

    public function atualizar(Request $request): JsonResponse
    {
        try {
            $validado = validator($request->all(), [
                'id' => 'required|integer',
                'consultor_codigo' => 'integer',
                'data' => 'date',
                'hora_inicio' => 'date_format:H:i',
                'hora_fim' => 'date_format:H:i',
                'intervalo' => 'date_format:H:i'
            ])->validate();
        } catch (Throwable $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        $compromisso = Compromisso::find($validado['id']);

        if ($compromisso == null) {
            return new JsonResponse(['erro' => 'id não encontrado'], 400);
        }

        $strings = ['consultor_codigo', 'data', 'hora_inicio', 'hora_fim', 'intervalo'];
        foreach ($strings as $string) {
            if (array_key_exists($string, $validado)) {
                if ($string == 'consultor_codigo' && Consultor::find($validado['consultor_codigo']) == null) {
                    return new JsonResponse(['erro' => 'consultor não encontrado'], 400);
                }
                $compromisso->$string = $validado[$string];
            }
        }

        $compromisso->save();

        if (array_key_exists('data', $validado))
            $compromisso->data = $this->formatar_data($validado['data']);
        return new JsonResponse($compromisso);
    }

    public function deletar(Request $request): JsonResponse {
        try {
            $validado = validator($request->all(), [
                'id' => 'required|integer',
            ])->validate();
        } catch (Throwable $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }

        Compromisso::destroy(7);
        return new JsonResponse(200);
    }

    public function pesquisar(Request $request): JsonResponse {
        try {
            $validado = validator($request->all(), [
                'data' => 'required|date',
                'hora_inicio' => 'required|date_format:H:i',
                'hora_fim' => 'required|date_format:H:i',
                'intervalo' => 'required|date_format:H:i'
            ])->validate();
        } catch (Throwable $e) {
            return new JsonResponse(['erro' => $e->getMessage()], 400);
        }
    }

    public function formatar_data(string $string): string
    {
        return (new DateTime($string))->format('Y-m-d');
    }

    public function formatar_hora(string $string): string
    {
        return (new DateTime($string))->format('H:i');
    }
}
