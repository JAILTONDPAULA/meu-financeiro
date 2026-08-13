<?php

namespace App\Services;

use App\Models\Movimentacao;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MovimentacaoService
{
    /**
     * Movimentações do usuário no mês informado (formato Y-m).
     *
     * O filtro define o campo de referência: "faturamento" usa o
     * faturamento_ym (mês da fatura do cartão) e "data" usa a
     * data_registro (quando o lançamento aconteceu de fato).
     */
    public function listar(int $usuarioId, string $filtro, string $mes): Collection
    {
        $referencia = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();

        return Movimentacao::with('categoria')
            ->where('user_id', $usuarioId)
            ->when(
                $filtro === 'faturamento',
                fn($query) => $query->where('faturamento_ym', (int) $referencia->format('Ym')),
                fn($query) => $query->whereBetween('data_registro', [
                    $referencia->toDateString(),
                    $referencia->copy()->endOfMonth()->toDateString(),
                ]),
            )
            ->orderBy('data_registro')
            ->orderBy('id')
            ->get();
    }

    /**
     * Exclui uma movimentação do usuário informado.
     *
     * O vínculo movimentacao_original_id é cascadeOnDelete, então apagar a
     * primeira parcela leva junto as demais; apagar uma parcela do meio
     * remove só ela.
     *
     * Devolve false quando a movimentação não existe ou é de outro usuário.
     */
    public function excluir(int $id, int $usuarioId): bool
    {
        $movimentacao = Movimentacao::where('id', $id)
            ->where('user_id', $usuarioId)
            ->first();

        if (! $movimentacao) {
            return false;
        }

        return (bool) $movimentacao->delete();
    }

    /**
     * Cria a movimentação do usuário informado e, quando for crédito
     * parcelado, também as parcelas seguintes.
     *
     * Recebe os dados já validados pelo controller. Regra das parcelas:
     * a primeira movimentação nasce com movimentacao_original_id nulo e as
     * demais apontam para o id dela; o faturamento_ym avança um mês a cada
     * parcela, a partir do mês informado (ou do mês de data_registro).
     *
     * Devolve todas as movimentações criadas, na ordem das parcelas.
     */
    public function salvar(array $dados, int $usuarioId): Collection
    {
        $credito = ($dados['flg_credito'] ?? 'N') === 'S';
        $parcelas = $credito ? (int) ($dados['parcela'] ?? 1) : 1;

        unset($dados['parcela']);
        $dados['user_id'] = $usuarioId;

        return DB::transaction(function () use ($dados, $parcelas, $credito) {
            $faturamento = $credito ? $this->faturamentoInicial($dados) : null;
            $criadas = collect();
            $original = null;

            for ($parcela = 0; $parcela < $parcelas; $parcela++) {
                $movimentacao = Movimentacao::create(array_merge($dados, [
                    'movimentacao_original_id' => $original?->id,
                    'faturamento_ym' => $credito
                        ? $this->somarMeses($faturamento, $parcela)
                        : ($dados['faturamento_ym'] ?? null),
                ]));

                // refresh traz os defaults aplicados pelo banco (data_registro,
                // flg_credito, recorrente) que o create() não devolve; a
                // categoria vai junto porque o front usa ícone e descrição.
                $original ??= $movimentacao;
                $criadas->push($movimentacao->refresh()->load('categoria'));
            }

            return $criadas;
        });
    }

    /**
     * Mês (YYYYMM) da fatura da primeira parcela: o informado ou, na falta
     * dele, o mês da data de registro.
     */
    private function faturamentoInicial(array $dados): int
    {
        if (! empty($dados['faturamento_ym'])) {
            return (int) $dados['faturamento_ym'];
        }

        return (int) Carbon::parse($dados['data_registro'] ?? now())->format('Ym');
    }

    /**
     * Avança meses sobre um YYYYMM, virando o ano quando passa de dezembro.
     */
    private function somarMeses(int $faturamentoYm, int $meses): int
    {
        $data = Carbon::createFromFormat('Ym', (string) $faturamentoYm)
            ->startOfMonth()
            ->addMonths($meses);

        return (int) $data->format('Ym');
    }
}
