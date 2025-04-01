<?php

// Gestion des clients
class ShowClientForm {

    private $pdo;
    private $ClientsRecoverySQLRequest = "SELECT * FROM Clients";
    private $ClientsRecoveryByPartenaireSQLRequest = "SELECT * FROM Clients WHERE partenaires_idpartenaires = [0] ";
    private $ClientsRecoveryByIdRequest = "SELECT * FROM Clients WHERE idclients = [0] ";
    private $DeleteClientById =  "DELETE FROM Clients WHERE idclients = [0]";

    // Constructeur pour initialiser la connexion PDO
    function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupération de tous les clients
    function ClientsRecovery() {
        $stmt = $this->pdo->prepare($this->ClientsRecoverySQLRequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupération des clients d'un partenaire
    function ClientsRecoveryByPartenaire($idpartenaire) {
        $sqlrequest = str_replace("[0]", $idpartenaire, $this->ClientsRecoveryByPartenaireSQLRequest);
        $stmt = $this->pdo->prepare($sqlrequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupération d'un client par son id
    function ClientsRecoveryById($idclient) {
        $sqlrequest = str_replace("[0]", $idclient, $this->ClientsRecoveryByIdRequest);
        $stmt = $this->pdo->prepare($sqlrequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ajouter un client à partir d'un formulaire
    function AddClientRecovery($nom, $email, $telephone, $adresse, $partenaires_idpartenaires) {
        // Préparation de la requête SQL
        $sql_clients = "INSERT INTO Clients (Nom, Email, Telephone, Adresse)
                        VALUES (:Nom, :Email, :Telephone, :Adresse, :Partenaires_idpartenaires)";

        // Préparation de la requête avec PDO
        $stmt_client = $this->pdo->prepare($sql_clients);

        // Lier les paramètres aux valeurs provenant du formulaire
        $stmt_client->bindParam(":Nom", $nom, PDO::PARAM_STR);
        $stmt_client->bindParam(":Email", $email, PDO::PARAM_STR);
        $stmt_client->bindParam(":Telephone", $telephone, PDO::PARAM_INT);
        $stmt_client->bindParam(":Adresse", $adresse, PDO::PARAM_STR);
        $stmt_client->bindParam(":Partenaires_idpartenaires", $partenaires_idpartenaires, PDO::PARAM_INT);

        try {
            $result = $stmt_client->execute();
            if ($result) {
                return true;
            } else {
                $errorInfo = $stmt_client->errorInfo();
                return "Erreur lors de l'insertion : " . $errorInfo[2];
            }
        } catch (PDOException $e) {
            return "Erreur PDO : " . $e->getMessage();
        }
    }

    // Traitement du formulaire d'ajout de clients
    function processClientsForm($formData) {
        // Validation des données
        $nom = htmlspecialchars($formData['Nom']);
        $email = htmlspecialchars($formData['Email']);
        $telephone = intval(preg_replace('/\D/', '', $formData['Telephone']));
        $adresse = htmlspecialchars($formData['Adresse']);
        $partenaires_idpartenaires = htmlspecialchars($formData['Partenaire_idpartenaires']);

        if (empty($nom) || empty($email) || empty($telephone) || empty($adresse) || empty($partenaires_idpartenaires)) {
            return "Veuillez remplir tous les champs obligatoires.";
        }

        // Ajouter le partenaire
        return $this->AddClientRecovery($nom, $email, $telephone, $adresse, $partenaires_idpartenaires);
    }

    // Fonction pour supprimer un client
    function deleteClient($idclient) {
        // Utilisation de str_replace pour injecter l'idclient dans la requête
        $sqlrequest = str_replace("[0]", $idclient, $this->DeleteClientById);
        
        try {
            // Préparation de la requête de suppression
            $stmt = $this->pdo->prepare($sqlrequest);

            // Exécution de la suppression
            if ($stmt->execute()) {
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                return "Erreur lors de la suppression : " . $errorInfo[2];
            }
        } catch (PDOException $e) {
            return "Erreur PDO lors de la suppression : " . $e->getMessage();
        }
    }


}

$ClientsForm = new ShowClientForm($pdo);

// Exemple d'utilisation de la méthode deleteClient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_client'])) {
    // Vérification de l'ID du client
    $clientIdToDelete = intval($_POST['delete_id']);
    $deleteResult = $ClientsForm->deleteClient($clientIdToDelete);
    
    if ($deleteResult === true) {
        echo "Client supprimé avec succès.";
        header("Location: clientlist.php"); // Redirige après la suppression
        exit();
    } else {
        echo $deleteResult; // Affiche l'erreur si la suppression échoue
    }
}

?>