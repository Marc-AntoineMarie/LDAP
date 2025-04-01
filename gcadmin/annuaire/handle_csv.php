<?php
require_once '../database/db.php';
require_once '../database/Annuaire_request.php';
require_once '../utils/functions.php';

session_start();
if (!isset($_SESSION['role'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

$annuaireManager = new AnnuaireManager($pdo);

// Fonction pour nettoyer les données CSV
function cleanData($str) {
    return trim(str_replace(["\r", "\n"], '', $str));
}

// Fonction pour normaliser les en-têtes
function normalizeHeader($header) {
    // Log la valeur originale pour le débogage
    error_log("Normalisation de l'en-tête original : '" . bin2hex($header) . "'");
    
    // Supprime les espaces et caractères spéciaux
    $header = trim($header);
    $header = str_replace(["\r", "\n", "\t"], '', $header);
    // Convertit en minuscules pour la comparaison
    $header = strtolower($header);
    
    // Log la valeur normalisée
    error_log("En-tête normalisé : '" . $header . "'");
    
    return $header;
}

// Gestion de l'import CSV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import_csv') {
    try {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Erreur lors de l'upload du fichier");
        }

        // Log le nom et la taille du fichier
        error_log("Import CSV - Nom du fichier : " . $_FILES['file']['name']);
        error_log("Import CSV - Taille du fichier : " . $_FILES['file']['size'] . " octets");

        // Vérifier le type MIME
        $mimeType = mime_content_type($_FILES['file']['tmp_name']);
        error_log("Import CSV - Type MIME : " . $mimeType);
        
        if (!in_array($mimeType, ['text/csv', 'text/plain', 'application/vnd.ms-excel'])) {
            throw new Exception("Format de fichier non valide. Veuillez utiliser un fichier CSV.");
        }

        // Récupérer l'ID du client depuis l'URL
        $clientId = isset($_GET['idclients']) ? (int)$_GET['idclients'] : null;
        if (!$clientId) {
            throw new Exception("ID client manquant");
        }

        // Ouvrir le fichier
        $handle = fopen($_FILES['file']['tmp_name'], 'r');
        if (!$handle) {
            throw new Exception("Impossible d'ouvrir le fichier");
        }

        // Lire les premiers octets pour détecter l'encodage
        $firstBytes = fread($handle, 4);
        rewind($handle);
        error_log("Import CSV - Premiers octets : " . bin2hex($firstBytes));

        // Détecter et supprimer le BOM UTF-8 si présent
        $hasBOM = false;
        if (substr($firstBytes, 0, 3) === "\xEF\xBB\xBF") {
            $hasBOM = true;
            fseek($handle, 3);
            error_log("Import CSV - BOM UTF-8 détecté et supprimé");
        } else {
            rewind($handle);
            error_log("Import CSV - Pas de BOM UTF-8 détecté");
        }

        // Lire l'en-tête
        $header = fgetcsv($handle, 0, ',');
        if (!$header) {
            throw new Exception("En-tête CSV manquant");
        }

        // Log des en-têtes pour le débogage
        error_log("En-têtes CSV bruts : " . print_r($header, true));
        error_log("Nombre d'en-têtes : " . count($header));
        foreach ($header as $index => $column) {
            error_log("En-tête $index : '" . bin2hex($column) . "'");
        }

        // Normaliser les en-têtes
        $header = array_map('normalizeHeader', $header);
        error_log("En-têtes CSV normalisés : " . implode(',', $header));

        // Définir les en-têtes attendus
        $expectedHeaders = array_map('normalizeHeader', ['Prenom', 'Nom', 'Email', 'Societe', 'Adresse', 'Ville', 'Telephone', 'Commentaire']);
        error_log("En-têtes attendus : " . implode(',', $expectedHeaders));

        // Vérifier que tous les en-têtes attendus sont présents
        $missingHeaders = array_diff($expectedHeaders, $header);
        if (!empty($missingHeaders)) {
            error_log("En-têtes manquants : " . implode(',', $missingHeaders));
            throw new Exception("Colonnes manquantes : " . implode(', ', $missingHeaders));
        }

        // Créer un mapping des colonnes
        $columnMap = array_combine($header, range(0, count($header) - 1));
        error_log("Mapping des colonnes : " . print_r($columnMap, true));

        // Compteurs pour le rapport
        $imported = 0;
        $errors = [];

        // Lire et importer les données
        $lineNumber = 2; // Commence à 2 car la ligne 1 est l'en-tête
        while (($data = fgetcsv($handle, 0, ',')) !== FALSE) {
            try {
                error_log("Lecture ligne $lineNumber : " . implode(',', $data));
                
                if (count($data) !== count($header)) {
                    throw new Exception("Nombre de colonnes incorrect (attendu: " . count($header) . ", reçu: " . count($data) . ")");
                }

                // Créer un tableau associatif des données
                $contact = [];
                foreach ($expectedHeaders as $expectedHeader) {
                    $index = $columnMap[$expectedHeader] ?? null;
                    $value = ($index !== null && isset($data[$index])) ? cleanData($data[$index]) : '';
                    $contact[ucfirst($expectedHeader)] = $value;
                    error_log("Colonne $expectedHeader : '$value'");
                }

                // Vérifier les champs requis
                if (empty($contact['Prenom']) || empty($contact['Nom'])) {
                    throw new Exception("Prénom et Nom sont requis");
                }

                $contactData = [
                    'Prenom' => cleanData($data[$expectedHeaders['prenom']]),
                    'Nom' => cleanData($data[$expectedHeaders['nom']]),
                    'Email' => cleanData($data[$expectedHeaders['email']]),
                    'Societe' => cleanData($data[$expectedHeaders['societe']]),
                    'Adresse' => cleanData($data[$expectedHeaders['adresse']]),
                    'Ville' => cleanData($data[$expectedHeaders['ville']]),
                    'Telephone' => cleanData($data[$expectedHeaders['telephone']]),
                    'Commentaire' => cleanData($data[$expectedHeaders['commentaire']])
                ];

                error_log("Tentative d'ajout du contact : " . print_r($contactData, true));
                
                $result = $annuaireManager->addContact($clientId, $contactData);
                if ($result) {
                    $contactId = $pdo->lastInsertId();
                    error_log("Contact ajouté en BDD avec ID: " . $contactId);

                    // Appel du trigger LDAP
                    require_once __DIR__ . '/../ldap/scripts/sync_triggers.php';
                    error_log("Appel du trigger LDAP pour le contact " . $contactId);
                    
                    try {
                        $triggerResult = LDAPSyncTriggers::afterContactSave($contactId);
                        if (!$triggerResult) {
                            error_log("Échec du trigger LDAP pour le contact " . $contactId);
                            throw new Exception("Erreur lors de la synchronisation LDAP");
                        }
                        error_log("Trigger LDAP exécuté avec succès pour le contact " . $contactId);
                    } catch (Exception $e) {
                        error_log("Exception dans le trigger LDAP: " . $e->getMessage());
                        error_log("Trace: " . $e->getTraceAsString());
                        throw $e;
                    }

                    $imported++;
                } else {
                    throw new Exception("Erreur lors de l'ajout en base de données");
                }
            } catch (Exception $e) {
                $error = "Ligne $lineNumber: " . $e->getMessage();
                $errors[] = $error;
                error_log("Erreur import CSV : " . $error);
            }
            $lineNumber++;
        }

        fclose($handle);

        // Préparer le rapport
        $message = "$imported contacts importés avec succès.";
        if (!empty($errors)) {
            $message .= "\nErreurs:\n" . implode("\n", $errors);
        }

        error_log("Import CSV terminé - Résultat : " . $message);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'errors' => $errors
        ]);

    } catch (Exception $e) {
        error_log("Erreur import CSV : " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Gestion de l'export CSV
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    try {
        // Récupérer l'ID du client
        $clientId = isset($_GET['idclients']) ? (int)$_GET['idclients'] : null;
        if (!$clientId) {
            throw new Exception("ID client manquant");
        }

        // Récupérer les contacts
        $contacts = $annuaireManager->getAnnuaireByClient($clientId);
        
        // Désactiver la mise en cache
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        
        // Préparer l'en-tête du fichier CSV
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="contacts_export.csv"');

        // S'assurer qu'aucune sortie n'a été envoyée avant
        if (ob_get_length()) ob_clean();
        
        // Créer le fichier CSV
        $output = fopen('php://output', 'w');
        if ($output === false) {
            throw new Exception("Impossible de créer le fichier de sortie");
        }

        // Écrire l'en-tête exactement comme dans le fichier qui fonctionne
        fprintf($output, "Prenom,Nom,Email,Societe,Adresse,Ville,Telephone,Commentaire\n");

        // Écrire les données si il y en a
        if (!empty($contacts)) {
            foreach ($contacts as $contact) {
                // Nettoyer et formater les données comme dans le fichier qui fonctionne
                $line = implode(',', [
                    $contact['Prenom'],
                    $contact['Nom'],
                    $contact['Email'],
                    $contact['Societe'],
                    $contact['Adresse'],
                    $contact['Ville'],
                    $contact['Telephone'],
                    $contact['Commentaire']
                ]);
                fprintf($output, "%s\n", $line);
            }
        }

        fclose($output);
        exit;

    } catch (Exception $e) {
        error_log("Erreur export CSV : " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
