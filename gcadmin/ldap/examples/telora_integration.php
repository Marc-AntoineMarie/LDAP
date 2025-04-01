<?php
// Exemple d'intégration dans TELORA

require_once __DIR__ . '/../scripts/sync_triggers.php';

// 1. Dans le code qui gère la sauvegarde d'un contact
function saveContact($data) {
    try {
        // Début de la transaction
        $GLOBALS['db']->beginTransaction();
        
        // Sauvegarde dans la base TELORA
        $query = "INSERT INTO contacts (name, phone, client_id) VALUES (?, ?, ?) 
                 ON DUPLICATE KEY UPDATE name = VALUES(name), phone = VALUES(phone)";
        $stmt = $GLOBALS['db']->prepare($query);
        $stmt->execute([$data['name'], $data['phone'], $data['client_id']]);
        
        $contactId = $stmt->rowCount() > 0 ? $GLOBALS['db']->lastInsertId() : $data['id'];
        
        // Synchronise avec LDAP
        if (!LDAPSyncTriggers::afterContactSave($contactId)) {
            throw new Exception("Erreur de synchronisation LDAP");
        }
        
        // Valide la transaction
        $GLOBALS['db']->commit();
        return true;
        
    } catch (Exception $e) {
        // Annule la transaction en cas d'erreur
        $GLOBALS['db']->rollBack();
        throw $e;
    }
}

// 2. Dans le code qui gère la suppression d'un contact
function deleteContact($contactId) {
    try {
        // Début de la transaction
        $GLOBALS['db']->beginTransaction();
        
        // Supprime d'abord de LDAP
        if (!LDAPSyncTriggers::beforeContactDelete($contactId)) {
            throw new Exception("Erreur de suppression LDAP");
        }
        
        // Puis supprime de la base TELORA
        $query = "DELETE FROM contacts WHERE id = ?";
        $stmt = $GLOBALS['db']->prepare($query);
        $stmt->execute([$contactId]);
        
        // Valide la transaction
        $GLOBALS['db']->commit();
        return true;
        
    } catch (Exception $e) {
        // Annule la transaction en cas d'erreur
        $GLOBALS['db']->rollBack();
        throw $e;
    }
}

// 3. Dans le code qui gère le renommage d'un client
function renameClient($clientId, $newName) {
    try {
        // Début de la transaction
        $GLOBALS['db']->beginTransaction();
        
        // Récupère l'ancien nom
        $query = "SELECT name FROM clients WHERE id = ?";
        $stmt = $GLOBALS['db']->prepare($query);
        $stmt->execute([$clientId]);
        $oldName = $stmt->fetchColumn();
        
        // Met à jour le nom dans TELORA
        $query = "UPDATE clients SET name = ? WHERE id = ?";
        $stmt = $GLOBALS['db']->prepare($query);
        $stmt->execute([$newName, $clientId]);
        
        // Synchronise avec LDAP
        if (!LDAPSyncTriggers::afterClientRename($clientId, $oldName, $newName)) {
            throw new Exception("Erreur de synchronisation LDAP");
        }
        
        // Valide la transaction
        $GLOBALS['db']->commit();
        return true;
        
    } catch (Exception $e) {
        // Annule la transaction en cas d'erreur
        $GLOBALS['db']->rollBack();
        throw $e;
    }
}
