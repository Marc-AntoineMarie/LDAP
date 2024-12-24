<?php
require_once '../database/db.php';
include '../database/partner_request.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Partenaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header text-center bg-primary text-white">
                <h3>Ajouter un Partenaire</h3>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <!-- Nom -->
                    <div class="mb-3">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="Nom" placeholder="Entrez le nom" required>
                        <div class="invalid-feedback">Le nom est obligatoire.</div>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="Email" placeholder="Entrez l'email" required>
                        <div class="invalid-feedback">Un email valide est obligatoire.</div>
                    </div>

                    <!-- Téléphone -->
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="telephone" name="Telephone" placeholder="Entrez le numéro de téléphone" required>
                        <div class="invalid-feedback">Le téléphone est obligatoire.</div>
                    </div>

                    <!-- Adresse (facultatif) -->
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea class="form-control" id="adresse" name="Adresse" placeholder="Entrez l'adresse (facultatif)" rows="3"></textarea>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="text-center">
                        <button type="submit" name="add_partner" class="btn btn-success">Ajouter le Partenaire</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
