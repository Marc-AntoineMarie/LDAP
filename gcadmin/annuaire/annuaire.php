<?php
/**
 * Page de gestion de l'annuaire d'un client
 */

// Activation des logs d'erreur
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Includes de base
require_once '../database/db.php';
require_once '../utils/functions.php';
require_once '../utils/encryption.php';

// Includes spécifiques à l'annuaire
require_once '../database/Annuaire_request.php';
require_once '../database/clients_request.php';
require_once '../database/utilisateurs_request.php';

// Configuration et classes LDAP
require_once __DIR__ . '/../ldap/config/ldap_config.php';
require_once __DIR__ . '/../ldap/core/LDAPManager.php';
require_once __DIR__ . '/../ldap/core/TeloraLDAPSync.php';
require_once __DIR__ . '/../ldap/scripts/sync_triggers.php';

// Log pour le debug
error_log("annuaire.php - Tous les fichiers requis ont été chargés");

// Vérification de l'authentification
if (!isset($_SESSION['role'])) {
    error_log("annuaire.php - Pas de session active, redirection vers login");
    header('Location: ../login/login.php');
    exit;
}

// Récupération des informations de session
$role = $_SESSION['role'];
$partnerId = $_SESSION['partner_id'] ?? null;

// Récupération de l'ID client
// Priorité à l'ID du formulaire POST, sinon prendre celui de l'URL
$clientsId = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idclients'])) {
    $clientsId = intval($_POST['idclients']);
} elseif (isset($_GET['idclients'])) {
    $clientsId = intval($_GET['idclients']);
}

// Si on n'a pas d'ID client, on redirige selon le rôle
if (!$clientsId) {
    if ($role === 'Admin') {
        header('Location: ../admin/V1_admin.php');
    } elseif ($role === 'Partenaire') {
        header('Location: ../clientlist/clientlist.php');
    } else {
        header('Location: ../login/login.php');
    }
    exit;
}

// Mise à jour du contexte partenaire si on a un ID client
$stmt = $pdo->prepare("SELECT partenaires_idpartenaires FROM Clients WHERE idclients = ?");
$stmt->execute([$clientsId]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if ($client) {
    $partnerId = $client['partenaires_idpartenaires'];
    $_SESSION['partner_id'] = $partnerId;
    $_SESSION['client_id'] = $clientsId;  // Important pour le contexte
    error_log("annuaire.php - Contexte mis à jour - PartnerId: $partnerId, ClientId: $clientsId");
} else {
    error_log("annuaire.php - Client non trouvé");
    header('Location: ../login/login.php');
    exit;
}

// Vérification des droits d'accès selon le rôle
if ($role === 'Admin') {
    // L'admin a accès à tout
} elseif ($role === 'Partenaire') {
    // Vérification de l'appartenance du client au partenaire
    if ($client['partenaires_idpartenaires'] != $_SESSION['partner_id']) {
        error_log("annuaire.php - Accès refusé pour le partenaire");
        header('Location: ../login/login.php');
        exit;
    }
} elseif ($role === 'Client') {
    if ($clientsId != $_SESSION['client_id']) {
        error_log("annuaire.php - Accès refusé pour le client");
        header('Location: ../login/login.php');
        exit;
    }
}

// Initialiser l'objet AnnuaireManager
$annuaireManager = new AnnuaireManager($pdo);

// Variables pour les messages
$message = '';
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'add':
                // Récupère le nom du partenaire et du client pour l'OU
                $stmt = $pdo->prepare("SELECT p.Nom as partner_name, c.Nom as client_name 
                                     FROM Clients c 
                                     JOIN Partenaires p ON c.partenaires_idpartenaires = p.idpartenaires 
                                     WHERE c.idclients = ?");
                $stmt->execute([$clientsId]);
                $info = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$info) {
                    throw new Exception("Impossible de trouver le partenaire et le client associés");
                }
                
                // Construit le nom de l'OU au format "Partenaire-Client"
                $ou = $info['partner_name'] . '-' . $info['client_name'];
                error_log("annuaire.php - OU construit : $ou");
                
                // Ajoute le contact dans la base via AnnuaireManager
                $result = $annuaireManager->addEntry(
                    $clientsId,
                    $_POST['Prenom'],
                    $_POST['Nom'],
                    $_POST['Email'] ?? '',
                    $_POST['Societe'] ?? '',
                    '', // adresse
                    '', // ville
                    $_POST['Telephone'],
                    $_POST['Commentaire'] ?? ''
                );
                
                if ($result) {
                    // Récupère l'ID du contact inséré
                    $contactId = $pdo->lastInsertId();
                    error_log("annuaire.php - Contact ajouté en BDD avec ID: $contactId");
                    
                    // Synchronise avec LDAP
                    if (!LDAPSyncTriggers::afterContactSave($contactId)) {
                        throw new Exception("Erreur de synchronisation LDAP lors de l'ajout");
                    }
                    
                    $message = "Contact ajouté avec succès";
                    // Redirection avec l'ID client
                    header("Location: annuaire.php?idclients=" . $clientsId);
                    exit;
                } else {
                    throw new Exception("Erreur lors de l'ajout du contact");
                }
                break;
        }
    } catch (Exception $e) {
        $error = "Erreur : " . $e->getMessage();
        error_log("Erreur annuaire.php : " . $e->getMessage());
    }
}

