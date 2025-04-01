<?php
require_once '../database/db.php';
include '../database/clients_request.php';

///////////////////// vérif des rôles ///////////////////
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login/login.php');
    exit;
}
///////////////////// FIN vérif des rôles ///////////////////

$clientsHandler = new ClientsHandler($pdo);

// Récupérer l'ID du partenaire depuis la session ou une autre source
$partnerId = $_SESSION['partner_id'] ?? ($_GET['idpartenaires'] ?? null);
if ($partnerId === null) {
    echo "Erreur : aucun partenaire spécifié.";
    exit;
}

$partnerName = $clientsHandler->getPartnerNameById($partnerId);

$error = null;

// Traiter le formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $clientsHandler->processAddClientForm($_POST, $partnerId);
    if ($result === true) {
        header("Location: ../clientlist/clientlist.php?idpartenaires=$partnerId");
        exit;
    } else {
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Script pour changer dynamiquement l'URL en fonction de la plateforme
        function updatePlatformURL() {
            const platform = document.getElementById('plateforme').value;
            const tenant = document.getElementById('tenant');
            const platformURL = document.getElementById('plateforme_url');

            let url = '';
            if (platform === 'Wazo') {
                tenant.style.display = 'block';
                const tenantValue = document.getElementById('tenant_value').value;
                url = tenantValue;
            } else if (platform === 'OVH') {
                tenant.style.display = 'none';
                url = 'fr.proxysip.eu';
            } else if (platform === 'Yeastar') {
                tenant.style.display = 'none';
                url = '192.168.1.150';
            } else {
                tenant.style.display = 'none';
            }

            platformURL.value = url;
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header text-center bg-primary text-white">
                <h3>Ajouter un Client chez <?= htmlspecialchars($partnerName) ?></h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"> <?= htmlspecialchars($error) ?> </div>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="hidden" name="PartnerId" value="<?= htmlspecialchars($partnerId) ?>">
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

                    <!-- Plateforme -->
                    <div class="mb-3">
                        <label for="plateforme" class="form-label">Plateforme <span class="text-danger">*</span></label>
                        <select class="form-select" id="plateforme" name="Plateforme" onchange="updatePlatformURL()" required>
                            <option value="">Choisir une plateforme...</option>
                            <option value="Wazo">Wazo</option>
                            <option value="OVH">OVH</option>
                            <option value="Yeastar">Yeastar</option>
                        </select>
                        <div class="invalid-feedback">La plateforme est obligatoire.</div>
                    </div>

                    <!-- Tenant (Wazo seulement) -->
                    <div class="mb-3" id="tenant" style="display: none;">
                        <label for="tenant_value" class="form-label">Wazo Tenant</label>
                        <select class="form-select" id="tenant_value" name="Tenant" onchange="updatePlatformURL()">
                            <option value="">Choisir un tenant...</option>
                            <?php foreach ($clientsHandler->getPlatforms()['Wazo'] as $name => $url): ?>
                                <option value="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- URL Plateforme (readonly) -->
                    <div class="mb-3">
                        <label for="plateforme_url" class="form-label">URL Plateforme</label>
                        <input type="text" class="form-control" id="plateforme_url" name="PlateformeURL" readonly>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="text-center">
                        <button type="submit" name="add_client" class="btn btn-success">Ajouter le Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
