<?php

namespace App\Services;

use Anthropic\Client;
use App\Models\Categoria;
use Carbon\Carbon;
use RuntimeException;

class InterpretacaoMovimentacaoService
{
    /**
     * Como cada dado que falta é dito ao usuário.
     */
    private const CAMPOS = [
        'categoria' => 'a categoria',
        'valor' => 'o valor',
        'tipo' => 'se é entrada ou saída',
        'parcela' => 'em quantas parcelas',
    ];

    /**
     * Campos sem os quais a movimentação não pode ser montada.
     */
    private const OBRIGATORIOS = [
        'categoria' => 'categoria_id',
        'valor' => 'valor',
        'tipo' => 'tipo',
    ];

    /**
     * Interpreta a fala do usuário e devolve um objeto compatível com a
     * API de salvar movimentação (POST /api/movimentacoes).
     *
     * Quando o texto não tem informação suficiente (falta valor ou não dá
     * para deduzir a categoria), lança RuntimeException com a mensagem que
     * o controller devolve ao front.
     */
    public function interpretar(string $texto): array
    {
        $chave = config('services.anthropic.key');

        if (empty($chave)) {
            throw new RuntimeException('O reconhecimento por voz não está configurado (ANTHROPIC_API_KEY ausente).');
        }

        $categorias = Categoria::orderBy('descricao')->get(['id', 'descricao']);

        if ($categorias->isEmpty()) {
            throw new RuntimeException('Cadastre ao menos uma categoria antes de lançar por voz.');
        }

        $client = new Client(apiKey: $chave);

        $resposta = $client->messages->create(
            model: config('services.anthropic.model'),
            maxTokens: 4000,
            // Extração curta e bem especificada: effort baixo reduz a espera do usuário.
            outputConfig: ['effort' => 'low'],
            system: $this->prompt($categorias),
            messages: [
                ['role' => 'user', 'content' => $texto],
            ],
        );

        return $this->lerResposta($resposta);
    }

    /**
     * Monta as instruções: formato do JSON, lista de categorias e os
     * padrões assumidos quando o usuário não fala o campo.
     */
    private function prompt($categorias): string
    {
        $hoje = Carbon::now();

        $listaCategorias = $categorias
            ->map(fn($categoria) => "- {$categoria->id}: {$categoria->descricao}")
            ->implode("\n");

        return <<<PROMPT
        Você interpreta a fala de um usuário lançando uma movimentação financeira e devolve os dados estruturados.

        Responda SEMPRE com um único objeto JSON, sem markdown, sem crases e sem texto em volta.

        Quando conseguir interpretar, devolva:
        {
            "categoria_id": 3,
            "valor": 149.90,
            "tipo": "D",
            "data_registro": "2026-08-13",
            "flg_credito": "N",
            "parcela": 1,
            "faturamento_ym": 202608,
            "descricao_interpretada": "resumo curto do que foi entendido"
        }

        Quando faltar informação essencial, NÃO invente valores. Devolva apenas:
        {"faltando": ["categoria", "valor"]}

        Em "faltando", liste todos os dados que você não conseguiu extrair da fala, usando exatamente
        estes nomes: "categoria" (nenhuma das categorias disponíveis serve ou a fala não diz do que se trata),
        "valor" (não há quantia identificável), "tipo" (não dá para saber se é entrada ou saída),
        "parcela" (a fala indica parcelamento mas não diz em quantas vezes).
        Só liste o que realmente falta — campos com padrão definido abaixo nunca entram nessa lista.

        Se não conseguir interpretar a fala de forma alguma, devolva:
        {"faltando": ["categoria", "valor"]}

        Regras dos campos:

        - categoria_id: escolha o id da categoria que melhor se encaixa na fala, entre as disponíveis:
        {$listaCategorias}
          Se nenhuma categoria fizer sentido para o que foi dito, devolva erro.

        - valor: número decimal, sempre o valor de UMA parcela. Aceite "mil" = 1000, "cento e cinquenta" = 150,
          "dez e noventa" = 10.90. Sem valor identificável, devolva erro.

        - tipo: "F" para faturamento (entrada de dinheiro: salário, recebimento, venda, pix recebido)
          ou "D" para despesa (saída de dinheiro: compra, pagamento, conta, gasto). Na dúvida, use "D".

        - data_registro: formato AAAA-MM-DD. Se o usuário não disser a data, use {$hoje->format('Y-m-d')}.
          Interprete referências relativas ("ontem", "sexta passada", "dia 5") a partir dessa data.

        - flg_credito: "S" quando a fala indicar cartão de crédito ou parcelamento; "N" caso contrário.

        - parcela: quantidade de parcelas. Só maior que 1 quando flg_credito for "S"; caso contrário, sempre 1.
          Atenção ao que o número multiplica:
          "10x de mil" -> são 10 parcelas de 1000, então valor = 1000 e parcela = 10.
          "mil parcelado em 10x" -> o total é 1000 dividido em 10, então valor = 100 e parcela = 10.
          Ou seja: quando o usuário falar o valor TOTAL, divida pelo número de parcelas e arredonde em 2 casas.

        - faturamento_ym: inteiro AAAAMM da fatura da primeira parcela. Se o usuário não disser,
          use {$hoje->format('Ym')}.

        - descricao_interpretada: uma frase curta resumindo o que você entendeu, para o usuário conferir.
        PROMPT;
    }

