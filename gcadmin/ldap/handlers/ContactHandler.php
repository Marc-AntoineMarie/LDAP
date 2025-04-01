<?php
require_once __DIR__ . '/../core/LDAPManager.php';

/**
 * Gère les opérations sur les contacts LDAP
 */
class ContactHandler {
    private $ldap;
    
    public function __construct() {
        $this->ldap = new LDAPManager();
    }
    
    /**
     * Formate le nom du partenaire-client pour LDAP
     */
    private function formatPartenaireClient($idPartenaire, $idClient) {
        return "Partenaire-{$idPartenaire}-{$idClient}";
    }
    
    /**
     * Ajoute ou met à jour un contact
     */
    public function syncContact($idPartenaire, $idClient, $contact) {
        $partenaireClient = $this->formatPartenaireClient($idPartenaire, $idClient);
        
        // Formater les données du contact
        $contactData = [
            'uid' => strtolower($contact['nom'] . '.' . $contact['prenom']),
            'nom' => $contact['nom'],
            'prenom' => $contact['prenom'],
            'telephone' => $contact['telephone']
        ];
        
        try {
            // Vérifier si le contact existe
            $existing = $this->ldap->searchContacts($partenaireClient, $contactData['uid']);
            
            if ($existing && count($existing) > 0) {
                return $this->ldap->updateContact($partenaireClient, $contactData['uid'], $contactData);
            } else {
                return $this->ldap->addContact($partenaireClient, $contactData);
            }
        } catch (Exception $e) {
            error_log("Erreur LDAP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprime un contact
     */
    public function deleteContact($idPartenaire, $idClient, $uid) {
        try {
            $partenaireClient = $this->formatPartenaireClient($idPartenaire, $idClient);
            return $this->ldap->deleteContact($partenaireClient, $uid);
        } catch (Exception $e) {
            error_log("Erreur LDAP: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère tous les contacts
     */
    public function getContacts($idPartenaire, $idClient, $search = "") {
        try {
            $partenaireClient = $this->formatPartenaireClient($idPartenaire, $idClient);
            return $this->ldap->searchContacts($partenaireClient, $search);
        } catch (Exception $e) {
            error_log("Erreur LDAP: " . $e->getMessage());
            return false;
        }
    }
}
