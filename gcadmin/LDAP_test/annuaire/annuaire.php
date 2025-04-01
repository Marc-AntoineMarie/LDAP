<?php
require_once("../database/db.php");
include("../database/Annuaire_request.php");

if (!isset($pdo)) {
    die("Erreur : La connexion PDO n'est toujours pas initialisée.");
}else{
    //Je commente outrageusement ton caca
    //echo 'annuaire.php -> ok caca | ';
}


try {
    $annuaireManager = new AnnuaireManager($pdo);
    $clientsId = intval($_GET['idclients']);
    $contacts = $annuaireManager->getAnnuaireByClient($clientsId);

    if (empty($contacts)) {
        echo "Aucun contact trouvé pour le client ID : $clientsId.";
    } else {
        echo "<pre>";
        print_r($contacts);
        echo "</pre>";
    }
} catch (Exception $e) {
    die("Erreur lors de la récupération des contacts : " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Annuaires</title>
    <link rel="stylesheet" href="annuaire.css">
</head>
<body>

    <!-- Barre latérale -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../logo/Logo-ldap.png" alt="Logo" class="logo-sidebar">

            <?php
            // Récupération de l'id client pour afficher le bon nom
            if (isset($_GET['idclients'])) {
                $clientsId = intval($_GET['idclients']);
                $annuaireManager = new AnnuaireManager($pdo);
                $clientName = null;

                // Récupération du nom du client
                $clients = $annuaireManager->getClientName($clientsId);
                if (!empty($clients)) {
                    $clientName = htmlspecialchars($clients[0]['Nom']);
                    echo '<p>' . $clientName . '</p>';
                } else {
                    echo '<p>Client non trouvé.</p>';
                }
            } else {
                echo "<p>Aucun identifiant de client fourni.</p>";
                exit;
            }
            ?>
        </div>
    </aside>

    <!-- Contenu principal -->
   <main class="main-content">
        <header class="main-header">
            <h1>Administration des Annuaires</h1>
            <div class="action-buttons">
                <div class="button-container">
                    <form method="post" style="display: inline;">
                        <button name="export_csv" class="action-button">Exporter CSV</button>
                    </form>
                    <button id="add-contact" class="add-button">Ajouter un contact</button>
                </div>
            </div>
        </header>

        <!-- Formulaire d'ajout manuel de contact -->
      	<div id="add-contact-form" style="display: none; margin-top: 20px;">
            <h3>Ajouter un nouveau contact</h3>
            <form method="post">
                <label for="firstname">Prénom :</label>
                <input type="text" id="firstname" name="firstname" required><br>

                <label for="lastname">Nom :</label>
                <input type="text" id="lastname" name="lastname" required><br>

                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required><br>

                <label for="extension">Extension :</label>
                <input type="text" id="extension" name="extension"><br>

                <label for="code">Code :</label>
                <input type="text" id="code" name="code"><br>

                <button type="submit" name="add_contact" class="add-button">Ajouter</button>
            </form>
        </div>

        <section class="table-section">
            <h3>Liste des Contacts</h3>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th> 
                        <th>Prénom</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Extension</th>
                        <th>Code</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="contact-list">-->
                    <?php
                    // Récupération des contacts associés au client
                    $contacts = $annuaireManager->getAnnuaireByClient($clientsId);
                    foreach ($contacts as $contact) {
                        echo "<tr>
                            <td><input type='checkbox' class='contact-checkbox'></td>
                            <td>" . htmlspecialchars($contact['Nom']) . "</td>
                            <td>" . htmlspecialchars($contact['Adresse']) . "</td>
                            <td>" . htmlspecialchars($contact['Telephone']) . "</td>
                            <td>" . htmlspecialchars($contact['Email']) . "</td>
                            <td><button class='btn-delete' data-id='" . $contact['idAnnuaire'] . "'>✖</button></td>
                        </tr>";
                    }
                    ?>
               </tbody>
            </table>
        </section>
    </main>

    <a href="javascript:history.back()" class="back-button">Revenir en arrière</a>

    <script src="annuaire.js"></script>
</body>
</html>
