<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rootPath = dirname(dirname(__FILE__));
require_once $rootPath . '/database/db.php';
require_once $rootPath . '/database/Annuaire_request.php';
require_once $rootPath . '/utils/functions.php';
require_once $rootPath . '/database/ClientManager.php';
require_once $rootPath . '/ldap/scripts/sync_triggers.php';  

///////////////////// vérif des rôles ///////////////////
session_start();
if (!isset($_SESSION['role'])) {
    header('Location: ' . $rootPath . '/login/login.php');
    exit;
}
///////////////////// FIN vérif des rôles ///////////////////

// Récupération des informations de session
$role = $_SESSION['role'];
$partnerId = $_SESSION['partner_id'] ?? null;

// Récupérer les IDs nécessaires
$contactId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$clientId = isset($_GET['idclients']) ? (int)$_GET['idclients'] : null;

// Mettre à jour l'ID du partenaire dans la session
if ($clientId) {
    $clientManager = new ClientManager($pdo);
    $client = $clientManager->getClientById($clientId);
    if ($client) {
        $_SESSION['partner_id'] = $client['idpartenaires'];
    }
}

error_log("Contact ID: " . $contactId);
error_log("Client ID: " . $clientId);

if (!$contactId || !$clientId) {
    die("Paramètres manquants");
}

try {
    // Initialisation du gestionnaire d'annuaire
    if (!isset($pdo)) {
        throw new Exception("Erreur: PDO n'est pas initialisé");
    }
    $annuaireManager = new AnnuaireManager($pdo);

    // Récupérer les informations du contact
    $contact = $annuaireManager->getContact($contactId);
    error_log("Contact data: " . print_r($contact, true));
    
    if (!$contact) {
        throw new Exception("Contact non trouvé");
    }

    // Récupérer le nom du client
    $clientInfo = $annuaireManager->getClientName($clientId);
    error_log("Client info: " . print_r($clientInfo, true));
    
    $clientName = !empty($clientInfo[0]['Nom']) ? e($clientInfo[0]['Nom']) : 'Client inconnu';

    // Traiter le formulaire si soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log("POST data: " . print_r($_POST, true));
        
        $result = $annuaireManager->updateContact(
            $contactId,
            $_POST['prenom'] ?? '',
            $_POST['nom'] ?? '',
            $_POST['email'] ?? '',
            $_POST['societe'] ?? '',
            $_POST['adresse'] ?? '',
            $_POST['ville'] ?? '',
            $_POST['telephone'] ?? '',
            $_POST['commentaire'] ?? ''
        );

        if ($result) {
            // Synchronisation LDAP après la mise à jour
            if (!LDAPSyncTriggers::afterContactSave($contactId)) {
                error_log("Erreur lors de la synchronisation LDAP");
                $error = "Le contact a été modifié mais la synchronisation LDAP a échoué";
            } else {
                header("Location: annuaire.php?idclients=$clientId");
                exit;
            }
        } else {
            $error = "Erreur lors de la modification du contact";
        }
    }
} catch (Exception $e) {
    error_log("Error in editcontact_form.php: " . $e->getMessage());
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un Contact - <?= e($clientName) ?></title>
    <link rel="stylesheet" href="annuaire.css">
</head>
<body class="edit-form">

    <div class="container" >
        <div class="content">
            <div class="header-content">
                <h1>Modifier un Contact de <?= e($clientName) ?></h1>

            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="" class="form">
                <div class="form-group">
                    <label for="prenom">Prénom <span class="required">*</span></label>
                    <input type="text" id="prenom" name="prenom" value="<?= e($contact['Prenom']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="nom">Nom <span class="required">*</span></label>
                    <input type="text" id="nom" name="nom" value="<?= e($contact['Nom']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($contact['Email']) ?>">
                </div>

                <div class="form-group">
                    <label for="societe">Société</label>
                    <input type="text" id="societe" name="societe" value="<?= e($contact['Societe']) ?>">
                </div>

                <div class="form-group">
                    <label for="telephone">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="<?= e($contact['Telephone']) ?>">
                </div>

                <div class="form-group">
                    <label for="adresse">Adresse</label>
                    <input type="text" id="adresse" name="adresse" value="<?= e($contact['Adresse']) ?>">
                </div>

                <div class="form-group">
                    <label for="ville">Ville</label>
                    <input type="text" id="ville" name="ville" value="<?= e($contact['Ville']) ?>">
                </div>

                <div class="form-group">
                    <label for="commentaire">Commentaire</label>
                    <textarea id="commentaire" name="commentaire"><?= e($contact['Commentaire']) ?></textarea>
                </div>

                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="annuaire.php?idclients=<?= $clientId ?>" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
