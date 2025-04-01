<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Chemins absolus
require_once __DIR__ . '/../database/db.php';
require_once __DIR__ . '/../database/Annuaire_request.php';
require_once __DIR__ . '/../ldap/core/LDAPManager.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

function logError($message) {
    error_log("[Import CSV] " . $message);
}

try {
    logError("Début de l'importation");
    logError("POST data: " . print_r($_POST, true));
    logError("FILES data: " . print_r($_FILES, true));

    if (!isset($_POST['idclients'])) {
        throw new Exception("ID client manquant");
    }

    if (!isset($_FILES['csv_file'])) {
        throw new Exception("Fichier CSV manquant");
    }

    $clientId = (int)$_POST['idclients'];
    $file = $_FILES['csv_file'];

    logError("ID Client: " . $clientId);
    logError("Fichier reçu: " . print_r($file, true));

    // Vérification du fichier
    if ($file['error'] !== UPLOAD_ERR_OK) {
        logError("Erreur upload: " . $file['error']);
        throw new Exception("Erreur lors de l'upload: " . $file['error']);
    }

    // Lecture du fichier CSV avec détection automatique de l'encodage
    $content = file_get_contents($file['tmp_name']);
    logError("Contenu du fichier: " . substr($content, 0, 500)); // Log les 500 premiers caractères

    $handle = fopen($file['tmp_name'], 'r');
    if ($handle === false) {
        throw new Exception("Impossible d'ouvrir le fichier");
    }

    // Lecture de l'en-tête
    $header = fgetcsv($handle, 0, ',');
    logError("En-tête détecté: " . print_r($header, true));

    $expectedHeader = ['Prenom', 'Nom', 'Email', 'Societe', 'Adresse', 'Ville', 'Telephone', 'Commentaire'];
    
    if ($header !== $expectedHeader) {
        logError("En-tête invalide. Attendu: " . implode(',', $expectedHeader));
        logError("Reçu: " . implode(',', $header));
        throw new Exception("Format CSV invalide. L'en-tête doit être : " . implode(',', $expectedHeader));
    }

    // Récupération des informations du client pour LDAP
    $stmt = $pdo->prepare("SELECT c.Nom as client_name, p.Nom as partner_name 
                          FROM Clients c 
                          JOIN Partenaires p ON c.partenaires_idpartenaires = p.idpartenaires 
                          WHERE c.idclients = ?");
    $stmt->execute([$clientId]);
    $clientInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$clientInfo) {
        throw new Exception("Client non trouvé");
    }

    $ou = $clientInfo['partner_name'] . '-' . $clientInfo['client_name'];
    logError("OU construit: " . $ou);

    // Initialisation des gestionnaires
    $annuaireManager = new AnnuaireManager($pdo);
    $ldapManager = new LDAPManager();

    // Vérification/création de l'OU
    if (!$ldapManager->checkIfOUExists($ou)) {
        logError("L'OU n'existe pas, création de : " . $ou);
        $ouData = [
            'objectClass' => ['organizationalUnit'],
            'ou' => $ou
        ];
        if (!$ldapManager->createOU($ou, $ouData)) {
            throw new Exception("Erreur lors de la création de l'OU");
        }
    }

    $importedCount = 0;
    $errors = [];

    // Lecture des lignes
    $lineNumber = 2; // Commence à 2 car la ligne 1 est l'en-tête
    while (($data = fgetcsv($handle, 0, ',')) !== false) {
        try {
            logError("Traitement ligne " . $lineNumber . ": " . print_r($data, true));

            if (count($data) !== 8) {
                throw new Exception("Nombre de colonnes incorrect");
            }

            // Nettoyage et validation des données
            $prenom = trim($data[0]);
            $nom = trim($data[1]);
            $email = trim($data[2]);
            $societe = trim($data[3]);
            $adresse = trim($data[4]);
            $ville = trim($data[5]);
            $telephone = trim($data[6]);
            $commentaire = trim($data[7]);

            // Validation basique
            if (empty($prenom) || empty($nom) || empty($telephone)) {
                throw new Exception("Prénom, Nom et Téléphone sont obligatoires");
            }

            // Ajout du contact en BDD
            logError("Tentative d'ajout en BDD avec les données suivantes:");
            logError("- Prénom: " . $prenom);
            logError("- Nom: " . $nom);
            logError("- Email: " . $email);
            logError("- Téléphone: " . $telephone);
            
            $result = $annuaireManager->addEntry(
                $clientId,
                $prenom,
                $nom,
                $email,
                $societe,
                $adresse,
                $ville,
                $telephone,
                $commentaire
            );

            if ($result) {
                $contactId = $pdo->lastInsertId();
                logError("Contact ajouté en BDD avec ID: " . $contactId);

                // Appel explicite du trigger LDAP
                require_once __DIR__ . '/../ldap/scripts/sync_triggers.php';
                logError("Appel du trigger LDAP pour le contact " . $contactId);
                
                try {
                    $triggerResult = LDAPSyncTriggers::afterContactSave($contactId);
                    if (!$triggerResult) {
                        logError("Échec du trigger LDAP");
                        throw new Exception("Erreur lors de la synchronisation LDAP");
                    }
                    logError("Trigger LDAP exécuté avec succès");
                } catch (Exception $e) {
                    logError("Exception dans le trigger LDAP: " . $e->getMessage());
                    logError("Trace: " . $e->getTraceAsString());
                    throw $e;
                }

                $importedCount++;
            } else {
                throw new Exception("Erreur lors de l'ajout en base de données");
            }

        } catch (Exception $e) {
            $errorMsg = "Ligne $lineNumber: " . $e->getMessage();
            logError($errorMsg);
            $errors[] = $errorMsg;
        }
        $lineNumber++;
    }

    fclose($handle);

    $response = [
        'success' => true,
        'imported' => $importedCount,
        'errors' => $errors,
        'message' => "Importation terminée : $importedCount contacts importés" . 
                    (count($errors) > 0 ? " avec " . count($errors) . " erreurs" : "")
    ];

    logError("Réponse: " . print_r($response, true));
    echo json_encode($response);

} catch (Exception $e) {
    $error = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    logError("Erreur finale: " . $e->getMessage());
    echo json_encode($error);
}
