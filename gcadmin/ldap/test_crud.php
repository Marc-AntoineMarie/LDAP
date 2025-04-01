<?php
require_once __DIR__ . '/core/LDAPManager.php';

$message = '';
$error = '';
$debug = '';

try {
    $ldapManager = new LDAPManager();

    // Traitement des actions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            switch ($_POST['action']) {
                case 'add':
                    // Générer le LDIF pour l'afficher
                    $ldif = LdifTemplates::generateAddTemplate(
                        $_POST['uid'],
                        $_POST['ou'],
                        $_POST['sn'],
                        $_POST['employeeNumber']
                    );
                    
                    // Tenter l'ajout
                    $result = $ldapManager->addLdapContact(
                        $_POST['uid'],
                        $_POST['ou'],
                        $_POST['sn'],
                        $_POST['employeeNumber']
                    );
                    
                    $message = $result ? "Contact ajouté avec succès !" : "Erreur lors de l'ajout du contact";
                    $debug = "<pre>LDIF généré :\n" . htmlspecialchars($ldif) . "</pre>";
                    break;

                case 'modify':
                    // Générer le LDIF pour l'afficher
                    $ldif = LdifTemplates::generateModifyTemplate(
                        $_POST['uid'],
                        $_POST['ou'],
                        [
                            'sn' => $_POST['new_sn'],
                            'employeeNumber' => $_POST['new_employeeNumber']
                        ]
                    );
                    
                    // Tenter la modification
                    $result = $ldapManager->modifyLdapContact(
                        $_POST['uid'],
                        $_POST['ou'],
                        $_POST['new_sn'],
                        $_POST['new_employeeNumber']
                    );
                    
                    $message = $result ? "Contact modifié avec succès !" : "Erreur lors de la modification du contact";
                    $debug = "<pre>LDIF généré :\n" . htmlspecialchars($ldif) . "</pre>";
                    break;

                case 'delete':
                    // Générer le LDIF pour l'afficher
                    $ldif = LdifTemplates::generateDeleteTemplate(
                        $_POST['uid'],
                        $_POST['ou']
                    );
                    
                    // Tenter la suppression
                    $result = $ldapManager->deleteLdapContact(
                        $_POST['uid'],
                        $_POST['ou']
                    );
                    
                    $message = $result ? "Contact supprimé avec succès !" : "Erreur lors de la suppression du contact";
                    $debug = "<pre>LDIF généré :\n" . htmlspecialchars($ldif) . "</pre>";
                    break;
            }
        } catch (Exception $e) {
            error_log("Erreur LDAP: " . $e->getMessage());
            $error = "Erreur: " . $e->getMessage();
        }
    }
} catch (Exception $e) {
    $error = "Erreur : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test LDAP CRUD</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-section {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }
        .debug {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .command {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <h1>Test des opérations LDAP</h1>

    <?php if ($message): ?>
        <div class="message success">
            <?php echo htmlspecialchars($message); ?>
            <?php if ($debug): ?>
                <div class="debug"><?php echo $debug; ?></div>
                <div class="command">
                    Pour vérifier sur le serveur LDAP :
                    <br>1. <code>ssh root@141.94.251.137 -p 673</code>
                    <br>2. <code>ldapsearch -x -D "cn=admin,dc=test,dc=gcservice,dc=fr" -w votre_mot_de_passe -b "ou=GC-Test,dc=test,dc=gcservice,dc=fr"</code>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Formulaire d'ajout -->
    <div class="form-section">
        <h2>Ajouter un contact</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">
            
            <div class="form-group">
                <label for="uid">UID :</label>
                <input type="text" id="uid" name="uid" required>
            </div>

            <div class="form-group">
                <label for="ou">OU :</label>
                <input type="text" id="ou" name="ou" value="GC-Test" required>
            </div>

            <div class="form-group">
                <label for="sn">Nom (sn) :</label>
                <input type="text" id="sn" name="sn" required>
            </div>

            <div class="form-group">
                <label for="employeeNumber">Numéro de téléphone :</label>
                <input type="text" id="employeeNumber" name="employeeNumber" required>
            </div>

            <input type="submit" value="Ajouter">
        </form>
    </div>

    <!-- Formulaire de modification -->
    <div class="form-section">
        <h2>Modifier un contact</h2>
        <form method="post">
            <input type="hidden" name="action" value="modify">
            
            <div class="form-group">
                <label for="mod_uid">UID du contact à modifier :</label>
                <input type="text" id="mod_uid" name="uid" required>
            </div>

            <div class="form-group">
                <label for="mod_ou">OU :</label>
                <input type="text" id="mod_ou" name="ou" value="GC-Test" required>
            </div>

            <div class="form-group">
                <label for="new_sn">Nouveau nom :</label>
                <input type="text" id="new_sn" name="new_sn" required>
            </div>

            <div class="form-group">
                <label for="new_employeeNumber">Nouveau numéro :</label>
                <input type="text" id="new_employeeNumber" name="new_employeeNumber" required>
            </div>

            <input type="submit" value="Modifier">
        </form>
    </div>

    <!-- Formulaire de suppression -->
    <div class="form-section">
        <h2>Supprimer un contact</h2>
        <form method="post">
            <input type="hidden" name="action" value="delete">
            
            <div class="form-group">
                <label for="del_uid">UID du contact à supprimer :</label>
                <input type="text" id="del_uid" name="uid" required>
            </div>

            <div class="form-group">
                <label for="del_ou">OU :</label>
                <input type="text" id="del_ou" name="ou" value="GC-Test" required>
            </div>

            <input type="submit" value="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contact ?');">
        </form>
    </div>

    <p><a href="test_connection.php">← Retour aux tests de connexion</a></p>
</body>
</html>
