<?php
/**
 * Gestionnaire des opérations LDAP pour l'annuaire
 */

require_once '../ldap/handlers/ContactHandler.php';

// Initialisation du gestionnaire LDAP
$ldapHandler = new ContactHandler();

// Récupération des paramètres de la requête
$action = $_POST['action'] ?? '';
$idPartenaire = $_POST['idPartenaire'] ?? null;
$idClient = $_POST['idClient'] ?? null;

// Vérification des paramètres requis
if (!$idPartenaire || !$idClient) {
    echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
    exit;
}

try {
    switch ($action) {
        case 'add':
            // Ajout d'un contact
            $contact = [
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'telephone' => $_POST['telephone']
            ];
            
            $result = $ldapHandler->syncContact($idPartenaire, $idClient, $contact);
            echo json_encode([
                'success' => $result !== false,
                'message' => $result !== false ? 'Contact ajouté avec succès' : 'Erreur lors de l\'ajout'
            ]);
            break;
            
        case 'update':
            // Mise à jour d'un contact
            $contact = [
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'telephone' => $_POST['telephone']
            ];
            
            $result = $ldapHandler->syncContact($idPartenaire, $idClient, $contact);
            echo json_encode([
                'success' => $result !== false,
                'message' => $result !== false ? 'Contact mis à jour avec succès' : 'Erreur lors de la mise à jour'
            ]);
            break;
            
        case 'delete':
            // Suppression d'un contact
            $uid = $_POST['uid'];
            $result = $ldapHandler->deleteContact($idPartenaire, $idClient, $uid);
            echo json_encode([
                'success' => $result !== false,
                'message' => $result !== false ? 'Contact supprimé avec succès' : 'Erreur lors de la suppression'
            ]);
            break;
            
        case 'search':
            // Recherche de contacts
            $search = $_POST['search'] ?? '';
            $contacts = $ldapHandler->getContacts($idPartenaire, $idClient, $search);
            echo json_encode([
                'success' => $contacts !== false,
                'data' => $contacts,
                'message' => $contacts !== false ? 'Contacts récupérés avec succès' : 'Erreur lors de la recherche'
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
    }
} catch (Exception $e) {
    error_log("Erreur LDAP: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Une erreur est survenue']);
}
