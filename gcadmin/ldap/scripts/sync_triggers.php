<?php
require_once __DIR__ . '/../core/TeloraLDAPSync.php';

class LDAPSyncTriggers {
    
    /**
     * Déclenché après la sauvegarde d'un contact
     * @param int|null $contactId ID du contact. Si null, prend le dernier contact ajouté
     */
    public static function afterContactSave($contactId = null) {
        try {
            // Si pas d'ID fourni, récupère le dernier ID
            if ($contactId === null) {
                require_once __DIR__ . '/../../database/db.php';
                global $pdo;
                
                $sql = "SELECT MAX(idUserAnnuaire) as lastId FROM User_annuaire";
                $stmt = $pdo->query($sql);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $contactId = $result['lastId'];
            }

            if (!$contactId) {
                error_log("sync_triggers.php - Impossible de récupérer l'ID du contact");
                return false;
            }

            error_log("sync_triggers.php - Début afterContactSave avec contactId: " . $contactId);

            // Récupère les données complètes du contact
            $sql = "SELECT ua.*, c.Nom as client_name, p.Nom as partner_name 
                   FROM User_annuaire ua
                   JOIN Annuaires a ON ua.annuaire_id = a.idAnnuaires
                   JOIN Clients c ON a.clients_idclients = c.idclients
                   JOIN Partenaires p ON c.partenaires_idpartenaires = p.idpartenaires
                   WHERE ua.idUserAnnuaire = ?";
            
            global $pdo;
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contactId]);
            $contact = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$contact) {
                error_log("sync_triggers.php - Données du contact non trouvées");
                return false;
            }

            error_log("sync_triggers.php - Données du contact : " . print_r($contact, true));

            // Construction de l'OU
            $ou = $contact['partner_name'] . '-' . $contact['client_name'];
            error_log("sync_triggers.php - OU construit : " . $ou);

            // Initialise le gestionnaire LDAP
            error_log("sync_triggers.php - Initialisation du gestionnaire LDAP");
            $ldapManager = new LDAPManager();
            
            // Vérifie si l'OU existe, sinon le crée
            error_log("sync_triggers.php - Vérification existence OU : $ou");
            if (!$ldapManager->checkIfOUExists($ou)) {
                error_log("sync_triggers.php - L'OU n'existe pas, création");
                $ouData = [
                    'objectClass' => ['organizationalUnit'],
                    'ou' => $ou
                ];
                if (!$ldapManager->createOU($ou, $ouData)) {
                    error_log("sync_triggers.php - Erreur lors de la création de l'OU");
                    return false;
                }
                error_log("sync_triggers.php - OU créé avec succès");
            } else {
                error_log("sync_triggers.php - OU existe déjà");
            }

            // Vérifie si l'entrée existe déjà
            error_log("sync_triggers.php - Vérification existence entrée");
            if ($ldapManager->entryExists($ou, $contact['idUserAnnuaire'])) {
                error_log("sync_triggers.php - L'entrée existe, mise à jour");
                // Pour la mise à jour, on spécifie tous les champs à modifier
                $modifyData = [
                    'sn' => $contact['Nom'],
                    'employeeNumber' => $contact['Telephone'],
                    'cn' => $contact['Prenom'] . ' ' . $contact['Nom']
                ];
                $result = $ldapManager->updateEntry($ou, $contact['idUserAnnuaire'], $modifyData);
                error_log("sync_triggers.php - Mise à jour de l'entrée : " . ($result ? "succès" : "échec"));
            } else {
                error_log("sync_triggers.php - L'entrée n'existe pas, création");
                // Utilise addLdapContact pour la cohérence avec l'import CSV
                $result = $ldapManager->addLdapContact(
                    $contact['idUserAnnuaire'],
                    $ou,
                    $contact['Nom'],
                    $contact['Telephone'],
                    $contact['Prenom']
                );
                error_log("sync_triggers.php - Résultat opération LDAP : " . ($result ? "succès" : "échec"));
            }
            
            return $result;

        } catch (Exception $e) {
            error_log("sync_triggers.php - Erreur : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Déclenché après la suppression d'un contact
     */
    public static function afterContactDelete($contactId) {
        try {
            // Récupère les données du contact depuis la BDD
            require_once __DIR__ . '/../../database/db.php';
            global $pdo;
            
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
                error_log("sync_triggers.php - Données du contact non trouvées pour la suppression");
                return false;
            }

            // Construction de l'OU
            $ou = $contact['partner_name'] . '-' . $contact['client_name'];

            // Initialise le gestionnaire LDAP
            $ldapManager = new LDAPManager();
            
            // Supprime l'entrée LDAP
            return $ldapManager->deleteEntry($ou, $contact['idUserAnnuaire']);

        } catch (Exception $e) {
            error_log("sync_triggers.php - Erreur lors de la suppression : " . $e->getMessage());
            return false;
        }
    }
}
