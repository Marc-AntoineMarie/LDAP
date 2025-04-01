<?php
// Activer l'affichage des erreurs pour le debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'core/LDAPManager.php';

session_start();

// Vérification du rôle admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    error_log("Test LDAP - Accès refusé : role=" . ($_SESSION['role'] ?? 'non défini'));
    header('Location: ../login/login.php');
    exit;
}

$status = [
    'ldap' => ['status' => 'Non testé', 'message' => ''],
    'ssh' => ['status' => 'Non testé', 'message' => '']
];

try {
    error_log("Test LDAP - Début du test de connexion");
    
    // Test de la connexion LDAP
    error_log("Test LDAP - Création du LDAPManager");
    $ldapManager = new LDAPManager();
    error_log("Test LDAP - LDAPManager créé avec succès");
    
    $status['ldap'] = ['status' => 'OK', 'message' => 'Connexion LDAP établie avec succès'];
    error_log("Test LDAP - Connexion LDAP OK");
    
    // Test de la création d'un contact test
    $testContact = [
        'uid' => 'test.contact',
        'nom' => 'Test',
        'prenom' => 'Contact',
        'telephone' => '0123456789'
    ];
    
    error_log("Test LDAP - Tentative d'ajout d'un contact test");
    // Tester la création et suppression d'un contact
    $ldapManager->addContact('Test-Client', $testContact);
    error_log("Test LDAP - Contact test ajouté");
    
    $ldapManager->deleteContact('Test-Client', 'test.contact');
    error_log("Test LDAP - Contact test supprimé");
    
    $status['ssh'] = ['status' => 'OK', 'message' => 'Connexion SSH et opérations LDAP testées avec succès'];
    error_log("Test LDAP - Test SSH OK");
    
} catch (Exception $e) {
    error_log("Test LDAP - Erreur : " . $e->getMessage());
    error_log("Test LDAP - Trace : " . $e->getTraceAsString());
    
    if (strpos($e->getMessage(), 'ldap') !== false) {
        $status['ldap'] = ['status' => 'ERREUR', 'message' => $e->getMessage()];
    } else {
        $status['ssh'] = ['status' => 'ERREUR', 'message' => $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test de connexion LDAP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .container {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .status {
            margin: 10px 0;
            padding: 15px;
            border-radius: 4px;
        }
        .status.ok {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .status.error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .status.pending {
            background-color: #fcf8e3;
            color: #8a6d3b;
            border: 1px solid #faebcc;
        }
        .debug {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Test des connexions LDAP et SSH</h2>
        
        <div class="status <?php echo $status['ldap']['status'] === 'OK' ? 'ok' : ($status['ldap']['status'] === 'Non testé' ? 'pending' : 'error'); ?>">
            <h3>Connexion LDAP :</h3>
            <p><?php echo htmlspecialchars($status['ldap']['message']); ?></p>
        </div>
        
        <div class="status <?php echo $status['ssh']['status'] === 'OK' ? 'ok' : ($status['ssh']['status'] === 'Non testé' ? 'pending' : 'error'); ?>">
            <h3>Connexion SSH et opérations LDAP :</h3>
            <p><?php echo htmlspecialchars($status['ssh']['message']); ?></p>
        </div>
        
        <?php if ($status['ldap']['status'] === 'OK' && $status['ssh']['status'] === 'OK'): ?>
        <div class="status ok">
            <h3>✅ Tout est configuré correctement !</h3>
            <p>Vous pouvez maintenant utiliser l'annuaire LDAP.</p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($e)): ?>
        <div class="debug">
            <h3>Informations de débogage :</h3>
            <pre><?php 
                echo "Erreur : " . htmlspecialchars($e->getMessage()) . "\n\n";
                echo "Trace :\n" . htmlspecialchars($e->getTraceAsString());
            ?></pre>
        </div>
        <?php endif; ?>
        
        <p><a href="../annuaire/annuaire.php">Retour à l'annuaire</a></p>
    </div>
</body>
</html>
