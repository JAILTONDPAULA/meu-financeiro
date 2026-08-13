<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MovimentacaoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Throwable;

class MovimentacaoController extends Controller
{
    public function __construct(private readonly MovimentacaoService $service)
    {
    }

    /**
     * Lista as movimentações do usuário autenticado num mês.
     *
     * Espera na query string:
     *
     *     filtro=faturamento|data    // obrigatório, campo de referência do mês
     *     mes=2026-08                // obrigatório, formato AAAA-MM
     *
     * "faturamento" filtra por faturamento_ym (mês da fatura do cartão);
     * "data" filtra por data_registro (quando o lançamento aconteceu).
     *
     * Devolve em JSON a lista de movimentações com a categoria embutida,
     * ordenada por data e por id.
     */
    public function listar(Request $request): Response|JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'filtro' => ['required', 'string', 'in:faturamento,data'],
                'mes' => ['required', 'date_format:Y-m'],
            ], [
                'filtro.required' => 'Informe o filtro do período.',
                'filtro.in' => 'Filtro inválido.',
                'mes.required' => 'Informe o mês.',
                'mes.date_format' => 'Informe o mês no formato AAAA-MM.',
            ]);

            if ($validator->fails()) {
                return response($validator->errors()->first(), 422);
            }

            $dados = $validator->validated();

            $movimentacoes = $this->service->listar(
                $request->user()->id,
                $dados['filtro'],
                $dados['mes'],
            );

            return response()->json($movimentacoes, 200);
        } catch (Throwable $e) {
            report($e);

            return response('Não foi possível carregar as movimentações.', 500);
        }
    }

    /**
     * Exclui uma movimentação do usuário autenticado.
     *
     * Recebe o id pela rota. Excluir a primeira parcela de um crédito
     * parcelado remove as demais em cascata.
     *
     * Devolve 200 com texto de confirmação, ou 404 quando o id não existe
     * ou pertence a outro usuário.
     */
    public function excluir(Request $request, int $id): Response
    {
        try {
            $excluida = $this->service->excluir($id, $request->user()->id);

            if (! $excluida) {
                return response('Movimentação não encontrada.', 404);
            }

            return response('Movimentação excluída.', 200);
        } catch (Throwable $e) {
            report($e);

            return response('Não foi possível excluir a movimentação.', 500);
        }
    }

    /**
     * Salva uma movimentação do usuário autenticado.
     *
     * Espera um objeto JSON no corpo:
     *
     * {
     *     "categoria_id": 3,                  // int, obrigatório, categoria existente
     *     "valor": 149.90,                    // decimal(12,2), obrigatório, maior que zero
     *     "tipo": "D",                        // "F" ou "D", obrigatório
     *     "data_registro": "2026-08-13",      // date Y-m-d, opcional (padrão: data atual)
     *     "flg_credito": "N",                 // "S" ou "N", opcional (padrão: "N")
     *     "parcela": 3,                       // int >= 1, obrigatório quando flg_credito = "S"
     *     "recorrente": "N",                  // "S" ou "N", opcional (padrão: "N")
     *     "faturamento_ym": 202608            // int YYYYMM, opcional, fatura da 1ª parcela
     * }
     *
     * O user_id NÃO é aceito no corpo: vem sempre da sessão autenticada.
     * O movimentacao_original_id também NÃO é aceito: quem o preenche é o
     * service, ao gerar as parcelas do crédito.
     *
     * Quando flg_credito = "S" e parcela > 1, é criado um registro por
     * parcela: o primeiro com movimentacao_original_id nulo e os demais
     * apontando para ele, com o faturamento_ym avançando um mês a cada um.
     *
     * Devolve em JSON a lista das movimentações criadas, com status 201.
     */
    public function salvar(Request $request): Response|JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
                'valor' => ['required', 'numeric', 'min:0.01'],
                'tipo' => ['required', 'string', 'in:F,D'],
                'data_registro' => ['nullable', 'date_format:Y-m-d'],
                'flg_credito' => ['nullable', 'string', 'in:S,N'],
                'parcela' => ['required','integer','min:1'],
                'recorrente' => ['nullable', 'string', 'in:S,N'],
                'faturamento_ym' => ['nullable', 'integer', 'digits:6'],
            ], [
                'categoria_id.required' => 'Informe a categoria.',
                'categoria_id.integer' => 'Categoria inválida.',
                'categoria_id.exists' => 'A categoria informada não existe.',
                'valor.required' => 'Informe o valor.',
                'valor.numeric' => 'Informe um valor numérico.',
                'valor.min' => 'O valor deve ser maior que zero.',
                'tipo.required' => 'Informe o tipo da movimentação.',
                'tipo.in' => 'Tipo inválido.',
                'data_registro.date_format' => 'Informe a data no formato AAAA-MM-DD.',
                'flg_credito.in' => 'Indicador de crédito inválido.',
                'parcela.required_if' => 'Informe a quantidade de parcelas para a movimentação no crédito.',
                'parcela.prohibited' => 'Só é possível parcelar movimentações no crédito.',
                'parcela.integer' => 'Quantidade de parcelas inválida.',
                'parcela.min' => 'A quantidade de parcelas deve ser no mínimo 1.',
                'recorrente.in' => 'Indicador de recorrência inválido.',
                'faturamento_ym.integer' => 'Mês de faturamento inválido.',
                'faturamento_ym.digits' => 'Informe o mês de faturamento no formato AAAAMM.',
            ]);

            if ($validator->fails()) {
                return response($validator->errors()->first(), 422);
            }

            $movimentacoes = $this->service->salvar(
                array_filter($validator->validated(), fn($valor) => $valor !== null),
                $request->user()->id
            );

            return response()->json($movimentacoes, 201);
        } catch (Throwable $e) {
            report($e);

            return response('Não foi possível salvar a movimentação. Tente novamente.', 500);
        }
    }
}
