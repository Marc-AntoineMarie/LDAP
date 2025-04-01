<?php
include '../database/db.php';
include '../database/partner_request.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GC LDAP</title>
    <link rel="stylesheet" href="V1 admin.css">
</head>
<body>

    <header>
        <!-- LOGO -->
        <div class="logo">
            <img src="logo/Logo-ldap.png" alt="GC LDAP Logo">
        </div>
        <!-- FIN LOGO -->
    </header>

    <div class="container-body">
        <main>
            <!-- BANDE GC LDAP -->
            <section class="hero">
                <h1>GC LDAP</h1>
                <p id="animatedText">Oh yeah !</p>
            </section>
            <!-- FIN BANDE GC LDAP -->

            <!-- Boutons gestion partenaires -->
            <div class="buttons-container">
                <a href="addpartner_form.php" class="btn" id="add-partner" style="text-decoration:none">Ajouter Partenaire</a>
            </div>

            <!-- -------------------------- -->
                <!-- Bloc partenaires -->
            <!-- -------------------------- -->
            <section class="partners">
                <h2>Partenaire</h2>
                <div class="container-carré" id="partner-list">
                    <?php 
                        foreach ($Partners as $partner) {
                            echo '<a href="../clientlist/clientlist.php?idpartenaires=' . htmlspecialchars($partner['idpartenaires']) . '" class="carré">';
                            echo '<img src="logo/' . htmlspecialchars($partner['idpartenaires']) . '.png" alt="' . htmlspecialchars($partner['Nom']) . '">';
                            echo '<p>' . htmlspecialchars($partner['Nom']) . '</p>';
                            echo '</a>';
                        }
                    ?>
                </div>
            </section>
            <!-- -------------------------- -->
                <!-- FIN Bloc partenaires -->
            <!-- -------------------------- -->
        </main>
    </div>
    <!-- <script src="V1 admin.js"></script> -->
</body>
</html>