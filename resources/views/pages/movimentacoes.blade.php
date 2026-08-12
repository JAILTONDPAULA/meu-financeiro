@extends('layout.app')

@section('title', 'Movimentações')

@section('head')
    @vite(['resources/js/pages/movimentacoes.js'])
@endsection

@section('body')
<x-menu />
<section class="containerpage">
    <section class="titulo">
        <a href="{{ route('home') }}" class="icon">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1>Movimentações</h1>
    </section>

    <nav class="tabs">
        <button type="button" class="is-active" data-tab="geral">Geral</button>
        <button type="button" data-tab="faturado">Faturado</button>
        <button type="button" data-tab="despesas">Despesas</button>
    </nav>

    <section class="saldos">
        <div class="saldo">
            <div class="icon">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="info">
                <span>Saldo do dia</span>
                <strong>R$ 128,40</strong>
            </div>
        </div>
        <div class="saldo">
            <div class="icon">
                <i class="fa-solid fa-sack-dollar"></i>
            </div>
            <div class="info">
                <span>Saldo do mês</span>
                <strong>R$ 3.420,75</strong>
            </div>
        </div>
        <div class="saldo">
            <div class="icon">
                <i class="fa-solid fa-credit-card"></i>
            </div>
            <div class="info">
                <span>Saldo do mês no crédito</span>
                <strong>R$ 1.150,00</strong>
            </div>
        </div>
    </section>

    <section class="categorias">
        <div class="header">
            <div class="total">
                <span>Total gasto no mês</span>
                <strong>R$ 3.800,00</strong>
            </div>
            <div class="filtros">
                <select class="filtro-mes">
                    <option>Agosto 2026</option>
                    <option>Julho 2026</option>
                    <option>Junho 2026</option>
                </select>
                <div class="filtro-data">
                    <button type="button" class="is-active" data-data="despesa">Data da despesa</button>
                    <button type="button" data-data="faturamento">Data do faturamento</button>
                </div>
            </div>
        </div>

        <div class="lista">
            <div class="categoria" style="--cor: var(--blue-500)">
                <div class="icon">
                    <i class="fa-solid fa-utensils"></i>
                </div>
                <div class="dados">
                    <div class="linha">
                        <span class="nome">Alimentação</span>
                        <span class="valor">R$ 1.240,00 · 32,6%</span>
                    </div>
                    <div class="barra">
                        <div class="preenchido" style="width: 32.6%"></div>
                    </div>
                </div>
            </div>
            <div class="categoria" style="--cor: var(--teal-500)">
                <div class="icon">
                    <i class="fa-solid fa-house-chimney"></i>
                </div>
                <div class="dados">
                    <div class="linha">
                        <span class="nome">Moradia</span>
                        <span class="valor">R$ 980,00 · 25,8%</span>
                    </div>
                    <div class="barra">
                        <div class="preenchido" style="width: 25.8%"></div>
                    </div>
                </div>
            </div>
            <div class="categoria" style="--cor: var(--orange-500)">
                <div class="icon">
                    <i class="fa-solid fa-car"></i>
                </div>
                <div class="dados">
                    <div class="linha">
                        <span class="nome">Transporte</span>
                        <span class="valor">R$ 620,00 · 16,3%</span>
                    </div>
                    <div class="barra">
                        <div class="preenchido" style="width: 16.3%"></div>
                    </div>
                </div>
            </div>
            <div class="categoria" style="--cor: var(--red-500)">
                <div class="icon">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div class="dados">
                    <div class="linha">
                        <span class="nome">Saúde</span>
                        <span class="valor">R$ 410,00 · 10,8%</span>
                    </div>
                    <div class="barra">
                        <div class="preenchido" style="width: 10.8%"></div>
                    </div>
                </div>
            </div>
            <div class="categoria" style="--cor: var(--purple-500)">
                <div class="icon">
                    <i class="fa-solid fa-gamepad"></i>
                </div>
                <div class="dados">
                    <div class="linha">
                        <span class="nome">Lazer</span>
                        <span class="valor">R$ 340,00 · 8,9%</span>
                    </div>
                    <div class="barra">
                        <div class="preenchido" style="width: 8.9%"></div>
                    </div>
                </div>
            </div>
            <div class="categoria" style="--cor: var(--green-500)">
                <div class="icon">
                    <i class="fa-solid fa-ellipsis"></i>
                </div>
                <div class="dados">
                    <div class="linha">
                        <span class="nome">Outros</span>
                        <span class="valor">R$ 210,00 · 5,5%</span>
                    </div>
                    <div class="barra">
                        <div class="preenchido" style="width: 5.5%"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</section>
@endsection
