<?php
include '../database/db.php';
include '../database/partner_request.php';
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
            <h1>Liste Des Clients Finaux pour : </h1> 
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
                    echo '<p>' . $partnerName . '</p>';
                }   else {
                    echo '<p>Partenaire non trouvé.</p>';
                }
            }   else {
                echo "<p> Aucun idenifiant de partenaire fourni.</P>";
                exit; //coupe l'éxecution si l'ID n'est pas défini
            }
            
            // echo '<p>' . htmlspecialchars($partnerName) . '</p>'; 
            ?>
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
                        <th>Code postal</th>
                        <th>Contact</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody id="client-list">
                    <tr>
                        <td><input type="checkbox" class="client-checkbox"></td>
                        <td class="logo-cell">
                            <div class="logo-placeholder"></div>
                        </td>
                        <td class="card-content">
                            <a href="../annuaire/annuaire.php">
                                <h2>Centre omnisport de Mâcon</h2>
                            </a>
                        </td>
                        <td>03 85 32 32 10</td>
                        <td>71000</td>
                        <td>ADV</td>
                        <td><button class="btn-delete">✖</button></td>
                    </tr>
                    <tr>
                    <td><input type="checkbox" class="client-checkbox"></td>
                        <td class="logo-cell">
                            <div class="logo-placeholder"></div>
                        </td>
                        <td class="card-content">
                            <a href="client2.html">
                                <h2>Pharmacie des vignes</h2>
                            </a>
                        </td>
                        <td>06 33 89 05 29</td>
                        <td>67400</td>
                        <td>ADV</td>
                        <td><button class="btn-delete">✖</button></td>
                    </tr>
                    <tr>
                    <td><input type="checkbox" class="client-checkbox"></td>
                        <td class="logo-cell">
                            <div class="logo-placeholder"></div>
                        </td>
                        <td class="card-content">
                            <a href="client3.html">
                                <h2>Société X</h2>
                            </a>
                        </td>
                        <td>01 23 45 67 89</td>
                        <td>75000</td>
                        <td>ADV</td>
                        <td><button class="btn-delete">✖</button></td>
                    </tr>
                    <tr>
                    <td><input type="checkbox" class="client-checkbox"></td>
                        <td class="logo-cell">
                            <div class="logo-placeholder"></div>
                        </td>
                        <td class="card-content">
                            <a href="client4.html">
                                <h2>Boulangerie du coin</h2>
                            </a>
                        </td>
                        <td>04 56 78 90 12</td>
                        <td>69000</td>
                        <td>ADV</td>
                        <td><button class="btn-delete">✖</button></td>
                    </tr>
                    <tr>
                    <td><input type="checkbox" class="client-checkbox"></td>
                        <td class="logo-cell">
                            <div class="logo-placeholder"></div>
                        </td>
                        <td class="card-content">
                            <a href="client5.html">
                                <h2>Restaurant du Sud</h2>
                            </a>
                        </td>
                        <td>07 89 01 23 45</td>
                        <td>13000</td>
                        <td>ADV</td>
                        <td><button class="btn-delete">✖</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <a href="javascript:history.back()" class="back-button">Revenir en arrière</a>
    <!-- <script src="clientlist.js"></script> -->
</body>
</html>
