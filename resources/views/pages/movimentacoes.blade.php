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

    <section class="actions">
        <a href="{{ route('historico-movimentacoes') }}" class="btn btn-primary" id="btn-add-movimentacao">
            <i class="fa-solid fa-plus"></i>
            Adicionar movimentação
        </a>
    </section>

    <section class="valores">
        <h2>Faturamento Atual</h2>
        <div class="valor">
            <i class="fa-solid fa-wallet"></i>
            <strong class="numero">R$ 0,00</strong>
            <span class="titulo">Saldo</span>
        </div>            
        <div class="valor">
            <i class="fa-solid fa-credit-card"></i>
            <strong class="numero">R$ 0,00</strong>
            <span class="titulo">Crédito</span>
        </div>            
        <div class="valor">
            <i class="fa-solid fa-money-bill-wave"></i>
            <strong class="numero">R$ 0,00</strong>
            <span class="titulo">Despesa</span>
        </div>            
    </section>
</section>
@endsection
