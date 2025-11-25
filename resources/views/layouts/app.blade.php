<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestion Notes de Frais')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: 600; }
        .table th { font-weight: 600; background-color: #f8f9fa; }
        .btn-group-sm > .btn { padding: 0.25rem 0.5rem; }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                Notes de Frais
            </a>
            <div class="d-flex align-items-center">
                <span class="navbar-text text-white me-3">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span class="badge bg-secondary ms-2">
                        {{ auth()->user()->role === 'MANAGER' ? 'Manager' : 'Employé' }}
                    </span>
                </span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">
                        Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Contenu -->
    <div class="container-fluid mt-4 mb-5">
        @yield('content')
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>