@extends('layout.app')

@section('title', 'Home')

@section('head')
    @vite(['resources/js/pages/home.js'])
@endsection

@section('body')
<x-menu />
<section class="containerpage">
    <section class="titulo">
        <div class="icon">
            <i class="fa-solid fa-house"></i>
        </div>
        <h1>Home</h1>
    </section>

    <section class="modulos">
        <a href="{{ route('movimentacoes') }}" class="modulo">
            <div class="icon">
                <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <span>Movimentação</span>
        </a>
        <a href="#" class="modulo">
            <div class="icon">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <span>Lista de compras</span>
        </a>
        <a href="#" class="modulo">
            <div class="icon">
                <i class="fa-solid fa-bullseye"></i>
            </div>
            <span>Metas</span>
        </a>
    </section>
</section>
@endsection