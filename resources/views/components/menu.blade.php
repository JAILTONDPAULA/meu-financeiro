<header class="menu">
    <div class="left">
        <button type="button" id="menu-toggle" title="Menu">
            <i class="fa-solid fa-bars"></i>
        </button>
        <a href="/" class="brand">
            <span class="a">Meu</span>
            <span class="b">Financeiro</span>
        </a>
    </div>
    <div class="right">
        <div class="user">
            <strong>{{ auth()->user()->name }}</strong>
            <span>{{ auth()->user()->email }}</span>
        </div>
    </div>
</header>

<div id="sidebar-overlay"></div>

<aside id="sidebar">
    <div class="header">
        <button type="button" id="sidebar-close" title="Fechar">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="user">
            <i class="fa-solid fa-circle-user"></i>
            <strong>{{ auth()->user()->name }}</strong>
            <span>{{ auth()->user()->email }}</span>
        </div>
    </div>
    <nav class="body">
        <a href="{{ route('home') }}"><i class="fa-solid fa-money-bill-transfer"></i> Movimentação</a>
        <a href="#"><i class="fa-solid fa-cart-shopping"></i> Lista de compras</a>
        <a href="#"><i class="fa-solid fa-bullseye"></i> Metas</a>
        <button type="button" id="btn-logout" class="logout">
            <i class="fa-solid fa-right-from-bracket"></i> Sair
        </button>
    </nav>
    <div class="footer">
        <strong>Jailton DPaula</strong>
        <small>criador do software</small>
    </div>
</aside>
