<?php

/**
 * Gestion de l'affichage des clients
 * 
 * Cette classe contient toutes les fonctions de :
 * - Récupération des informations clients
 * - Gestion des relations client-partenaire
 * - Filtrage et validation des données
 * 
 * Points importants :
 * - Utilisation de requêtes préparées pour la sécurité
 * - Gestion des droits d'accès par partenaire
 * - Validation des données entrantes
 */
class ShowClientForm {

    private $pdo; 
    private $ClientsRecoverySQLRequest = "SELECT * FROM Clients";
    private $ClientsRecoveryByPartenaireSQLRequest = "SELECT * FROM Clients WHERE partenaires_idpartenaires = [0] ";
    private $ClientsRecoveryByIdRequest = "SELECT * FROM Clients WHERE idclients = [0] ";
    private $ClientsUpdateRequest = "UPDATE Clients SET Nom = \"[1]\", Email = \"[2]\", Telephone = \"[3]\", 
    		Adresse = \"[4]\", Plateforme = \"[5]\", PlateformeURL = \"[6]\" WHERE idclients = [0] ";
    private $PlateformeRecoverySQLRequest = "SELECT * FROM Plateformes ORDER BY PlateformeNom ASC ";

    /**
     * Constructeur pour initialiser la connexion PDO
     * 
     * @param PDO $pdo Objet PDO de connexion à la base
     */
    function __construct($pdo) {
        $this->pdo = $pdo; 
    }

