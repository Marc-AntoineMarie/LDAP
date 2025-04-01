<?php 

// Gestion des partenaires
class ShowPartnerForm {

    private $pdo;
    private $PartnerRecoverySQLRequest = "SELECT * FROM Partenaires";
    private $PartnerRecoveryByIdSQLRequest = "SELECT * FROM Partenaires WHERE idpartenaires = [0] ";

    // Constructeur pour initialiser la connexion PDO
    function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Récupération de tous les partenaires
    function PartnerRecovery() {
        $stmt = $this->pdo->prepare($this->PartnerRecoverySQLRequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
     // Récupération d'un partenaire par son id
    function PartnerRecoveryById($idpartenaire) {
				$sqlrequest = str_replace("[0]", $idpartenaire,$this->PartnerRecoveryByIdSQLRequest);
        $stmt = $this->pdo->prepare($sqlrequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Ajouter un partenaire à partir d'un formulaire
    function addPartnerRecovery($nom, $email, $telephone, $adresse) {
        // Préparer la requête SQL
        $sql_Partner = "INSERT INTO Partenaires (Nom, Email, Telephone, Adresse)
                        VALUES (:Nom, :Email, :Telephone, :Adresse)";

        // Préparer la requête avec PDO
        $stmt_Partner = $this->pdo->prepare($sql_Partner);

        // Lier les paramètres aux valeurs provenant du formulaire
        $stmt_Partner->bindParam(":Nom", $nom, PDO::PARAM_STR);
        $stmt_Partner->bindParam(":Email", $email, PDO::PARAM_STR);
        $stmt_Partner->bindParam(":Telephone", $telephone, PDO::PARAM_INT);
        $stmt_Partner->bindParam(":Adresse", $adresse, PDO::PARAM_STR);

        try {
            $result = $stmt_Partner->execute();
            if ($result) {
                return true; // Succès
            } else {
                // Capturer les erreurs SQL
                $errorInfo = $stmt_Partner->errorInfo();
                return "Erreur lors de l'insertion : " . $errorInfo[2];
            }
        } catch (PDOException $e) {
            return "Erreur PDO : " . $e->getMessage();
        }
    }

    // Traitement du formulaire d'ajout de partenaire
    function processPartnerForm($formData) {
        // Validation des données
        $nom = htmlspecialchars($formData['Nom']);
        $email = htmlspecialchars($formData['Email']);
        $telephone = intval(preg_replace('/\D/', '', $formData['Telephone']));
        $adresse = htmlspecialchars($formData['Adresse'] ?? '');

        if (empty($nom) || empty($email) || empty($telephone)) {
            return "Veuillez remplir tous les champs obligatoires.";
        }

        // Ajouter le partenaire
        return $this->addPartnerRecovery($nom, $email, $telephone, $adresse);
        
    }
    
}

// Instance de la class ShowPartnerForm
$PartnerForm = new ShowPartnerForm($pdo);

// Récupération de la liste des partenaires
$Partners = $PartnerForm->PartnerRecovery();

// Vérification et traitement du formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_partner'])) {
    $result = $PartnerForm->processPartnerForm($_POST);

    if ($result === true) {
        echo "Partenaire ajouté avec succès.";
        header("refresh: 2; url=V1_admin.php"); // Redirection après 2 secondes
        exit();
    } else {
        echo $result; // Affiche un message d'erreur ou de validation
    }
}
?>
