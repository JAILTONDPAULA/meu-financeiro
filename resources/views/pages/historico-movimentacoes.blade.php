@extends('layout.app')

@section('title', 'Histórico Movimentações')

@section('head')
    @vite(['resources/js/pages/historico-movimentacoes.js'])
@endsection

@php
    // Sem intl instalado no PHP, o nome do mês vem de um mapa fixo.
    $meses = ['JANEIRO', 'FEVEREIRO', 'MARÇO', 'ABRIL', 'MAIO', 'JUNHO', 'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO'];
    $hoje = now();
@endphp

@section('body')
<x-menu />
<section class="containerpage">
    <section class="titulo">
        <a href="{{ route('movimentacoes') }}" class="icon">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1>Histórico Movimentações</h1>
    </section>
    <section class="adicionar">
        <button type="button" class="microfone">
            <i class="fa-solid fa-microphone"></i>
        </button>
        <span class="status">Segure o microfone e fale</span>
    </section>
    <section class="historico">
        <section class="filtro">
            <div class="tipo">
                <label class="opcao">
                    <input type="radio" name="filtro" value="faturamento" checked>
                    <span>Faturamento</span>
                </label>
                <label class="opcao">
                    <input type="radio" name="filtro" value="data">
                    <span>Data</span>
                </label>
            </div>
            <label class="periodo">
                <input type="month" class="mes" value="{{ $hoje->format('Y-m') }}">
                <span class="rotulo">{{ $meses[$hoje->month - 1] }} de {{ $hoje->year }}</span>
                <i class="fa-solid fa-chevron-down"></i>
            </label>
        </section>

        {{-- Preenchida por Page.historico.renderizar() a partir de window.movimentacoes. --}}
        <section class="registros"></section>
    </section>
</section>
@endsection