// Suppression d'un contact
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $contactId = (int)$_GET['id'];
    
    try {
        // Récupère les informations du contact avant la suppression
        $sql = "SELECT ua.*, c.Nom as client_name, p.Nom as partner_name 
               FROM User_annuaire ua
               JOIN Annuaires a ON ua.annuaire_id = a.idAnnuaires
               JOIN Clients c ON a.clients_idclients = c.idclients
               JOIN Partenaires p ON c.partenaires_idpartenaires = p.idpartenaires
               WHERE ua.idUserAnnuaire = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$contactId]);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$contact) {
            throw new Exception("Contact non trouvé");
        }
        
        // Construction de l'OU
        $ou = $contact['partner_name'] . '-' . $contact['client_name'];
        
        // Initialise le gestionnaire LDAP
        $ldapManager = new LDAPManager();
        
        // Supprime d'abord dans LDAP
        if (!$ldapManager->deleteEntry($ou, $contactId)) {
            throw new Exception("Erreur lors de la suppression LDAP");
        }
        
        // Puis supprime dans la base de données
        if (!$annuaireManager->deleteContact($contactId)) {
            throw new Exception("Erreur lors de la suppression en base de données");
        }
        
        header("Location: annuaire.php?idclients=" . $clientsId);
        exit;
        
    } catch (Exception $e) {
        error_log("Erreur lors de la suppression : " . $e->getMessage());
        $error = "Erreur lors de la suppression : " . $e->getMessage();
    }
}

// Récupérer les informations du client
$clientInfo = null;
if ($clientsId) {
    $stmt = $pdo->prepare("SELECT Nom FROM Clients WHERE idclients = ?");
    $stmt->execute([$clientsId]);
    $clientInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Initialiser l'objet ClientsForm
$ClientsForm = new ShowClientForm($pdo);

// Pour la barre latérale
$idclient = $clientsId;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Annuaire - <?= $clientInfo ? htmlspecialchars($clientInfo['Nom']) : 'Client' ?></title>
    <link rel="stylesheet" href="annuaire.css">
</head>
<body>
    <!-- Barre latérale -->
    <?php include '../partials/barreclient.php'; ?>

    <!-- Contenu principal -->
    <main class="main-content">
        <?php include '../partials/header.php'; ?>
        
        <div class="content">
            <div class="header-content">
                <h1>Annuaire des contacts</h1>
                <?php if ($clientInfo): ?>
                <div class="client-info">
                    <h2><?= htmlspecialchars($clientInfo['Nom']) ?></h2>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($clientsId): ?>
            <div class="action-buttons">
                <button id="import-csv-btn" class="btn btn-primary">Importer CSV</button>
                <button id="export-csv-btn" class="btn btn-secondary">Exporter CSV</button>
                <a href="addcontact_form.php?idclients=<?= $clientsId ?>" class="btn btn-primary">Ajouter un contact</a>
            </div>

            <table class="table">
                <thead>
                    <tr>
                        <th class="text-center">Prénom</th>
                        <th class="text-center">Nom</th>
                        <th class="text-center">Email</th>
                        <th class="text-center">Société</th>
                        <th class="text-center">Téléphone</th>
                        <th class="text-center">Commentaire</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $contacts = $annuaireManager->getAnnuaireByClient($clientsId);
                    foreach ($contacts as $contact):
                    ?>
                    <tr>
                        <td class="text-center"><?= htmlspecialchars($contact['Prenom']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($contact['Nom']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($contact['Email']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($contact['Societe']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($contact['Telephone']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($contact['Commentaire']) ?></td>
                        <td class="text-center">
                            <a href="editcontact_form.php?id=<?= $contact['iduser_annuaire'] ?>&idclients=<?= $clientsId ?>" class="btn btn-primary">Modifier</a>
                            <a href="annuaire.php?action=delete&id=<?= $contact['iduser_annuaire'] ?>&idclients=<?= $clientsId ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contact ?')">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="alert alert-info">
                Veuillez sélectionner un client pour voir son annuaire.
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="annuaire.js"></script>
</body>
</html>
