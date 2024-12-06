<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface Utilisateur</title>
    <link rel="stylesheet" href="annuaire.css">
</head>
<body>

    <!-- Barre latérale -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../logo/logo-ldap.png" alt="Logo" class="logo-sidebar">
            <h2>AGILINK</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#">Contact Management</a></li>
            <ul class="submenu">
                <li><a href="#">Administration</a></li>
                <li><a href="#">Comptabilité</a></li>
                <li><a href="#">Technique</a></li>
                <li><a href="#">Vendeur</a></li>
                <li><a href="#">Test</a></li>
            </ul>
        </ul>
    </aside>

    <!-- Contenu principal -->
    <main class="main-content">
        <header class="main-header">
            <h1>Administration</h1>
            <div class="action-buttons">
                <div class="button-container">
                    <button class="action-button">Import CSV</button>
                    <button class="action-button">Export CSV</button>
                    <button id="add-client" class="add-button">Ajouter un client</button> 
                </div>
            </div>
        </header>

        <section class="table-section">
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th> 
                        <th>Firstname</th>
                        <th>Lastname</th>
                        <th>Email</th>
                        <th>Extension</th>
                        <th>Code</th>
                        <th>Supprimmer</th>
                    </tr>
                </thead>
                <tbody id="client-list">
                    <tr>
                        <td><input type="checkbox" class="client-checkbox"></td> 
                        <td>AALIYAH</td>
                        <td>BOIS-COLOMBES</td>
                        <td>gregoireetbarilleau@gmx.fr</td>
                        <td>2140</td>
                        <td>444145</td>
                        <td><button class="btn-delete">✖</button></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" class="client-checkbox"></td> 
                        <td>ALINE</td>
                        <td>BRIENNE CARENTAN</td>
                        <td>alineb@gmx.fr</td>
                        <td>9464</td>
                        <td>140843</td>
                        <td><button class="btn-delete">✖</button></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>
    <a href="javascript:history.back()" class="back-button">Revenir en arrière</a>

    <script src="annuaire.js"></script> 
</body>
</html>
