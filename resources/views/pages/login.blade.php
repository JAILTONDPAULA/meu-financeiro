@extends('layout.app')

@section('title', 'Login')

@section('head')
@vite(['resources/js/pages/login.js'])
@endsection

@section('body')
<section class="content">
    <form id="login-form">
        <div class="header">
            <h1>Meu Financeiro</h1>
            <p>Controle financeiro</p>
        </div>
        <div class="body">
            <div class="input-group">
                <label for="login">e-mail</label>
                <input type="email" name="email" id="login" required>
            </div>
            <div class="input-group button-left">
                <label for="password">senha</label>
                <input type="password" name="password" id="password" required>
                <label class="fa-solid fa-eye" id="view-password"><input type="checkbox"></label>
            </div>
            <button type="submit"><i class="fa-solid fa-arrow-right-to-bracket"></i> LOGIN</button>
        </div>
        <div class="footer">
            <small>&copy; Traumfabrik {{ date('Y') }}</small>
        </div>
    </form>
</section>
@endsection
