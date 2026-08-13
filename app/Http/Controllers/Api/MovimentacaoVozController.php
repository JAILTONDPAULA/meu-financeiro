<?php

namespace App\Http\Controllers\Api;

use Anthropic\Core\Exceptions\APIStatusException;
use App\Http\Controllers\Controller;
use App\Services\InterpretacaoMovimentacaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class MovimentacaoVozController extends Controller
{
    public function __construct(private readonly InterpretacaoMovimentacaoService $service)
    {
    }

    /**
     * Interpreta um texto falado e devolve o objeto pronto para o
     * POST /api/movimentacoes.
     *
     * Espera um objeto JSON no corpo:
     *
     * {
     *     "texto": "gastei cento e cinquenta no mercado"    // string, obrigatório
     * }
     *
     * Devolve em JSON, com status 200, o mesmo formato aceito pela API de
     * salvar, mais um resumo do que foi entendido:
     *
     * {
     *     "categoria_id": 3,
     *     "valor": 149.90,
     *     "tipo": "D",
     *     "data_registro": "2026-08-13",
     *     "flg_credito": "N",
     *     "parcela": 1,
     *     "recorrente": "N",
     *     "faturamento_ym": 202608,
     *     "descricao_interpretada": "compra de 149,90 no mercado"
     * }
     *
     * Quando a fala não tem informação suficiente, devolve 422 com a
     * mensagem em texto cru explicando o que faltou.
     */
    public function interpretar(Request $request): Response|JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'texto' => ['required', 'string', 'min:3'],
            ], [
                'texto.required' => 'Nada foi reconhecido na gravação.',
                'texto.string' => 'Texto inválido.',
                'texto.min' => 'A gravação ficou curta demais para ser interpretada.',
            ]);

            if ($validator->fails()) {
                return response($validator->errors()->first(), 422);
            }

            $movimentacao = $this->service->interpretar($validator->validated()['texto']);

            return response()->json($movimentacao, 200);
        } catch (RuntimeException $e) {
            return response($e->getMessage(), 422);
        } catch (APIStatusException $e) {
            report($e);

            return response($this->mensagemDaApi($e), 502);
        } catch (Throwable $e) {
            report($e);

            return response('Não foi possível interpretar a gravação. Tente novamente.', 500);
        }
    }

    /**
     * Traduz a falha da API da Anthropic para algo acionável, em vez de
     * culpar a gravação por um problema de conta ou de serviço.
     */
    private function mensagemDaApi(APIStatusException $e): string
    {
        $corpo = (string) json_encode($e->body);

        if ($e->status === 400 && str_contains($corpo, 'credit balance')) {
            return 'Sem créditos na API da Anthropic. Recarregue em Plans & Billing no console.';
        }

        return match ($e->status) {
            401, 403 => 'Chave da API da Anthropic inválida ou sem permissão.',
            429 => 'Muitas gravações seguidas. Aguarde alguns segundos e tente de novo.',
            529 => 'O serviço de interpretação está sobrecarregado. Tente novamente em instantes.',
            default => 'O serviço de interpretação recusou a requisição. Tente novamente.',
        };
    }
}
