<?php

require_once __DIR__ . '/LDAPManager.php';
require_once __DIR__ . '/../../database/db.php';

class TeloraLDAPSync {
    private $ldapManager;
    private $db;
    private $logger;

    public function __construct() {
        $this->ldapManager = new LDAPManager();
        $this->db = $GLOBALS['pdo'];  
        $this->setupLogger();
    }

    private function setupLogger() {
        $logFile = __DIR__ . '/../logs/telora_sync.log';
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }
        $this->logger = function($message) use ($logFile) {
            $timestamp = date('Y-m-d H:i:s');
            file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
        };
    }

    private function log($message) {
        ($this->logger)($message);
    }

    /**
     * Synchronise un contact depuis TELORA vers LDAP
     */
    public function syncContact($contactId) {
        try {
            $this->log("Début synchronisation contact ID: $contactId");

            // Récupère les données du contact depuis TELORA
            $query = "SELECT ua.*, c.name as client_name 
                     FROM user_annuaire ua 
                     JOIN clients c ON ua.idclients = c.id 
                     WHERE ua.iduser_annuaire = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$contactId]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$contact) {
                throw new Exception("Contact non trouvé dans TELORA: $contactId");
            }

            // Prépare les données pour LDAP
            $uid = $contact['iduser_annuaire'];  // ID comme UID
            $ou = $contact['client_name'];       // Nom du client comme OU
            $sn = $contact['Nom'] . ' ' . $contact['Prenom'];  // Nom complet
            $employeeNumber = $contact['Telephone'];

            // Vérifie si le contact existe déjà dans LDAP
            $exists = $this->ldapManager->checkIfContactExists($uid, $ou);

            if ($exists) {
                // Met à jour le contact existant
                $this->log("Mise à jour du contact LDAP - UID: $uid, OU: $ou");
                $result = $this->ldapManager->modifyLdapContact($uid, $ou, $sn, $employeeNumber);
            } else {
                // Crée un nouveau contact
                $this->log("Création du contact LDAP - UID: $uid, OU: $ou");
                $result = $this->ldapManager->addLdapContact($uid, $ou, $sn, $employeeNumber);
            }

            if (!$result) {
                throw new Exception("Échec de la synchronisation LDAP");
            }

            $this->log("Synchronisation réussie pour le contact $contactId");
            return true;

        } catch (Exception $e) {
            $this->log("ERREUR - Synchronisation contact $contactId : " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Supprime un contact de LDAP quand il est supprimé de TELORA
     */
    public function deleteContact($contactId, $ou) {
        try {
            $this->log("Début suppression contact ID: $contactId");
            
            $result = $this->ldapManager->deleteLdapContact($contactId, $ou);
            
            if (!$result) {
                throw new Exception("Échec de la suppression LDAP");
            }

            $this->log("Suppression réussie pour le contact $contactId");
            return true;

        } catch (Exception $e) {
            $this->log("ERREUR - Suppression contact $contactId : " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Synchronise tous les contacts d'un client
     */
    public function syncClientContacts($clientId) {
        try {
            $this->log("Début synchronisation des contacts du client ID: $clientId");

            // Récupère tous les contacts du client
            $query = "SELECT id FROM contacts WHERE client_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$clientId]);
            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $success = true;
            foreach ($contacts as $contact) {
                try {
                    $this->syncContact($contact['id']);
                } catch (Exception $e) {
                    $this->log("ERREUR - Contact {$contact['id']} : " . $e->getMessage());
                    $success = false;
                }
            }

            return $success;

        } catch (Exception $e) {
            $this->log("ERREUR - Synchronisation client $clientId : " . $e->getMessage());
            throw $e;
        }
    }
}