    /**
     * Récupération de tous les clients
     * 
     * @return array Liste des clients
     * 
     * Cette fonction :
     * - Exécute la requête de récupération des clients
     * - Retourne les résultats sous forme de tableau associatif
     */
    function ClientsRecovery(){

        $stmt = $this->pdo->prepare($this->ClientsRecoverySQLRequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupération des clients d'un partenaire
     * 
     * @param int $idpartenaire ID du partenaire
     * @return array Liste des clients associés au partenaire
     * 
     * Cette fonction :
     * - Filtre les clients par partenaire
     * - Vérifie les droits d'accès
     * - Retourne les informations nécessaires
     */
    function ClientsRecoveryByPartenaire($idpartenaire) {
				$sqlrequest = str_replace("[0]", $idpartenaire,$this->ClientsRecoveryByPartenaireSQLRequest);
        $stmt = $this->pdo->prepare($sqlrequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
     /**
      * Récupération d'un client par son id
      * 
      * @param int $idclient ID du client
      * @return array|false Informations détaillées du client
      * 
      * Cette fonction :
      * - Vérifie l'existence du client
      * - Récupère toutes les informations associées
      * - Gère les erreurs de requête
      */
    function ClientsRecoveryById($idclient) {
				$sqlrequest = str_replace("[0]", $idclient,$this->ClientsRecoveryByIdRequest);
        $stmt = $this->pdo->prepare($sqlrequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Enregistrement des données d'un client
     * 
     * @param int $idclient ID du client
     * @param string $nom Nom du client
     * @param string $email Email du client
     * @param string $tel Téléphone du client
     * @param string $adresse Adresse du client
     * @param string $plateforme Plateforme du client
     * @param string $plateformeurl URL de la plateforme du client
     * @return array|false Informations détaillées du client
     * 
     * Cette fonction :
     * - Valide les données entrantes
     * - Met à jour l'enregistrement en base
     * - Gère les relations avec le partenaire
     */
    function ClientsUpdate($idclient,$nom="",$email="",$tel="",$adresse="",$plateforme="",$plateformeurl="") {
				$sqlrequest = str_replace("[0]", $idclient,$this->ClientsUpdateRequest);
				$sqlrequest = str_replace("[1]", $nom,$sqlrequest);
				$sqlrequest = str_replace("[2]", $email,$sqlrequest);
				$sqlrequest = str_replace("[3]", $tel,$sqlrequest);
				$sqlrequest = str_replace("[4]", $adresse,$sqlrequest);
				$sqlrequest = str_replace("[5]", $plateforme,$sqlrequest);
				$sqlrequest = str_replace("[6]", $plateformeurl,$sqlrequest);
        $stmt = $this->pdo->prepare($sqlrequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupération des plateformes possibles pour affichage
     * 
     * @return array Liste des plateformes
     * 
     * Cette fonction :
     * - Exécute la requête de récupération des plateformes
     * - Retourne les résultats sous forme de tableau associatif
     */
    function PlateformeRecovery(){

        $stmt = $this->pdo->prepare($this->PlateformeRecoverySQLRequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajouter un client à partir d'un formulaire
     * 
     * @param string $nom Nom du client
     * @param string $email Email du client
     * @param string $telephone Téléphone du client
     * @param string $adresse Adresse du client
     * @param int $partenaires_idpartenaires ID du partenaire
     * @return bool|string True si succès, message d'erreur sinon
     * 
     * Cette fonction :
     * - Valide les données entrantes
     * - Crée l'enregistrement en base
     * - Gère les relations avec le partenaire
     */
    function AddClientRecovery($nom, $email, $telephone, $adresse, $partenaires_idpartenaires) {
        //préparation de la requête SQL
        $sql_clients = "INSERT INTO Clients (Nom, Email, Telephone, Adresse, partenaires_idpartenaires)
                        VALUES (:Nom, :Email, :Telephone, :Adresse, :Partenaires_idpartenaires)";

        //préparation de la requete avec PDO
        $stmt_client = $this->pdo->prepare($sql_clients);

        //lier les paramètres aux valeurs provenant du formulaire
        $stmt_client->bindParam(":Nom", $nom, PDO::PARAM_STR);
        $stmt_client->bindParam(":Email", $email, PDO::PARAM_STR);
        $stmt_client->bindParam(":Telephone", $telephone, PDO::PARAM_INT);
        $stmt_client->bindParam(":Adresse", $adresse, PDO::PARAM_STR);
        $stmt_client->bindParam(":Partenaires_idpartenaires", $partenaires_idpartenaires, PDO::PARAM_INT);

        try  {
            $result = $stmt_client->execute();
            if($result) {
                return true;
        } else {
            $errorInfo = $stmt_client->errorInfo();
            return "Erreur lors de l'insertion : " . $errorInfo[2];
        }
    } catch (PDOException $e) {
        return "Erreur PDO : ". $e->getMessage();
        }
    }

    /**
     * Traitement du formulaire d'ajout clients
     * 
     * @param array $formData Données du formulaire
     * @return bool|string True si succès, message d'erreur sinon
     * 
     * Cette fonction :
     * - Valide les données entrantes
     * - Appelle la fonction d'ajout de client
     * - Gère les erreurs de requête
     */
    function processClientsForm($formData) {
        //validation des données
        $nom = htmlspecialchars($formData['Nom']);
        $email = htmlspecialchars($formData['Email']);
        $telephone = intval(preg_replace('/\D/', '', $formData['Telephone']));
        $adresse = htmlspecialchars($formData['Adresse']);
        $partenaires_idpartenaires = isset($formData['Partenaire_idpartenaires']) ? htmlspecialchars($formData['Partenaire_idpartenaires']) : null;

        if (empty($nom) || empty($email) || empty($telephone) || empty($adresse) || empty($partenaires_idpartenaires)) {
            return "Veuillez remplir tous les champs obligatoires.";
   			}

        //Ajouter le partenaire
        return $this->AddClientRecovery($nom, $email, $telephone, $adresse, $partenaires_idpartenaires);
    }
}

// Instance de la class ShowClientForm
$ClientsForm = new ShowClientForm($pdo);

/**
 * Gestion des clients
 * 
 * Cette classe contient toutes les fonctions de :
 * - Récupération des informations clients
 * - Gestion des relations client-partenaire
 * - Filtrage et validation des données
 * 
 * Points importants :
 * - Utilisation de requêtes préparées pour la sécurité
 * - Gestion des droits d'accès par partenaire
 * - Validation des données entrantes
 */
class ClientManager {
    private $pdo;

    /**
     * Constructeur pour initialiser la connexion PDO
     * 
     * @param PDO $pdo Objet PDO de connexion à la base
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère un client par son ID
     * 
     * @param int $clientId ID du client
     * @return array|false Informations détaillées du client
     * 
     * Cette fonction :
     * - Vérifie l'existence du client
     * - Récupère toutes les informations associées
     * - Gère les erreurs de requête
     */
    public function getClientById($clientId) {
        $sql = "SELECT * FROM Clients WHERE idclients = :clientId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['clientId' => $clientId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

$clientManager = new ClientManager($pdo);

/**
 * Gestion des clients
 * 
 * Cette classe contient toutes les fonctions de :
 * - Récupération des informations clients
 * - Gestion des relations client-partenaire
 * - Filtrage et validation des données
 * 
 * Points importants :
 * - Utilisation de requêtes préparées pour la sécurité
 * - Gestion des droits d'accès par partenaire
 * - Validation des données entrantes
 */
class ClientsHandler {

    private $pdo;

    /**
     * Constructeur pour initialiser la connexion PDO
     * 
     * @param PDO $pdo Objet PDO de connexion à la base
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les clients
     * 
     * @return array Liste des clients
     * 
     * Cette fonction :
     * - Exécute la requête de récupération des clients
     * - Retourne les résultats sous forme de tableau associatif
     */
    public function getAllClients() {
        $sql = "SELECT * FROM Clients";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les clients d'un partenaire spécifique
     * 
     * @param int $partnerId ID du partenaire
     * @return array Liste des clients associés au partenaire
     * 
     * Cette fonction :
     * - Filtre les clients par partenaire
     * - Vérifie les droits d'accès
     * - Retourne les informations nécessaires
     */
    public function getClientsByPartner($partnerId) {
        $sql = "SELECT * FROM Clients WHERE partenaires_idpartenaires = :partnerId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':partnerId', $partnerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Ajoute un client
     * 
     * @param string $nom Nom du client
     * @param string $email Email du client
     * @param string $telephone Téléphone du client
     * @param string $adresse Adresse du client
     * @param string $plateforme Plateforme du client
     * @param string $plateformeURL URL de la plateforme du client
     * @param int $partnerId ID du partenaire
     * @return bool|string True si succès, message d'erreur sinon
     * 
     * Cette fonction :
     * - Valide les données entrantes
     * - Crée l'enregistrement en base
     * - Gère les relations avec le partenaire
     */
    public function addClient($nom, $email, $telephone, $adresse, $plateforme, $plateformeURL, $partnerId) {
        try {
            $sql = "INSERT INTO Clients (Nom, Email, Telephone, Adresse, Plateforme, PlateformeURL, partenaires_idpartenaires) 
                    VALUES (:nom, :email, :telephone, :adresse, :plateforme, :plateformeURL, :partnerId)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':telephone', $telephone);
            $stmt->bindParam(':adresse', $adresse);
            $stmt->bindParam(':plateforme', $plateforme);
            $stmt->bindParam(':plateformeURL', $plateformeURL);
            $stmt->bindParam(':partnerId', $partnerId);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return "Erreur lors de l'ajout du client : " . $e->getMessage();
        }
    }

    /**
     * Met à jour un client
     * 
     * @param int $clientId ID du client
     * @param string $nom Nom du client
     * @param string $email Email du client
     * @param string $telephone Téléphone du client
     * @param string $adresse Adresse du client
     * @param string $plateforme Plateforme du client
     * @param string $plateformeURL URL de la plateforme du client
     * @return bool True si succès, false sinon
     * 
     * Cette fonction :
     * - Valide les données entrantes
     * - Met à jour l'enregistrement en base
     * - Gère les relations avec le partenaire
     */
    public function updateClient($clientId, $nom, $email, $telephone, $adresse, $plateforme, $plateformeURL) {
        $sql = "UPDATE Clients 
                SET Nom = :nom, Email = :email, Telephone = :telephone, Adresse = :adresse, Plateforme = :plateforme, PlateformeURL = :plateformeURL 
                WHERE idclients = :clientId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telephone', $telephone);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':plateforme', $plateforme);
        $stmt->bindParam(':plateformeURL', $plateformeURL);
        $stmt->execute();
        return true;
    }

    /**
     * Récupère un client par ID
     * 
     * @param int $clientId ID du client
     * @return array|false Informations détaillées du client
     * 
     * Cette fonction :
     * - Vérifie l'existence du client
     * - Récupère toutes les informations associées
     * - Gère les erreurs de requête
     */
    public function getClientById($clientId) {
        $sql = "SELECT * FROM Clients WHERE idclients = :clientId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les plateformes
     * 
     * @return array Liste des plateformes
     * 
     * Cette fonction :
     * - Retourne les plateformes disponibles
     */
    public function getPlatforms() {
        return [
            'Wazo' => [
                'Aquitaine numérique' => '141.94.251.137',
                'Aquitaine numérique 2' => '141.94.69.47',
                'LDS Solution' => '51.77.245.92',
                'Profibre' => '146.59.153.66',
                'Squartis' => '217.182.68.135'
            ],
            'OVH' => ['OVH URL' => 'fr.proxysip.eu'],
            'Yeastar' => ['Yeastar URL' => '192.168.1.150']
        ];
    }

    /**
     * Traitement du formulaire
     * 
     * @param array $formData Données du formulaire
     * @param int $partnerId ID du partenaire
     * @return bool|string True si succès, message d'erreur sinon
     * 
     * Cette fonction :
     * - Valide les données entrantes
     * - Appelle la fonction d'ajout de client
     * - Gère les erreurs de requête
     */
    public function processAddClientForm($formData, $partnerId) {
        $nom = trim($formData['Nom'] ?? '');
        $email = trim($formData['Email'] ?? '');
        $telephone = trim($formData['Telephone'] ?? '');
        $adresse = trim($formData['Adresse'] ?? '');
        $plateforme = trim($formData['Plateforme'] ?? '');
        $plateformeURL = trim($formData['PlateformeURL'] ?? '');

        if (empty($nom) || empty($email) || empty($telephone) || empty($plateforme)) {
            return "Tous les champs obligatoires doivent être remplis.";
        }

        return $this->addClient($nom, $email, $telephone, $adresse, $plateforme, $plateformeURL, $partnerId);
    }

    /**
     * Récupère le nom du partenaire
     * 
     * @param int $partnerId ID du partenaire
     * @return string Nom du partenaire
     * 
     * Cette fonction :
     * - Vérifie l'existence du partenaire
     * - Récupère le nom du partenaire
     * - Gère les erreurs de requête
     */
    public function getPartnerNameById($partnerId) {
        $sql = "SELECT Nom FROM Partenaires WHERE idpartenaires = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $partnerId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['Nom'] ?? 'Inconnu';
    }

    /**
     * Suppression d'un client par ID
     * 
     * @param int $clientId ID du client
     * @return bool|string True si succès, message d'erreur sinon
     * 
     * Cette fonction :
     * - Vérifie l'existence du client
     * - Supprime l'enregistrement en base
     * - Gère les erreurs de requête
     */
    public function deleteClient($clientId) {
        try {
            // Vérification pour éviter la suppression d'un client non autorisé au cas où on donnerais un accès a un partenaire un jours. (comme ça c'est fais)
            if ($_SESSION['role'] === 'Partenaire') {
                $sql = "SELECT partenaires_idpartenaires FROM Clients WHERE idclients = :clientId";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($result['partenaires_idpartenaires'] != $_SESSION['partner_id']) {
                    return "Vous n'avez pas l'autorisation de supprimer ce client.";
                }
            }
    
            $sql = "DELETE FROM Clients WHERE idclients = :clientId";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            return "Erreur lors de la suppression du client : " . $e->getMessage();
        }
    }
    

    
}

$clientsHandler = new ClientsHandler($pdo);
?>
