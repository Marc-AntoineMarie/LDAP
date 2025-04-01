<?php
include_once '../database/db.php';
include_once '../database/partner_request.php';
include_once '../database/clients_request.php';

//Temporaire pour le développement :
// $_POST['idpartenaires'] = 2;

// Vérification que le partenaire id est bien set dans l'URL
if (isset($_GET['idpartenaires'])) {
    $partnerId = intval($_GET['idpartenaires']);
    // Retrouver les clients à partir du partenaire ID
    $Clients = $ClientsForm->ClientsRecoveryByPartenaire($partnerId);

    // Rediriger vers la même page sans arrêter l'exécution du HTML
    if (empty($Clients)) {
        echo "Aucun client trouvé pour ce partenaire.";
    }
} else {
    echo "Aucun identifiant de partenaire fourni.";
    exit; 
}

// Si un client est à supprimer
if (isset($_POST['delete_client']) && isset($_POST['delete_id'])) {
    $idclient = intval($_POST['delete_id']);  // On s'assure que l'ID est un entier valide

    // Suppression du client
    $deleteResult = $ClientsForm->deleteClient($idclient);
    
    if ($deleteResult === true) {
        // Si la suppression est réussie, on redirige vers la même page
        header("Location: clientlist.php?idpartenaires=" . $_GET['idpartenaires']);
        exit;
    } else {
        // Si erreur de suppression, afficher un message
        echo "Erreur lors de la suppression : " . $deleteResult;
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UI Example</title>
    <link rel="stylesheet" href="clientlist.css">
</head>
<body>

    <img src="../admin/logo/Logo-ldap.png" alt="Logo" class="logo-header">

    <section class="main-section">
        <div class="title-container">
            <h1>Liste des clients pour : 
            <?php
            //récupération de l'id partenaire pour afficher le bon nom + vérification de l'id dans l'URL
            //Vérification de l'id dans l'URL
            if (isset($_GET['idpartenaires'])) {
                $partnerId = intval($_GET['idpartenaires']);
                $partnerName = null;

                //recherche du partenaire en fonction de l'id
                foreach ($Partners as $partner){
                    if ($partner['idpartenaires'] == $partnerId) {
                        $partnerName = htmlspecialchars($partner['Nom']);
                        break;
                    }
                }

                //affichage du nom correspondant a l'id
                if ($partnerName) {
                    echo $partnerName;
                }   else {
                    echo 'Partenaire non trouvé.';
                }
            }   else {
                echo " Aucun idenifiant de partenaire fourni.";
                exit; //coupe l'éxecution si l'ID n'est pas défini
            }
            
            // echo '<p>' . htmlspecialchars($partnerName) . '</p>'; */
            ?></h1> 
        </div>

        <div class="button-container">
            <a href="addclients_form.php" class="add-button" id="add-client" style="text-decoration:none">Ajouter un client</a>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Logo</th> 
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <?php
                        	if (isset($_GET['idpartenaires'])) {
                        		if ($_GET['idpartenaires'] == 0){
                       				echo "<th>Partenaire</th>";
                       			}
                        	}
                        ?>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="client-list">
                	
                <?php foreach ($Clients as $client): ?>
                    <tr data-id="<?php echo $client['idclients']; ?>">
                        <td><input type="checkbox" class="client-checkbox"></td>
                        <td class="logo-cell">
                            <div class="logo-placeholder"></div>
                        </td>
                        <td class="card-content">
                            <a href="../clientdetail/clientdetail.php?idclient=<?php echo $client['idclients']; ?>">
                                <h2><?php echo htmlspecialchars($client['Nom']); ?></h2>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($client['Telephone']); ?></td>
                        <td><?php echo htmlspecialchars($client['Adresse']); ?></td>
                        <?php if ($_GET['idpartenaires'] == 0): ?>
                            <td><?php echo htmlspecialchars($client['partenaires_partenairesid']); ?></td>
                        <?php endif; ?>
                        <td>
                            <form method="POST" action="clientlist.php">
                                <input type="hidden" name="delete_id" value="<?php echo $client['idclients']; ?>">
                                <button type="submit" class="btn-delete" name="delete_client">✖</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <a href="javascript:history.back()" class="back-button">Revenir en arrière</a>
    <!-- <script src="clientlist.js"></script> -->
</body>
</html>
