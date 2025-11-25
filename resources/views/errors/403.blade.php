<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Accès refusé</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-6 text-center">
                <h1 class="display-1 text-danger">403</h1>
                <h2 class="mb-4">Accès refusé</h2>
                <p class="lead text-muted mb-4">
                    Vous n'avez pas l'autorisation d'accéder à cette page.
                </p>
                <div class="d-flex gap-2 justify-content-center">
                    @auth
                        @if(auth()->user()->isManager())
                            <a href="/manager/dashboard" class="btn btn-primary">Retour au dashboard</a>
                        @else
                            <a href="/employee/dashboard" class="btn btn-primary">Retour au dashboard</a>
                        @endif
                    @else
                        <a href="/login" class="btn btn-primary">Se connecter</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</body>
</html>
