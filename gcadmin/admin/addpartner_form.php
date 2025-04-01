<?php
include '../database/db.php';
include '../database/partner_request.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Partenaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="addpartner_form.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-4">
        <div class="card">
            <div class="custom-header text-center py-3">
                <h3>Ajouter un Partenaire</h3>
            </div>
            <div class="card-body py-4">
                <form method="post" action="">
                    <!-- Nom -->
                    <div class="input-group">
                        <label class="input-group__label" for="nom">Nom <span class="text-danger">*</span></label>
                        <input type="text" id="nom" name="Nom" class="input-group__input" required>
                        <div class="invalid-feedback">Le nom est obligatoire.</div>
                    </div>

                    <!-- Email -->
                    <div class="input-group">
                        <label class="input-group__label" for="email">Email <span class="text-danger">*</span></label>
                        <input type="email" id="email" name="Email" class="input-group__input" required>
                        <div class="invalid-feedback">Un email valide est obligatoire.</div>
                    </div>

                    <!-- Téléphone -->
                    <div class="input-group">
                        <label class="input-group__label" for="telephone">Téléphone <span
                                class="text-danger">*</span></label>
                        <input type="tel" id="telephone" name="Telephone" class="input-group__input" required>
                        <div class="invalid-feedback">Le téléphone est obligatoire.</div>
                    </div>

                    <!-- Adresse -->
                    <div class="input-group">
                        <label class="input-group__label" for="adresse">Adresse</label>
                        <textarea id="adresse" name="Adresse" class="input-group__input" rows="2"></textarea>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="text-center mt-3">
                        <button type="submit" name="add_partner" class="btn btn-gradient">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>