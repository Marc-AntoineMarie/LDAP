<?php
/**
 * Formulaire d'ajout/modification de contact
 * 
 * Cette page fournit :
 * - Un formulaire pour ajouter de nouveaux contacts
 * - Un formulaire pour modifier les contacts existants
 * - La validation côté client des données
 * - Une interface utilisateur cohérente avec le reste de l'application
 * 
 * Le formulaire gère :
 * - Les champs obligatoires et optionnels
 * - La validation des formats (téléphone, email)
 * - Les messages d'erreur et de succès
 * - Le retour à l'annuaire après soumission
 */

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'authentification
if (!isset($_SESSION['role'])) {
    header('Location: ../login/login.php');
    exit;
}

// Récupération des informations de session
$role = $_SESSION['role'];
$partnerId = $_SESSION['partner_id'] ?? null;
$clientsId = isset($_GET['idclients']) ? intval($_GET['idclients']) : null;

// Vérification des droits d'accès
if ($role === 'Admin') {
    if (!$clientsId) {
        header('Location: ../admin/V1_admin.php');
        exit;
    }
} elseif ($role === 'Partenaire') {
    if (!$clientsId) {
        header('Location: ../clientlist/clientlist.php');
        exit;
    }
    
    // Vérification de l'appartenance du client au partenaire
    $stmt = $pdo->prepare("SELECT idclients FROM Clients WHERE idclients = ? AND partenaires_idpartenaires = ?");
    $stmt->execute([$clientsId, $_SESSION['partner_id']]);
    if (!$stmt->fetch()) {
        header('Location: ../login/login.php');
        exit;
    }
} elseif ($role === 'Client') {
    if ($clientsId != $_SESSION['client_id']) {
        header('Location: ../login/login.php');
        exit;
    }
}

// Inclusion des dépendances nécessaires pour le formulaire
require_once '../database/db.php';
require_once '../database/Annuaire_request.php';
require_once '../ldap/handlers/ContactHandler.php';

///////////////////// vérif des rôles ///////////////////
///////////////////// FIN vérif des rôles ///////////////////

// Récupérer l'ID du client depuis l'URL
$clientId = isset($_GET['idclients']) ? (int)$_GET['idclients'] : null;

if (!$clientId) {
    die("Erreur : ID client manquant");
}

// Initialisation du gestionnaire d'annuaire
$annuaireManager = new AnnuaireManager($pdo);

// Initialisation du gestionnaire LDAP
$ldapHandler = new ContactHandler();

// Récupération du nom du client
try {
    $clientInfo = $annuaireManager->getClientName($clientId);
    $clientName = !empty($clientInfo[0]['Nom']) ? htmlspecialchars($clientInfo[0]['Nom']) : 'Client inconnu';
} catch (Exception $e) {
    echo "Erreur : " . htmlspecialchars($e->getMessage());
    exit;
}

$error = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) ? $_GET['success'] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Contact - <?= $clientName ?></title>
    <link rel="stylesheet" href="annuaire.css">
</head>
<body class="edit-form">
    <div class="container">
        <div class="content">
            <div class="header-content">
                <h1>Ajouter un Contact - <?= $clientName ?></h1>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="post" action="annuaire.php">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="idclients" value="<?= $clientsId ?>">

                <div class="form-group">
                    <label for="Prenom">Prénom *</label>
                    <input type="text" id="Prenom" name="Prenom" required class="form-control">
                </div>

                <div class="form-group">
                    <label for="Nom">Nom *</label>
                    <input type="text" id="Nom" name="Nom" required class="form-control">
                </div>

                <div class="form-group">
                    <label for="Email">Email</label>
                    <input type="email" id="Email" name="Email" class="form-control">
                </div>

                <div class="form-group">
                    <label for="Societe">Société</label>
                    <input type="text" id="Societe" name="Societe" class="form-control">
                </div>

                <div class="form-group">
                    <label for="Adresse">Adresse</label>
                    <input type="text" id="Adresse" name="Adresse" class="form-control">
                </div>

                <div class="form-group">
                    <label for="Ville">Ville</label>
                    <input type="text" id="Ville" name="Ville" class="form-control">
                </div>

                <div class="form-group">
                    <label for="Telephone">Téléphone *</label>
                    <input type="tel" id="Telephone" name="Telephone" required class="form-control">
                </div>

                <div class="form-group">
                    <label for="Commentaire">Commentaire</label>
                    <textarea id="Commentaire" name="Commentaire" class="form-control"></textarea>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                    <a href="annuaire.php?idclients=<?= $clientsId ?>" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Nom = $_POST['Nom'] ?? '';
    $Prenom = $_POST['Prenom'] ?? '';
    $Telephone = $_POST['Telephone'] ?? '';
    $idclients = $_POST['idclients'] ?? null;
    $idpartenaires = $_POST['idpartenaires'] ?? null;
    
    // Validation des données
    if (!empty($Nom) && !empty($Prenom) && !empty($Telephone) && $idclients && $idpartenaires) {
        try {
            // Ajout dans la base de données MySQL
            $result = addContact($pdo, $Nom, $Prenom, $Telephone, $idclients);
            
            if ($result) {
                // Synchronisation avec LDAP
                $contact = [
                    'nom' => $Nom,
                    'prenom' => $Prenom,
                    'telephone' => $Telephone
                ];
                
                $ldapResult = $ldapHandler->syncContact($idpartenaires, $idclients, $contact);
                
                if ($ldapResult !== false) {
                    $_SESSION['message'] = "Contact ajouté avec succès (MySQL + LDAP)";
                } else {
                    $_SESSION['message'] = "Contact ajouté dans MySQL mais erreur LDAP";
                    error_log("Erreur synchronisation LDAP pour contact: $Nom $Prenom");
                }
            } else {
                $_SESSION['message'] = "Erreur lors de l'ajout du contact";
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Une erreur est survenue";
            error_log("Erreur ajout contact: " . $e->getMessage());
        }
    } else {
        $_SESSION['message'] = "Tous les champs sont obligatoires";
    }
    
    // Redirection vers l'annuaire
    header("Location: annuaire.php?idclients=" . $idclients);
    exit;
}
?>
