<?php


class AnnuaireManager {

    private $pdo;

    function __construct($pdo){
        $this->pdo = $pdo;
    }
    
// Récupération des entrées de l'annuaire pour un client spécifique
    function getAnnuaireByClient($clientId) {
        try {
            $sql = "SELECT * FROM Annuaires WHERE clients_idclients = :clientId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("getAnnuaireByCLient -> Erreur lors de la récupération des données |  : " . $e->getMessage());
        }
    }


    function addEntry($clientId, $nom, $adresse, $telephone, $email) {
        $sql = "INSERT INTO Annuaires (clients_idclients, Nom, Adresse, Telephone, Email) 
                VALUES (:clientId, :nom, :adresse, :telephone, :email)";

        $stmt = $this->pdo->prepare($sql);

        $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
        $stmt->bindParam(":nom", $nom, PDO::PARAM_STR);
        $stmt->bindParam(":adresse", $adresse, PDO::PARAM_STR);
        $stmt->bindParam(":telephone", $telephone, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        return $stmt->execute();
    }

    function deleteEntry($entryId) {
        $sql = "DELETE FROM Annuaires WHERE idAnnuaire = :entryId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(":entryId", $entryId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    function getClientName($clientId) {
        $sql = "SELECT Nom FROM clients WHERE idclients = :clientId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $sql = "SELECT Nom, Adresse, Telephone, Email FROM Annuaire WHERE clients_idclients = :clientId";
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

$annuaireManager = new AnnuaireManager($pdo);
$contacts = $annuaireManager->getAnnuaireByClient($clientsId);
