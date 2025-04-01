<?php


class AnnuaireManager {

    private $pdo;

    function __construct($pdo){
        $this->pdo = $pdo;
    }
    
// Récupération des entrées de l'annuaire pour un client spécifique
    function getAnnuaireByClient($clientId) {
        try {
            $sql = "SELECT ua.idUserAnnuaire as iduser_annuaire, ua.Prenom, ua.Nom, 
                           ua.Email, ua.Societe, ua.Adresse, ua.Ville, 
                           ua.Telephone, ua.Commentaire
                    FROM User_annuaire ua
                    INNER JOIN Annuaires a ON ua.annuaire_id = a.idAnnuaires
                    WHERE a.clients_idclients = :clientId
                    ORDER BY ua.Nom, ua.Prenom";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des contacts : " . $e->getMessage());
        }
    }

    function addEntry($clientId, $prenom, $nom, $email, $societe, $adresse, $ville, $telephone, $commentaire = '') {
        try {
            // D'abord, récupérer l'ID de l'annuaire existant du client
            $sqlGetAnnuaire = "SELECT idAnnuaires FROM Annuaires WHERE clients_idclients = :clientId LIMIT 1";
            $stmtGetAnnuaire = $this->pdo->prepare($sqlGetAnnuaire);
            $stmtGetAnnuaire->bindParam(":clientId", $clientId, PDO::PARAM_INT);
            $stmtGetAnnuaire->execute();
            
            $annuaire = $stmtGetAnnuaire->fetch(PDO::FETCH_ASSOC);
            $annuaireId = $annuaire ? $annuaire['idAnnuaires'] : null;
            
            // Si aucun annuaire n'existe, en créer un nouveau
            if (!$annuaireId) {
                $sqlAnnuaire = "INSERT INTO Annuaires (clients_idclients, Nom) VALUES (:clientId, 'Annuaire')";
                $stmtAnnuaire = $this->pdo->prepare($sqlAnnuaire);
                $stmtAnnuaire->bindParam(":clientId", $clientId, PDO::PARAM_INT);
                $stmtAnnuaire->execute();
                $annuaireId = $this->pdo->lastInsertId();
            }
            
            // Créer l'entrée utilisateur dans User_annuaire
            $sqlUser = "INSERT INTO User_annuaire (annuaire_id, Prenom, Nom, Email, Societe, Adresse, Ville, Telephone, Commentaire) 
                       VALUES (:annuaireId, :prenom, :nom, :email, :societe, :adresse, :ville, :telephone, :commentaire)";

            $stmtUser = $this->pdo->prepare($sqlUser);
            $stmtUser->bindParam(":annuaireId", $annuaireId, PDO::PARAM_INT);
            $stmtUser->bindParam(":prenom", $prenom, PDO::PARAM_STR);
            $stmtUser->bindParam(":nom", $nom, PDO::PARAM_STR);
            $stmtUser->bindParam(":email", $email, PDO::PARAM_STR);
            $stmtUser->bindParam(":societe", $societe, PDO::PARAM_STR);
            $stmtUser->bindParam(":adresse", $adresse, PDO::PARAM_STR);
            $stmtUser->bindParam(":ville", $ville, PDO::PARAM_STR);
            $stmtUser->bindParam(":telephone", $telephone, PDO::PARAM_STR);
            $stmtUser->bindParam(":commentaire", $commentaire, PDO::PARAM_STR);
            
            return $stmtUser->execute();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de l'ajout du contact : " . $e->getMessage());
        }
    }

    function deleteEntry($entryId) {
        try {
            $this->pdo->beginTransaction();
            
            // First delete from User_annuaire
            $sqlUser = "DELETE FROM User_annuaire WHERE annuaire_id = :entryId";
            $stmtUser = $this->pdo->prepare($sqlUser);
            $stmtUser->bindParam(":entryId", $entryId, PDO::PARAM_INT);
            $stmtUser->execute();
            
            // Then delete from Annuaires
            $sqlAnnuaire = "DELETE FROM Annuaires WHERE idAnnuaires = :entryId";
            $stmtAnnuaire = $this->pdo->prepare($sqlAnnuaire);
            $stmtAnnuaire->bindParam(":entryId", $entryId, PDO::PARAM_INT);
            $stmtAnnuaire->execute();
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            die("deleteEntry -> Erreur lors de la suppression des données : " . $e->getMessage());
        }
    }

    function getClientName($clientId) {
        try{        
            $sql = "SELECT Nom FROM Clients WHERE idclients = :clientId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur SQL dans getClientName : " . $e->getMessage());
        }

    }
    
    function addEntryWithPrenom($clientId, $nom, $prenom, $email, $societe = '', $adresse = '', $telephone = '') {
        try {
            $sql = "INSERT INTO Annuaires (clients_idclients, Nom, Prenom, Email, Societe, Adresse, Telephone) 
                    VALUES (:clientId, :nom, :prenom, :email, :societe, :adresse, :telephone)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
            $stmt->bindParam(":nom", $nom, PDO::PARAM_STR);
            $stmt->bindParam(":prenom", $prenom, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":societe", $societe, PDO::PARAM_STR);
            $stmt->bindParam(":adresse", $adresse, PDO::PARAM_STR);
            $stmt->bindParam(":telephone", $telephone, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Erreur lors de l'ajout : " . $e->getMessage());
        }
    }

    function updateEntry($entryId, $nom, $prenom, $email, $societe = '', $adresse = '', $telephone = '') {
        try {
            $sql = "UPDATE Annuaires SET 
                    Nom = :nom,
                    Prenom = :prenom,
                    Email = :email,
                    Societe = :societe,
                    Adresse = :adresse,
                    Telephone = :telephone
                    WHERE idAnnuaire = :entryId";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":entryId", $entryId, PDO::PARAM_INT);
            $stmt->bindParam(":nom", $nom, PDO::PARAM_STR);
            $stmt->bindParam(":prenom", $prenom, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":societe", $societe, PDO::PARAM_STR);
            $stmt->bindParam(":adresse", $adresse, PDO::PARAM_STR);
            $stmt->bindParam(":telephone", $telephone, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            die("Erreur lors de la mise à jour : " . $e->getMessage());
        }
    }

    function getEntry($entryId) {
        try {
            $sql = "SELECT * FROM Annuaires WHERE idAnnuaire = :entryId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":entryId", $entryId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur lors de la récupération de l'entrée : " . $e->getMessage());
        }
    }

    function getContact($contactId) {
        try {
            $sql = "SELECT ua.idUserAnnuaire as idAnnuaire, ua.Prenom, ua.Nom, 
                           ua.Email, ua.Societe, ua.Adresse, ua.Ville, 
                           ua.Telephone, ua.Commentaire
                    FROM User_annuaire ua
                    WHERE ua.idUserAnnuaire = :contactId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":contactId", $contactId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération du contact : " . $e->getMessage());
        }
    }

    function updateContact($contactId, $prenom, $nom, $email, $societe, $adresse, $ville, $telephone, $commentaire) {
        try {
            $sql = "UPDATE User_annuaire 
                   SET Prenom = :prenom,
                       Nom = :nom,
                       Email = :email,
                       Societe = :societe,
                       Adresse = :adresse,
                       Ville = :ville,
                       Telephone = :telephone,
                       Commentaire = :commentaire
                   WHERE idUserAnnuaire = :contactId";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":contactId", $contactId, PDO::PARAM_INT);
            $stmt->bindParam(":prenom", $prenom, PDO::PARAM_STR);
            $stmt->bindParam(":nom", $nom, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":societe", $societe, PDO::PARAM_STR);
            $stmt->bindParam(":adresse", $adresse, PDO::PARAM_STR);
            $stmt->bindParam(":ville", $ville, PDO::PARAM_STR);
            $stmt->bindParam(":telephone", $telephone, PDO::PARAM_STR);
            $stmt->bindParam(":commentaire", $commentaire, PDO::PARAM_STR);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la modification du contact : " . $e->getMessage());
        }
    }

    function deleteContact($contactId) {
        try {
            $sql = "DELETE FROM User_annuaire WHERE idUserAnnuaire = :contactId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":contactId", $contactId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la suppression du contact : " . $e->getMessage());
        }
    }

