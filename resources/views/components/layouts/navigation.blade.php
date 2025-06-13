<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">{{ config("app.name") }}</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">

                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#"><i class="bi bi-speedometer"></i> Dasboard</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="#"><i class="bi bi-cash-coin"></i> Ventas</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="bi bi-journal-medical"></i>
                        Catálogos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Marcas</a></li>
                        <li><a class="dropdown-item" href="#">Categorias</a></li>
                        <li><a class="dropdown-item" href="#">Productos</a></li>
                        <li><a class="dropdown-item" href="#">Clientes</a></li>
                    </ul>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
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
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                Mi cuenta
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#">Ajustes de cuenta</a></li>
                <li><a class="dropdown-item" href="#">Cerrar sesión</a></li>
            </ul>
        </div>
    </div>
</nav>