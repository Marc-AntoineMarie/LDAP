<?php
include_once '../database/db.php';
include_once '../database/login_request.php';
include_once '../database/partner_request.php'; // Assurez-vous d'inclure le fichier pour les partenaires

// Récupération des partenaires
$PartnerForm = new ShowPartnerForm($pdo);
$Partners = $PartnerForm->PartnerRecovery(); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Vérifie si une requête POST est effectuée
    $Login = $_POST['username'];
    $Email = $_POST['email'];
    $MDP = $_POST['password'];
    $status = $_POST['role'];
    $partnerId = $_POST['partner']; // Récupère l'ID du partenaire sélectionné

    // Appelle la méthode pour enregistrer l'utilisateur
    if ($rolesCRUD->register($Login, $Email, $MDP, $status, $partnerId)) {
        $message = "Utilisateur enregistré avec succès";
    } else {
        $message = "Échec de l'enregistrement. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrement Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="register.css" rel="stylesheet">
</head>
<body>
<div class="login-container d-flex flex-column flex-lg-row">
    <div class="form-section col-12 col-lg-6">
        <h3>Enregistrer un compte</h3>
        <!-- Affiche un message de succès ou d'erreur -->
        <?php if (isset($message)): ?>
            <div class="alert <?= strpos($message, 'succès') !== false ? 'alert-success' : 'alert-danger'; ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Nom d'utilisateur <span class="required-star"> *</span></label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Entrer un nom d'utilisateur" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Entrer votre Email">
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Rôle <span class="required-star"> *</span></label>
                <select class="form-select" id="role" name="role" required>
                    <option value="client">Client</option>
                    <option value="partenaire">Partenaire</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <!-- Menu déroulant pour sélectionner un partenaire -->
            <div class="mb-3">
                <label for="partner" class="form-label">Partenaire</label>
                <select class="form-select" id="partner" name="partner" required>
                    <option value="">Sélectionnez un partenaire</option>
                    <?php foreach ($Partners as $partner): ?>
                        <option value="<?= htmlspecialchars($partner['idpartenaires']); ?>">
                            <?= htmlspecialchars($partner['Nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Mot de passe <span class="required-star"> *</span></label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Entrer votre mot de passe" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirmer le mot de passe <span class="required-star"> *</span></label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmer le mot de passe" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">S'enregistrer</button>
        </form>
        <p class="text-center mt-3">
            Déjà un compte ? <a href="../login/login.php" class="link">Se connecter ici</a>
        </p>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
