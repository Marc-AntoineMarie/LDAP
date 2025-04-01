<?php
include_once '../database/db.php';
include_once '../database/Session.php';

try {
    $roles = $rolesCRUD->Login($Login, $MDP);
    echo "Connexion réussi. Bienvenu, " . $Roles['Status'];
} catch (Exception $e) { 
    echo $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="login.css" rel="stylesheet">
</head>
<body>

<div class="login-container d-flex flex-column flex-lg-row">
    <div class="form-section col-12 col-lg-6">
        <h3>Portail de connexion</h3>
        <form>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" placeholder="Entrer votre Email">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" class="form-control" id="password" placeholder="Entrer votre mot de passe">
            </div>
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Se souvenir de moi</label>
                </div>
                <a href="#" class="link">Mot de passe oublier ?</a>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign in</button>
        </form>
        <p class="text-center mt-3">
            Probleme de connexion ? <a href="#" class="link">Contacter nos administrateurs</a>
        </p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
