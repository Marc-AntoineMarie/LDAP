<?php
require_once '../database/db.php';

// Activer l'affichage des erreurs PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function writeLog($message) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

// Récupérer les partenaires avant le formulaire
try {
    $stmt = $pdo->prepare("SELECT idpartenaires, Nom FROM Partenaires");
    $stmt->execute();
    $partners = $stmt->fetchAll(PDO::FETCH_ASSOC);
    writeLog("Contenu des partenaires récupérés : " . json_encode($partners));
} catch (PDOException $e) {
    writeLog("Erreur lors de la récupération des partenaires : " . $e->getMessage());
    die("Erreur lors de la récupération des partenaires : " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = htmlspecialchars($_POST['login'] ?? '');
    $password = htmlspecialchars($_POST['password'] ?? '');
    $role = htmlspecialchars($_POST['role'] ?? '');
    $partnerId = htmlspecialchars($_POST['partner_id'] ?? null);
    $clientId = htmlspecialchars($_POST['client_id'] ?? null);

    writeLog("Valeurs reçues : " . json_encode($_POST));

    // Vérification de la connexion à la base
    try {
        $pdo->query('SELECT 1');
        writeLog("Connexion à la base de données réussie.");
    } catch (PDOException $e) {
        writeLog("Erreur de connexion à la base de données : " . $e->getMessage());
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }

    if (empty($login) || empty($password) || empty($role)) {
        $error = "Tous les champs doivent être remplis.";
    } else {
        try {
            // Si le rôle est "Client", vérifier la validité de l'ID client
            if ($role === 'Client' && !empty($clientId)) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM Clients WHERE idclients = :clientId");
                $stmt->bindParam(':clientId', $clientId, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->fetchColumn() == 0) {
                    $error = "L'ID du client est invalide.";
                    writeLog("Erreur : L'ID client $clientId n'existe pas.");
                }
            }

            // Si pas d'erreur, insérer les données
            if (empty($error)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Gérer les valeurs NULL pour partenaires_idpartenaires et clients_idclients
                if ($role !== 'Client') {
                    $clientId = null;
                }
                if ($role === 'Admin') {
                    $partnerId = null;
                }

                $stmt = $pdo->prepare("INSERT INTO Roles (Login, MDP, Status, partenaires_idpartenaires, clients_idclients) VALUES (:login, :password, :role, :partnerId, :clientId)");
                $stmt->bindParam(':login', $login, PDO::PARAM_STR);
                $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
                $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                $stmt->bindParam(':partnerId', $partnerId, PDO::PARAM_INT);
                $stmt->bindValue(':clientId', $clientId, PDO::PARAM_INT); // bindValue pour permettre NULL

                $stmt->execute();
                $success = "Utilisateur enregistré avec succès.";
                writeLog("Nouvel utilisateur enregistré : $login avec le rôle $role.");
            }
        } catch (PDOException $e) {
            writeLog("Erreur lors de l'enregistrement : " . $e->getMessage());
            $error = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card shadow" style="width: 400px; padding: 20px;">
            <h1 class="text-center">Inscription</h1>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label for="login" class="form-label">Identifiant :</label>
                    <input type="text" id="login" name="login" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe :</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Rôle :</label>
                    <select id="role" name="role" class="form-select" required onchange="togglePartnerClientFields(this.value)">
                        <option value="">Choisir...</option>
                        <option value="Admin">Admin</option>
                        <option value="Partenaire">Partenaire</option>
                        <option value="Client">Client</option>
                    </select>
                </div>
                <div class="mb-3" id="partnerField" style="display: none;">
                    <label for="partner_id" class="form-label">Sélectionnez un partenaire :</label>
                    <select id="partner_id" name="partner_id" class="form-select" onchange="fetchClients(this.value)">
                        <option value="">Choisir...</option>
                        <?php foreach ($partners as $partner): ?>
                            <option value="<?php echo htmlspecialchars($partner['idpartenaires']); ?>">
                                <?php echo htmlspecialchars($partner['Nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3" id="clientField" style="display: none;">
                    <label for="client_id" class="form-label">Sélectionnez un client :</label>
                    <select id="client_id" name="client_id" class="form-select">
                        <option value="">Choisir...</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100">S'inscrire</button>
            </form>
        </div>
    </div>

    <script>
        function togglePartnerClientFields(role) {
            const partnerField = document.getElementById('partnerField');
            const clientField = document.getElementById('clientField');

            if (role === 'Client') {
                partnerField.style.display = 'block';
                clientField.style.display = 'block';
            } else if (role === 'Partenaire') {
                partnerField.style.display = 'block';
                clientField.style.display = 'none';
            } else {
                partnerField.style.display = 'none';
                clientField.style.display = 'none';
            }
        }

        function fetchClients(partnerId) {
            if (partnerId) {
                fetch(`get_clients.php?partner_id=${partnerId}`)
                    .then(response => response.json())
                    .then(data => {
                        const clientField = document.getElementById('client_id');
                        clientField.innerHTML = '<option value="">Choisir...</option>';
                        if (data.error) {
                            console.error(data.error);
                        } else {
                            data.forEach(client => {
                                const option = document.createElement('option');
                                option.value = client.idclients;
                                option.textContent = client.Nom;
                                clientField.appendChild(option);
                            });
                        }
                    })
                    .catch(error => console.error('Erreur lors de la récupération des clients :', error));
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>