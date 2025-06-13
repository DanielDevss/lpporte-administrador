@props([
    'path' => null
])
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">{{ config("app.name") }}</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">

                <li class="nav-item">
                    <a class="nav-link @if(!$path) active @endif" aria-current="page" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer"></i> 
                        Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link @if($path == "orders") active @endif" aria-current="page" href="{{ route('order.home') }}">
                        <i class="bi bi-cash-coin"></i> 
                        Ventas
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link @if(in_array($path, ['brands', 'categories', 'products', 'customers'])) active @endif dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-journal-medical"></i>
                        Catálogos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item @if($path == 'brands') active @endif" href="{{ route('brand.home') }}">Marcas</a></li>
                        <li><a class="dropdown-item @if($path == 'categories') active @endif" href="#">Categorias</a></li>
                        <li><a class="dropdown-item @if($path == 'products') active @endif" href="#">Productos</a></li>
                        <li><a class="dropdown-item" @if($path == 'customers') active @endif href="#">Clientes</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link @if(in_array($path, ['suscriptions', 'settings'])) active @endif dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-gear"></i>
                        Configuraciones
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Suscripciones</a></li>
                        <li><a class="dropdown-item" href="#">Ajustes</a></li>
                    </ul>
                </li>
            </ul>
        </div>

        <div class="nav-item dropdown">
            <a class="nav-link dropdown-toggle @if(in_array($path, ['profile'])) active @endif" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle"></i>
                Mi cuenta
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">Ajustes de cuenta</a></li>
                <li><a class="dropdown-item" href="#">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>