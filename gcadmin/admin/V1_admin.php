<?php
/**
 * Page d'administration principale
 * 
 * Cette page est le point d'entrée pour les administrateurs et permet :
 * - La gestion des partenaires (liste, ajout, modification)
 * - L'accès aux clients de chaque partenaire
 * - La gestion des droits d'accès globaux
 * 
 * Fonctionnalités clés :
 * - Vue d'ensemble de tous les partenaires
 * - Navigation vers les clients de chaque partenaire
 * - Gestion des accès et des permissions
 * - Point de départ de la navigation en marque blanche
 */

// Inclusion des dépendances nécessaires
// Ces fichiers fournissent :
// - La connexion à la base de données
// - Les fonctions de gestion des partenaires
// - Les utilitaires communs
require_once '../database/db.php';
require_once '../database/partner_request.php';
require_once '../database/login_request.php';

///////////////////// Gestion des droits d'accès ///////////////////
session_start();

// Vérification du rôle administrateur
// Cette vérification est critique pour :
// - La sécurité de l'application
// - L'accès aux fonctionnalités d'administration
// - La protection des données sensibles
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login/login.php');
    exit;
}

///////////////////// FIN vérif des rôles ///////////////////
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telora</title>
    <!-- je propose ce nom mdr comme le LDAP n'est qu'une petite aprtie du site -->
    <link rel="icon" type="image/png" href="logo/Logo-ldap.png">
    <link rel="shortcut icon" type="image/png" href="logo/Logo-ldap.png">
    <link rel="stylesheet" href="V1_admin.css">
    <link rel="stylesheet" href="styles.scss" type="text/x-scss">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sass.js/dist/sass.sync.min.js"></script>
    <script>
        // Attendre que SASS soit chargé
        window.addEventListener('load', function () {
            // Compiler le SCSS
            fetch('styles.scss')
                .then(response => response.text())
                .then(scss => {
                    if (typeof Sass !== 'undefined') {
                        Sass.compile(scss, function (result) {
                            if (result.status === 0) {
                                const style = document.createElement('style');
                                style.textContent = result.text;
                                document.head.appendChild(style);
                            } else {
                                console.error('Erreur SASS:', result.message);
                            }
                        });
                    } else {
                        console.error('SASS nest pas chargé correctement');
                    }
                })
                .catch(error => console.error('Erreur de chargement du SCSS:', error));
        });
    </script>
</head>

<body>

    <header>
        <!-- LOGO -->
        <div class="logo">
            <img src="logo/Logo-ldap.png" alt="Telora Logo">
        </div>
        <!-- FIN LOGO -->
    </header>

    <div class="container-body">
        <main>
            <!-- BANDE Telora -->
            <section class="hero">
                <h1>Telora</h1>
                <p class="subtitle">Solution de Gestion de Contacts Multi-Partenaires</p>
            </section>
            <!-- FIN BANDE Telora -->

            <!-- Boutons gestion partenaires -->
            <div class="button-container-2">
                <span class="mas">Ajouter</span>
                <a href="addpartner_form.php" class="button" id="openModal">Ajouter</a>
            </div>

            <div id="myModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn">&times;</span>
                    <h2>Ajouter un Partenaire</h2>
                    <form action="V1_admin.php" method="POST">
                        <input type="hidden" name="action" value="1">
                        <!-- Nom -->
                        <div class="input-group">
                            <label class="input-group__label" for="nom">Nom <span class="text-danger">*</span></label>
                            <input type="text" id="nom" name="Nom" class="input-group__input" required>
                        </div>

                        <!-- Email -->
                        <div class="input-group">
                            <label class="input-group__label" for="email">Email <span
                                    class="text-danger">*</span></label>
                            <input type="email" id="email" name="Email" class="input-group__input" required>
                        </div>

                        <!-- Téléphone -->
                        <div class="input-group">
                            <label class="input-group__label" for="telephone">Téléphone <span
                                    class="text-danger">*</span></label>
                            <input type="tel" id="telephone" name="Telephone" class="input-group__input" required>

                        </div>

                        <!-- Adresse -->
                        <div class="input-group">
                            <label class="input-group__label" for="adresse">Adresse</label>
                            <textarea id="adresse" name="Adresse" class="input-group__input" rows="2"></textarea>
                        </div>

                        <!-- Bouton de soumission -->
                        <div class="text-center mt-3">
                            <button type="submit" name="add_partner" class="btn">Ajouter</button>
                        </div>
                        </input>
                    </form>
                </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Récupérer les éléments
        const modal = document.getElementById('myModal');
        const openModalLink = document.getElementById('openModal');
        const closeBtn = document.querySelector('.close-btn');

        // Empêcher le comportement par défaut du lien
        openModalLink.addEventListener('click', (e) => {
            e.preventDefault(); // Empêche le lien de naviguer
            modal.style.display = 'flex';
        });

        // Fermer la modal
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Fermer la modal si on clique en dehors
        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>

</html>