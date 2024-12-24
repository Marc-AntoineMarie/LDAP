<?php

//Gestion des clients
class ShowClientForm {

    private $pdo; 
    private $ClientsRecoverySQLRequest = "SELECT * FROM Clients";
    private $ClientsRecoveryByPartenaireSQLRequest = "SELECT * FROM Clients WHERE partenaires_idpartenaires = [0] ";
    private $ClientsRecoveryByIdRequest = "SELECT * FROM Clients WHERE idclients = [0] ";

    //Constructeur pour initialiser la connexion PDO
    function __construct($pdo) {
        $this->pdo = $pdo; 
    }

    //Récupération de tous les clients
    function ClientsRecovery(){

        $stmt = $this->pdo->prepare($this->ClientsRecoverySQLRequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Récupération des clients d'un partenaire
    function ClientsRecoveryByPartenaire($idpartenaire) {
				$sqlrequest = str_replace("[0]", $idpartenaire,$this->ClientsRecoveryByPartenaireSQLRequest);
        $stmt = $this->pdo->prepare($sqlrequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
     // Récupération d'un client par son id
    function ClientsRecoveryById($idclient) {
				$sqlrequest = str_replace("[0]", $idclient,$this->ClientsRecoveryByIdRequest);
        $stmt = $this->pdo->prepare($sqlrequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //Ajouter un client à partir d'un formulaire
    function AddClientRecovery($nom, $email, $telephone, $adresse, $partenaires_idpartenaires) {
        //préparation de la requête SQL
        $sql_clients = "INSERT INTO Clients (Nom, Email, Telephone, Adresse)
                        VALUES (:Nom, :Email, :Telephone, :Adresse, :Partenaires_idpartenaires";

        //préparation de la requete avec PDO
        $stmt_client = $this->pdo->prepare($sql_clients);

        //lier les paramètres aux valeurs provenant du formulaire
        $stmt_client->bindParam(":Nom", $nom, PDO::PARAM_STR);
        $stmt_client->bindParam(":Email", $email, PDO::PARAM_STR);
        $stmt_client->bindParam(":Telephone", $telephone, PDO::PARAM_INT);
        $stmt_client->bindParam(":Adresse", $adresse, PDO::PARAM_STR);
        $stmt_client->bindParam(":Partenares_idpartenaires", $partenaires_idpartenaires, PDO::PARAM_INT);

        try  {
            $result = $stmt_client->execute();
            if($result) {
                return true;
        } else {
            $errorInfo = $stmt_client->errorInfo();
            return "Erreur lors d l'insertion : " . $errorInfo[2];
        }
    } catch (PDOException $e) {
        return "Erreur PDO : ". $e->getMessage();
        }
    }

    //Traitement du formulaire d'ajout clients
    function processClientsForm($formData) {
        //validation des données
        $nom = htmlspecialchars($formData['Nom']);
        $email = htmlspecialchars($formData['Email']);
        $telephone = intval(preg_replace('/\D/', '', $formData['Telephone']));
        $adresse = htmlspecialchars($formData['Adresse']);
        $partenaires_idpartenaires = htmlspecialchars($formData['Partenaire_idpartenaires']);

        if (empty($nom) || empty($email) || empty($telephone) || empty($adresse) || empty($partenaires_idpartenaires)) {
            return "Veuillez remplir tous les champs obligatoires.";
   			}

        //Ajouter le partenaire
        return $this->AddClientRecovery($nom, $email, $telephone, $adresse, $partenaires_idpartenaires);
    }
}

// Instance de la class ShowClientForm
$ClientsForm = new ShowClientForm($pdo);

/*// Récupération de la liste des partenaires
$Clients = $ClientsForm->ClientsRecovery();

// Vérification et traitement du formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_client'])) {
    $result = $ClientsForm->processClientsForm($_POST);

    if ($result === true) {
        echo "Clients ajouté avec succès.";
        header("refresh: 2; url=V1_admin.php"); // Redirection après 2 secondes
        exit();
    } else {
        echo $result; // Affiche un message d'erreur ou de validation
    }
}
*/


?>