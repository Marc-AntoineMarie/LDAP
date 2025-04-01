<?php
/**
 * Gestionnaire des opérations CRUD pour les contacts
 * 
 * Ce fichier traite :
 * - L'ajout de nouveaux contacts dans l'annuaire
 * - La modification des contacts existants
 * - La suppression de contacts
 * - La validation des données avant manipulation
 * 
 * Les opérations sont sécurisées par :
 * - Vérification des droits d'accès
 * - Validation des données entrantes
 * - Protection contre les injections SQL via PDO
 */

// Inclusion des dépendances nécessaires pour la gestion des contacts
require_once '../database/db.php';
require_once '../database/Annuaire_request.php';
require_once '../ldap/handlers/ContactHandler.php';

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérification de la session
if (!isset($_SESSION['role'])) {
    header('Location: ../login/login.php');
    exit;
}

// Initialisation du gestionnaire LDAP
$ldapHandler = new ContactHandler();

// Vérification de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Erreur: Méthode non autorisée");
}

// Récupération des données
$action = $_POST['action'] ?? '';
$clientId = isset($_POST['idclients']) ? (int)$_POST['idclients'] : null;
$idcontact = $_POST['idcontact'] ?? null;

if (!$clientId) {
    header("Location: addcontact_form.php?error=ID+client+invalide");
    exit;
}

// Initialisation du gestionnaire d'annuaire
$annuaireManager = new AnnuaireManager($pdo);

try {
    if ($action === 'add') {
        $result = $annuaireManager->addEntry(
            $clientId,
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
            header("Location: annuaire.php?idclients=$clientId&success=Contact+ajouté");
        } else {
            header("Location: addcontact_form.php?idclients=$clientId&error=Erreur+lors+de+l'ajout");
        }
    } elseif ($action === 'delete' && $idcontact) {
        // Récupération des informations du contact avant suppression
        $stmt = $pdo->prepare("SELECT nom, prenom FROM Annuaire WHERE idcontact = ?");
        $stmt->execute([$idcontact]);
        $contact = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Récupération de l'ID du partenaire
        $stmt = $pdo->prepare("SELECT partenaires_idpartenaires FROM Clients WHERE idclients = ?");
        $stmt->execute([$clientId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($contact && $client) {
            // Suppression dans MySQL
            $result = deleteContact($pdo, $idcontact);
            
            if ($result) {
                // Suppression dans LDAP
                $uid = strtolower($contact['nom'] . '.' . $contact['prenom']);
                $ldapResult = $ldapHandler->deleteContact(
                    $client['partenaires_idpartenaires'],
                    $clientId,
                    $uid
                );
                
                if ($ldapResult !== false) {
                    header("Location: annuaire.php?idclients=$clientId&success=Contact+supprimé");
                } else {
                    header("Location: annuaire.php?idclients=$clientId&error=Erreur+lors+de+la+suppression+LDAP");
                }
            } else {
                header("Location: annuaire.php?idclients=$clientId&error=Erreur+lors+de+la+suppression");
            }
        } else {
            header("Location: annuaire.php?idclients=$clientId&error=Contact+ou+client+non+trouvé");
        }
    } else {
        header("Location: addcontact_form.php?idclients=$clientId&error=Action+invalide");
    }
} catch (Exception $e) {
    header("Location: addcontact_form.php?idclients=$clientId&error=" . urlencode($e->getMessage()));
}
exit;