////////////////////////////////////////////////////
/////////// Gestion des fichier CSV ////////////////
////////////////////////////////////////////////////
    function importAnnuaireFromCSV($clientId, $filePath) {
        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $sql = "INSERT INTO Annuaires (clients_idclients, Nom, Adresse, Telephone, Email) 
                        VALUES (:clientId, :nom, :adresse, :telephone, :email)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
                $stmt->bindParam(":nom", $data[0], PDO::PARAM_STR);
                $stmt->bindParam(":adresse", $data[1], PDO::PARAM_STR);
                $stmt->bindParam(":telephone", $data[2], PDO::PARAM_STR);
                $stmt->bindParam(":email", $data[3], PDO::PARAM_STR);
                $stmt->execute();
            }
            fclose($handle);
            return true;
        } else {
            return "Erreur lors de l'ouverture du fichier CSV.";
        }
    }


    function exportAnnuaireToCSV($clientId, $filePath) {
        $sql = "SELECT Nom, Adresse, Telephone, Email FROM Annuaires WHERE clients_idclients = :clientId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
        $stmt->execute();
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        $file = fopen($filePath, 'w');
        fputcsv($file, ['Nom', 'Adresse', 'Telephone', 'Email']); // En-tête CSV
    
        foreach ($contacts as $contact) {
            fputcsv($file, $contact);
        }
        fclose($file);
        return true;
    }
}

if (!isset($pdo) || $pdo === null) {
    die("Erreur : PDO non initialisé. Annuaire_request");
}

class UserAnnuaireManager {
    private $pdo;

    function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupérer les utilisateurs d'un annuaire
    function getUsersByAnnuaire($annuaireId) {
        $sql = "SELECT * FROM User_annuaire WHERE annuaire_id = :annuaireId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(":annuaireId", $annuaireId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter un utilisateur à un annuaire
    function addUserToAnnuaire($annuaireId, $prenom, $nom, $email, $societe, $adresse, $ville, $telephone, $commentaire) {
        $sql = "INSERT INTO User_annuaire (annuaire_id, Prenom, Nom, Email, Societe, Adresse, Ville, Telephone, Commentaire) 
                VALUES (:annuaireId, :prenom, :nom, :email, :societe, :adresse, :ville, :telephone, :commentaire)";
        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(":annuaireId", $annuaireId, PDO::PARAM_INT);
        $stmt->bindParam(":prenom", $prenom, PDO::PARAM_STR);
        $stmt->bindParam(":nom", $nom, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":societe", $societe, PDO::PARAM_STR);
        $stmt->bindParam(":adresse", $adresse, PDO::PARAM_STR);
        $stmt->bindParam(":ville", $ville, PDO::PARAM_STR);
        $stmt->bindParam(":telephone", $telephone, PDO::PARAM_STR);
        $stmt->bindParam(":commentaire", $commentaire, PDO::PARAM_STR);

        return $stmt->execute();
    }

    // Supprimer un utilisateur de l'annuaire
    function deleteUserFromAnnuaire($userId) {
        $sql = "DELETE FROM User_annuaire WHERE idUserAnnuaire = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(":userId", $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}

$annuaireManager = new AnnuaireManager($pdo);
