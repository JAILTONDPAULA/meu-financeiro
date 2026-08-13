import '../../sass/pages/historico-movimentacoes.sass';
import MovimentacaoApi from '../apis/movimentacao.js';

const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

const MESES = ['JANEIRO', 'FEVEREIRO', 'MARÇO', 'ABRIL', 'MAIO', 'JUNHO', 'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO'];
const DIAS = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];

const DIALOG_EXCLUIR = 'dialog-excluir-movimentacao';
const ARRASTO_MINIMO = 90;
// Casa com a animação de entrada do Dialog (.2s).
const RETORNO_ARRASTO = 220;

const Formato = {
    dinheiro(valor) {
        return Number(valor).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    // "2026-08-13T00:00:00.000000Z" -> "2026-08-13", sem conversão de fuso.
    dia(dataRegistro) {
        return String(dataRegistro).slice(0, 10);
    },

    dataBR(dia) {
        const [ano, mes, numero] = dia.split('-');

        return `${numero}/${mes}/${ano}`;
    },

    diaSemana(dia) {
        const [ano, mes, numero] = dia.split('-').map(Number);

        return DIAS[new Date(ano, mes - 1, numero).getDay()];
    },

    texto(valor) {
        return $('<div>').text(valor ?? '').html();
    },
};

class Page {
    static recognition = null;
    static parando = false;
    static consumidos = 0;
    static trechos = [];
    static arrasto = null;

    static events = {
        init() {
            Page.events.dom();
            Page.historico.carregar();
            Preload.destroy();
        },

        dom() {
            $(document)
                .on('pointerdown', '.adicionar .microfone', event => Page.methods.pressionar(event))
                .on('pointerup pointercancel', '.adicionar .microfone', _ => Page.methods.soltar())
                .on('contextmenu', '.adicionar .microfone', event => event.preventDefault())
                .on('change', '.historico .filtro .mes', _ => Page.historico.trocarMes())
                .on('change', '.historico .filtro input[name="filtro"]', _ => Page.historico.carregar())
                .on('pointerdown', '.registros .registro', event => Page.exclusao.iniciar(event))
                .on('pointermove', '.registros .registro', event => Page.exclusao.mover(event))
                .on('pointerup pointercancel', '.registros .registro', event => Page.exclusao.soltar(event))
                .on('submit', `#${DIALOG_EXCLUIR}`, event => Page.exclusao.confirmar(event));
        },
    };

    static methods = {
        pressionar(event) {
            event.preventDefault();

            // Prende o ponteiro ao botão: sem isso, arrastar o dedo (ou o
            // mouse) para fora encerra a gravação no meio da fala.
            const botao = event.currentTarget;
            const ponteiro = event.pointerId ?? event.originalEvent?.pointerId;

            if (botao.setPointerCapture && ponteiro !== undefined) {
                botao.setPointerCapture(ponteiro);
            }

            if (Page.recognition) {
                return;
            }

            Page.trechos = [];
            Page.methods.gravar();
        },

        soltar() {
            if (!Page.recognition) {
                return;
            }

            Page.parando = true;
            Page.recognition.stop();
        },

        gravar() {
            if (!SpeechRecognition) {
                Toast.error('Seu navegador não suporta reconhecimento de voz.');
                return;
            }

            const recognition = new SpeechRecognition();

            recognition.lang = 'pt-BR';
            // continuous mantém a sessão viva nas pausas da fala; sem isso o
            // reconhecimento encerra no primeiro silêncio e o áudio dito
            // durante o reinício se perde.
            recognition.continuous = true;
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            recognition.onstart = _ => {
                Page.consumidos = 0;
                Page.methods.status(true, 'Escutando... solte para processar');
            };

            recognition.onresult = event => {
                if (Page.recognition !== recognition) {
                    return;
                }

                for (let i = 0; i < event.results.length; i++) {
                    if (i < Page.consumidos || !event.results[i].isFinal) {
                        continue;
                    }

                    Page.consumidos = i + 1;
                    Page.methods.registrar(event.results[i][0].transcript);
                }
            };

            recognition.onerror = event => {
                if (event.error === 'no-speech' || event.error === 'aborted') {
                    return;
                }

                Page.parando = true;

                if (event.error === 'not-allowed' || event.error === 'service-not-allowed') {
                    Toast.error('Permissão de microfone negada.');
                    return;
                }

                if (event.error === 'network') {
                    Toast.error('Sem conexão com o serviço de reconhecimento de voz.');
                    return;
                }

                Toast.error('Erro ao gravar: ' + event.error);
            };

            recognition.onend = _ => {
                recognition.onstart = null;
                recognition.onresult = null;
                recognition.onerror = null;
                recognition.onend = null;

                if (!Page.parando) {
                    Page.methods.gravar();
                    return;
                }

                Page.recognition = null;
                Page.parando = false;
                Page.methods.status(false, 'Segure o microfone e fale');

                Page.processamento.interpretar(Page.trechos.join(' '));
            };

            Page.recognition = recognition;
            recognition.start();
        },

        registrar(transcript) {
            const trecho = transcript.trim();

            if (!trecho || trecho === Page.trechos[Page.trechos.length - 1]) {
                return;
            }

            Page.trechos.push(trecho);
        },

        status(gravando, mensagem) {
            $('.adicionar .microfone').toggleClass('gravando', gravando);
            $('.adicionar .status').text(mensagem);
        },
    };

    static historico = {
        filtro() {
            return $('.historico .filtro input[name="filtro"]:checked').val();
        },

        mes() {
            return $('.historico .filtro .mes').val();
        },

        // Troca o rótulo visível junto com o input month escondido.
        trocarMes() {
            const [ano, mes] = Page.historico.mes().split('-');

            $('.historico .filtro .rotulo').text(`${MESES[Number(mes) - 1]} de ${ano}`);
            Page.historico.carregar();
        },

        carregar() {
            const filtro = Page.historico.filtro();
            const mes = Page.historico.mes();

            if (!filtro || !mes) {
                return;
            }

            return MovimentacaoApi.listar(filtro, mes)
                .then(movimentacoes => {
                    window.movimentacoes = movimentacoes;
                    Page.historico.renderizar();
                })
                .catch(_ => {});
        },

        // Acrescenta o que a API devolveu no cadastro, sem recarregar a lista.
        adicionar(cadastradas) {
            const novas = [].concat(cadastradas).filter(Page.historico.noPeriodo);

            if (!novas.length) {
                return;
            }

            window.movimentacoes = (window.movimentacoes ?? []).concat(novas);
            Page.historico.renderizar(novas.map(movimentacao => movimentacao.id));
        },

        // Uma parcela futura não pertence ao período aberto na tela.
        noPeriodo(movimentacao) {
            const mes = Page.historico.mes();

            if (Page.historico.filtro() === 'faturamento') {
                return String(movimentacao.faturamento_ym) === mes.replace('-', '');
            }

            return Formato.dia(movimentacao.data_registro).startsWith(mes);
        },

        remover(id) {
            const $registro = $(`.registros .registro[data-id="${id}"]`);

            const concluir = _ => {
                window.movimentacoes = (window.movimentacoes ?? []).filter(movimentacao => movimentacao.id !== id);
                Page.historico.renderizar();
            };

            if (!$registro.length) {
                concluir();
                return;
            }

            $registro.addClass('saindo').one('animationend', concluir);
        },

        renderizar(novas = []) {
            const grupos = Page.historico.agrupar(window.movimentacoes ?? []);
            const $registros = $('.historico .registros');

            if (!grupos.length) {
                $registros.html('<p class="vazio">Nenhuma movimentação neste período.</p>');
                return;
            }

            $registros.html(
                grupos
                    .map(([dia, movimentacoes]) => Page.historico.htmlDia(dia, movimentacoes, novas))
                    .join('')
            );
        },

        // [['2026-08-13', [...]], ['2026-08-11', [...]]] — mais recente primeiro.
        agrupar(movimentacoes) {
            const dias = new Map();

            movimentacoes.forEach(movimentacao => {
                const dia = Formato.dia(movimentacao.data_registro);

                if (!dias.has(dia)) {
                    dias.set(dia, []);
                }

                dias.get(dia).push(movimentacao);
            });

            return [...dias.entries()].sort(([a], [b]) => b.localeCompare(a));
        },

        totais(movimentacoes) {
            const somar = tipo => movimentacoes
                .filter(movimentacao => movimentacao.tipo === tipo)
                .reduce((total, movimentacao) => total + Number(movimentacao.valor), 0);

            const faturado = somar('F');
            const gasto = somar('D');

            return { faturado, gasto, saldo: faturado - gasto };
        },

        htmlDia(dia, movimentacoes, novas) {
            const { faturado, gasto, saldo } = Page.historico.totais(movimentacoes);

            return (
                '<article class="dia">' +
                    '<header>' +
                        '<div class="data">' +
                            `<strong>${Formato.diaSemana(dia)}</strong>` +
                            `<span>${Formato.dataBR(dia)}</span>` +
                        '</div>' +
                        '<div class="totais">' +
                            `<span class="faturado">+ R$ ${Formato.dinheiro(faturado)}</span>` +
                            `<span class="gasto">- R$ ${Formato.dinheiro(gasto)}</span>` +
                            `<span class="saldo${saldo < 0 ? ' negativo' : ''}">${saldo < 0 ? '- ' : ''}R$ ${Formato.dinheiro(Math.abs(saldo))}</span>` +
                        '</div>' +
                    '</header>' +
                    '<ul>' +
                        movimentacoes.map(movimentacao => Page.historico.htmlRegistro(movimentacao, novas)).join('') +
                    '</ul>' +
                '</article>'
            );
        },

        htmlRegistro(movimentacao, novas) {
            const faturamento = movimentacao.tipo === 'F';
            const icone = movimentacao.categoria?.icone ?? 'ellipsis';
            const descricao = movimentacao.categoria?.descricao ?? 'Sem categoria';

            return (
                `<li class="registro${novas.includes(movimentacao.id) ? ' entrando' : ''}" data-id="${movimentacao.id}">` +
                    `<div class="icone"><i class="fa-solid fa-${Formato.texto(icone)}"></i></div>` +
                    '<div class="descricao">' +
                        `<strong>${Formato.texto(descricao)}</strong>` +
                        '<div class="tags">' +
                            Page.historico.htmlTags(movimentacao) +
                        '</div>' +
                    '</div>' +
                    `<span class="valor ${faturamento ? 'faturamento' : 'despesa'}">${faturamento ? '+' : '-'} R$ ${Formato.dinheiro(movimentacao.valor)}</span>` +
                '</li>'
            );
        },

        htmlTags(movimentacao) {
            const tags = [movimentacao.flg_credito === 'S'
                ? '<span class="tag credito">Crédito</span>'
                : '<span class="tag">Débito</span>'];

            if (movimentacao.recorrente === 'S') {
                tags.push('<span class="tag">Recorrente</span>');
            }

            if (movimentacao.movimentacao_original_id) {
                tags.push('<span class="tag parcela">Parcela</span>');
            }

            return tags.join('');
        },
    };

    static exclusao = {
        iniciar(event) {
            const $registro = $(event.currentTarget);
            const ponteiro = event.pointerId ?? event.originalEvent?.pointerId;

            if (event.currentTarget.setPointerCapture && ponteiro !== undefined) {
                event.currentTarget.setPointerCapture(ponteiro);
            }

            Page.arrasto = {
                id: $registro.data('id'),
                x: event.clientX,
                y: event.clientY,
                ativo: false,
            };
        },

        mover(event) {
            if (!Page.arrasto) {
                return;
            }

            const dx = event.clientX - Page.arrasto.x;
            const dy = event.clientY - Page.arrasto.y;

            // Só assume o gesto quando ele é claramente horizontal, para não
            // roubar a rolagem vertical da lista.
            if (!Page.arrasto.ativo) {
                if (dx < 10 || Math.abs(dx) <= Math.abs(dy)) {
                    return;
                }

                Page.arrasto.ativo = true;
                $(event.currentTarget).addClass('arrastando');
            }

            $(event.currentTarget).css('transform', `translateX(${Math.max(0, dx)}px)`);
        },

        soltar(event) {
            if (!Page.arrasto) {
                return;
            }

            const { id, x, ativo } = Page.arrasto;
            const $registro = $(event.currentTarget);
            const percorrido = event.clientX - x;

            Page.arrasto = null;
            $registro.removeClass('arrastando');

            if (!ativo || percorrido < ARRASTO_MINIMO) {
                $registro.css('transform', '');
                return;
            }

            // Segura o item deslocado enquanto o diálogo entra; só depois ele
            // desliza de volta, acompanhando o gesto em vez de saltar.
            Page.exclusao.perguntar(id);

            setTimeout(_ => $registro.css('transform', ''), RETORNO_ARRASTO);
        },

        perguntar(id) {
            const movimentacao = (window.movimentacoes ?? []).find(item => item.id === id);

            if (!movimentacao) {
                return;
            }

            const sinal = movimentacao.tipo === 'F' ? '+' : '-';
            const parcelada = movimentacao.flg_credito === 'S' && !movimentacao.movimentacao_original_id;

            Dialog.create({
                titulo: 'Excluir movimentação',
                id: DIALOG_EXCLUIR,
                tipo: 'error',
                submit: 'Excluir',
                body: (
                    '<p>Esta movimentação será removida definitivamente.</p>' +
                    '<div class="resumo">' +
                        `<strong>${Formato.texto(movimentacao.categoria?.descricao ?? 'Sem categoria')}</strong>` +
                        `<span>${sinal} R$ ${Formato.dinheiro(movimentacao.valor)} · ${Formato.dataBR(Formato.dia(movimentacao.data_registro))}</span>` +
                    '</div>' +
                    (parcelada ? '<p class="aviso">É a primeira parcela: as demais também serão excluídas.</p>' : '')
                ),
            });

            $(`#${DIALOG_EXCLUIR}`).data('id', id);
        },

        confirmar(event) {
            event.preventDefault();

            const id = $(event.currentTarget).data('id');

            Dialog.close(DIALOG_EXCLUIR);

            return MovimentacaoApi.excluir(id)
                .then(_ => {
                    Toast.success('Movimentação excluída.');
                    Page.historico.remover(id);
                })
                .catch(_ => {});
        },
    };

    static processamento = {
        interpretar(texto) {
            if (!texto) {
                Toast.warning('Nada foi reconhecido na gravação.');
                return;
            }

            MovimentacaoApi.interpretar(texto)
                .then(movimentacao => Page.processamento.salvar(movimentacao))
                .catch(_ => Page.processamento.encerrar());
        },

        salvar(movimentacao) {
            return MovimentacaoApi.salvar(movimentacao)
                .then(cadastradas => Page.processamento.concluir(cadastradas))
                .catch(_ => Page.processamento.encerrar());
        },

        concluir(cadastradas) {
            console.log('Movimentação cadastrada:', cadastradas);

            Page.historico.adicionar(cadastradas);
            Toast.success('Movimentação cadastrada.');
            Page.processamento.encerrar();
        },

        // O RequestHelper só fecha o Preload no erro; no sucesso ele fica aberto.
        encerrar() {
            Page.methods.status(false, 'Segure o microfone e fale');
            Preload.destroy();
        },
    };
}

$(document).ready(_ => Page.events.init());