    /**
     * Extrai o JSON do texto devolvido pelo modelo e valida o mínimo
     * necessário antes de entregar ao controller.
     */
    private function lerResposta($resposta): array
    {
        $texto = '';

        foreach ($resposta->content as $bloco) {
            if ($bloco->type === 'text') {
                $texto .= $bloco->text;
            }
        }

        $dados = json_decode(trim($texto), true);

        if (! is_array($dados)) {
            throw new RuntimeException('Não entendi o que foi falado. Repita dizendo o que foi e quanto custou.');
        }

        $faltando = $this->faltando($dados);

        if ($faltando !== []) {
            throw new RuntimeException($this->mensagem($faltando));
        }

        // Recorrência ainda não é interpretada: fora do prompt para economizar
        // tokens, e fixada aqui para o modelo não decidir por conta própria.
        $dados['recorrente'] = 'N';

        return $dados;
    }

    /**
     * O que o modelo apontou tem prioridade — ele sabe o que entendeu da
     * fala. Só quando não aponta nada reconhecível é que conferimos os
     * obrigatórios do próprio payload. A ordem segue self::CAMPOS.
     */
    private function faltando(array $dados): array
    {
        $apontados = $this->conhecidos(array_map('strval', (array) ($dados['faltando'] ?? [])));

        if ($apontados !== []) {
            return $apontados;
        }

        $ausentes = [];

        foreach (self::OBRIGATORIOS as $rotulo => $campo) {
            if (! isset($dados[$campo]) || $dados[$campo] === '') {
                $ausentes[] = $rotulo;
            }
        }

        return $this->conhecidos($ausentes);
    }

    /**
     * Descarta rótulos que o modelo tenha inventado fora de self::CAMPOS.
     */
    private function conhecidos(array $rotulos): array
    {
        return array_values(array_intersect(array_keys(self::CAMPOS), $rotulos));
    }

    /**
     * "Faltou informar a categoria e o valor. Repita a gravação incluindo esses dados."
     */
    private function mensagem(array $faltando): string
    {
        $rotulos = array_map(fn($campo) => self::CAMPOS[$campo], $faltando);
        $ultimo = array_pop($rotulos);

        $lista = $rotulos === [] ? $ultimo : implode(', ', $rotulos) . ' e ' . $ultimo;
        $complemento = count($faltando) === 1 ? 'esse dado' : 'esses dados';

        return "Faltou informar {$lista}. Repita a gravação incluindo {$complemento}.";
    }
}
