<?php
require_once 'encryption.php';
session_start();

// Vérification du rôle admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login/login.php');
    exit;
}

$encrypted = '';
$decrypted = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        try {
            // Chiffrement
            $encrypted = 'ENC:' . Encryption::encrypt($_POST['password']);
            
            // Test de déchiffrement
            $decrypted = Encryption::decrypt(substr($encrypted, 4));
            
            $message = 'Mot de passe chiffré avec succès !<br>
                Résultat chiffré : ' . htmlspecialchars($encrypted) . '<br>
                Test de déchiffrement : ' . htmlspecialchars($decrypted) . '';
            
            if ($decrypted === $_POST['password']) {
                $message .= '<div style="color: green;">✓ Le test de déchiffrement est réussi !</div>';
            } else {
                $message .= '<div style="color: red;">❌ ERREUR : Le test de déchiffrement a échoué !</div>';
            }
            
        } catch (Exception $e) {
            $message = 'Erreur : ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chiffrement de mot de passe</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="password"], input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .message {
            margin-top: 10px;
            padding: 10px;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .instructions {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Chiffrement de mot de passe pour LDAP</h2>
        
        <form method="POST">
            <div class="form-group">
                <label for="password">Mot de passe à chiffrer :</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Chiffrer</button>
        </form>
        
        <?php if ($message): ?>
        <div class="message success">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($encrypted): ?>
        <div class="result">
            <h3>Résultat du chiffrement :</h3>
            <input type="text" value="<?php echo htmlspecialchars($encrypted); ?>" readonly onclick="this.select();">
        </div>
        
        <div class="instructions">
            <h3>Instructions :</h3>
            <ol>
                <li>Copiez la valeur chiffrée ci-dessus</li>
                <li>Ouvrez le fichier <code>config/.env</code></li>
                <li>Collez la valeur dans le fichier comme ceci :
                    <pre>
LDAP_ADMIN_PASSWORD=<?php echo htmlspecialchars($encrypted); ?>
# ou
LDAP_SSH_PASSWORD=<?php echo htmlspecialchars($encrypted); ?></pre>
                </li>
            </ol>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
