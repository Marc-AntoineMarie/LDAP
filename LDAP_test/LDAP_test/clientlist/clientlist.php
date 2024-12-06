<?php
include '../database/db.php';
include '../database/partner_request.php';
include '../database/clients_request.php';
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
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Adresse</th>
                        <th>Supprimer</th>
                    </tr>
                </thead>
                <tbody id="client-list">
                   <?php foreach ($Clients as $client): ?>
                    <tr>
                        <td><?php htmlspecialchars($client['Nom'] ?? 'Non défini') ?></td>
                        <td><?php htmlspecialchars($client['Email'] ?? 'Non défini') ?></td>
                        <td><?php htmlspecialchars($client['Telephone'] ?? 'Non défini') ?></td>
                        <td><?php htmlspecialchars($client['Adresse'] ?? 'Non défini') ?></td>
                        <td><?php htmlspecialchars($client['Partenaires_idpartenaires'] ?? 'Non défini') ?></td>
                    </tr>
                   <?php endforeach; ?> 
                </tbody>
            </table>
        </div>
    </section>

    <a href="javascript:history.back()" class="back-button">Revenir en arrière</a>
    <!-- <script src="clientlist.js"></script> -->


    <!-- fonction JS de filtrage a ajouter et parametrer quand le tableau Client et l'ajout client sera mis en place  -->

    <!-- <script>
        function filterClients () {
            const input =document.getElementById('search-input');
            const filter = input.value.toLowerCase();
            const row = document.querySelectorAll('#client-list tr');

            row.forEach(row => {
                const cells = row.querySelectorAll('td');
                const matches = Array.from(cells).some(cell => cell.textContent.toLowerCase().include(filter));
                row.style.display = matches ? '' : 'none';
            });
        }
    </script> -->


</body>
</html>
