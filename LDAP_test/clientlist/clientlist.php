<?php
require_once '../database/db.php';
include '../database/partner_request.php';
include '../database/clients_request.php';

//Temporaire pour le développement :
$_POST['idpartenaires'] = 2;

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
            if (isset($_POST['idpartenaires'])) {
                $partnerId = intval($_POST['idpartenaires']);
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
                        	if (isset($_POST['idpartenaires'])) {
                        		if ($_POST['idpartenaires'] == 0){
                       				echo "<th>Partenaire</th>";
                       			}
                        	}
                        ?>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="client-list">
                	
                <?php
                	if (isset($_POST['idpartenaires'])) {
                		$Clients = $ClientsForm->ClientsRecoveryByPartenaire($partnerId);
                		
                		foreach ($Clients as $client){
                			 echo "<tr>";
                			 echo "<td><input type=\"checkbox\" class=\"client-checkbox\"></td>";
                       echo "<td class=\"logo-cell\">";
                       echo "<div class=\"logo-placeholder\"></div>";
                       echo "</td>";
                       echo "<td class=\"card-content\">";
                       echo "<a href=\"../clientdetail/clientdetail.php?idclient=$client[idclients]\">";
                       echo "<h2>$client[Nom]</h2>";
                       echo "</a>";
                       echo "</td>";
                       echo "<td>$client[Telephone]</td>";
                       echo "<td>$client[Adresse]</td>";
                       if ($_POST['idpartenaires'] == 0){
                       	//A faire : afficher le nom à la place de l'id
                       	 echo "<td>$client[partenaires_partenairesid]</td>";
                       }
                       echo "<td><button class=\"btn-delete\">✖</button></td>";
                       echo "</tr>";              			
                		}
                	}
                	
                ?>
                </tbody>
            </table>
        </div>
    </section>

    <a href="javascript:history.back()" class="back-button">Revenir en arrière</a>
    <!-- <script src="clientlist.js"></script> -->
</body>
</html>
