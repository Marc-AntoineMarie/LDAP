<?php
/**
 * Partner's client list
 * 
 * This page displays:
 * - The list of clients associated with a partner
 * - Links to each client's details
 * - Client management options based on user roles
 * 
 * Main features:
 * - Filtering clients by partner
 * - Access rights management (Admin/Partner)
 * - Maintaining navigation context
 * - Updating session IDs
 */

require_once '../database/db.php';

include '../database/partner_request.php';
include '../database/clients_request.php';
///////////////////// Access rights gestionnary ///////////////////
session_start();

// Authentication verification
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Partenaire')) {
    header('Location: ../login/login.php');
    exit;
}

// Retrieving the partner ID
$partnerId = null;
if (isset($_GET['idpartenaires'])) {
    $partnerId = intval($_GET['idpartenaires']);
    $_SESSION['partner_id'] = $partnerId;
    error_log("[clientlist.php] Updated partner_id in session from GET: " . $partnerId);
} elseif (isset($_SESSION['partner_id'])) {
    $partnerId = $_SESSION['partner_id'];
    error_log("[clientlist.php] Using partner_id from session: " . $partnerId);
} else {
    error_log("[clientlist.php] No partner_id found");
}

// Access rights verification
if ($_SESSION['role'] === 'Partenaire' && $_SESSION['partner_id'] !== $partnerId) {
    header('Location: ../login/login.php');
    exit;
}

///////////////////// END Access rights verification ///////////////////
//Temporaire pour le développement :
if (isset($_GET['idpartenaires']))
    $idpartenaire = $_GET['idpartenaires'];
else
    $idpartenaire = 2;

if (isset($_POST['idpartenaire']))
    $idpartenaire = $_POST['idpartenaire'];

$clientsHandler = new ClientsHandler($pdo);

// Management of deletion via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $clientId = intval($_POST['id']);
    $result = $clientsHandler->deleteClient($clientId);

    header('Content-Type: application/json');
    if ($result === true) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression.']);
    }
    exit;
}

// Claim Partner ID from session or GET
$partnerId = $_SESSION['partner_id'] ?? ($_GET['idpartenaires'] ?? null);

// need to check if partnerId is set
if ($partnerId === null) {
    echo "Erreur : aucun partenaire spécifié.";
    exit;
}

$partnerName = $clientsHandler->getPartnerNameById($partnerId);

$error = null;

// Forms tratiment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $clientsHandler->processAddClientForm($_POST, $partnerId);
    if ($result === true) {
        header("Location: ../clientlist/clientlist.php?idpartenaires=$partnerId");
        exit;
    } else {
        $error = $result;
    }
}

// Modal traitment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $clientsHandler->processAddClientForm($_POST, $partnerId);

    // Ajoutez cette partie pour gérer la réponse JSON
    header('Content-Type: application/json');

    if ($result === true) {
        echo json_encode(['success' => true, 'message' => 'Client ajouté avec succès']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => $result]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UI Example</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="clientlist2.css">
    <!-- <link rel="stylesheet" href="styles.scss"> -->
</head>

<body>
    <div id="loading-spinner" class="loading-spinner"></div>

    <!-- <img src="../admin/logo/Logo-ldap.png" alt="Logo" class="logo-header"> -->
    <?php //include '../partials/header_copy.php'; ?>

    <div class="container">
        <div class="title-section">
            <h1>Liste des clients pour : <span class="partner-name" id="partner-name">
                    <?php
                    if (isset($idpartenaire)) {
                        $partnerId = intval($idpartenaire);
                        $partnerName = $clientsHandler->getPartnerNameById($partnerId);
                        echo htmlspecialchars($partnerName);
                    } else {
                        echo "Aucun identifiant de partenaire fourni.";
                        exit;
                    }
                    ?>
                </span>
            </h1>
        </div>
        <div class="actions">
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <a href="#=<?php echo $idpartenaire; ?>" class="btn btn-primary" id="add-client"
                    style="text-decoration:none">Ajouter un client
                    <i class="fas fa-plus"></i>
                </a>
            <?php endif; ?>
        </div>
        <div class="filters">
            <div class="search-box">
                <input type="text" id="search-input" placeholder="Rechercher un client...">
                <i class="fas fa-search search-icon"></i>
            </div>
            <button class="btn btn-outline" id="filter-btn">
                <i class="fas fa-filter"></i> Filtres
            </button>
        </div>

        <div class="client-cards" id="client-list">
            <?php
            if (isset($idpartenaire)) {
                $Clients = $clientsHandler->getClientsByPartner($idpartenaire);
                foreach ($Clients as $client) {
                    // Vérifie si l'adresse est vide et attribue une valeur par défaut si nécessaire
                    $adresse = !empty($client['Adresse']) ? $client['Adresse'] : "Aucune localisation indiquée";

                    echo "<div class=\"client-card\" data-client-id=\"$client[idclients]\">";
                    echo "    <input type=\"checkbox\" class=\"card-checkbox\">";
                    echo "    <div class=\"card-logo\">";
                    echo "        <div class=\"logo-placeholder\">" . strtoupper(substr($client['Nom'], 0, 2)) . "</div>";
                    echo "    </div>";
                    echo "    <div class=\"card-body\">";
                    echo "        <div class=\"client-status status-active-tag\">";
                    echo "            <span class=\"status-indicator status-active\"></span> Actif";
                    echo "        </div>";
                    echo "        <a href=\"../clientdetail/clientdetail.php?idclient=$client[idclients]\" class=\"client-name\">";
                    echo "            $client[Nom]";
                    echo "        </a>";
                    echo "        <div class=\"client-details\">";
                    echo "            <div class=\"detail-item\"><i class=\"fas fa-phone\"></i> <span>$client[Telephone]</span></div>";
                    echo "            <div class=\"detail-item\"><i class=\"fas fa-envelope\"></i> <span>$client[Email]</span></div>";
                    echo "            <div class=\"detail-item\"><i class=\"fas fa-map-marker-alt\"></i> <span>$adresse</span></div>";
                    if ($idpartenaire == 0) {
                        echo "        <div class=\"detail-item\"><i class=\"fas fa-handshake\"></i> <span>Partenaire: $client[partenaires_idpartenaires]</span></div>";
                    }
                    echo "        </div>";
                    echo "        <div class=\"card-actions\">";
                    echo "            <button class=\"btn btn-icon btn-edit\" onclick=\"editClient($client[idclients])\">";
                    echo "                <i class=\"fas fa-edit\"></i>";
                    echo "            </button>";
                    echo "            <button class=\"btn btn-icon btn-delete\" data-client-id=\"$client[idclients]\" onclick=\"confirmDelete($client[idclients])\">";
                    echo "                <i class=\"fas fa-trash-alt\"></i>";
                    echo "            </button>";
                    echo "        </div>";
                    echo "    </div>";
                    echo "</div>";
                }
            }
            ?>
        </div>






        <!-- Add client by this modal-->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>
                <h2>Ajouter un nouveau client</h2>
                <form id="addClientForm" method="POST">
                    <input type="hidden" name="PartnerId" value="<?= htmlspecialchars($partnerId) ?>">
                    <!-- Name -->
                    <div class="mb-auto">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="Nom" placeholder="Entrez le nom"
                            required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="Email" placeholder="Entrez l'email"
                            required>
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Téléphone <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="telephone" name="Telephone"
                            placeholder="Entrez le numéro de téléphone" required>
                    </div>

                    <!-- Adress (optional) -->
                    <div class="mb-3">
                        <label for="adresse" class="form-label">Adresse</label>
                        <textarea class="form-control" id="adresse" name="Adresse"
                            placeholder="Entrez l'adresse (facultatif)" rows="3"></textarea>
                    </div>

                    <!-- Plateform -->
                    <div class="mb-3">
                        <label for="plateforme" class="form-label">Plateforme <span class="text-danger">*</span></label>
                        <select class="form-select" id="plateforme" name="Plateforme" onchange="updatePlatformURL()"
                            required>
                            <option value="">Choisir une plateforme...</option>
                            <option value="Wazo">Wazo</option>
                            <option value="OVH">OVH</option>
                            <option value="Yeastar">Yeastar</option>
                        </select>
                    </div>

                    <!-- Tenant (Wazo only) -->
                    <div class="mb-3" id="tenant" style="display: none;">
                        <label for="tenant_value" class="form-label">Wazo Tenant</label>
                        <select class="form-select" id="tenant_value" name="Tenant" onchange="updatePlatformURL()">
                            <option value="">Choisir un tenant...</option>
                            <?php foreach ($clientsHandler->getPlatforms()['Wazo'] as $name => $url): ?>
                                <option value="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- URL Plateform (readonly) -->
                    <div class="mb-3">
                        <label for="plateforme_url" class="form-label">URL Plateforme</label>
                        <input type="text" class="form-control" id="plateforme_url" name="PlateformeURL" readonly>
                    </div>

                    <!-- Submission button -->
                    <div class="text-center">
                        <button type="submit" name="add_client" class="btn btn-success">Ajouter le
                            Client</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit client modal -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close-edit-btn">&times;</span>
                <h2>Modifier les informations du client</h2>
                <form id="editClientForm" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="client_id" id="edit_client_id">
                    <input type="hidden" name="PartnerId" value="<?= htmlspecialchars($partnerId) ?>">

                    <!-- Name -->
                    <div class="mb-auto">
                        <label for="edit_nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_nom" name="Nom" placeholder="Entrez le nom"
                            required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="edit_email" name="Email"
                            placeholder="Entrez l'email" required>
                    </div>

                    <!-- Phone -->
                    <div class="mb-3">
                        <label for="edit_telephone" class="form-label">Téléphone <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_telephone" name="Telephone"
                            placeholder="Entrez le numéro de téléphone" required>
                    </div>

                    <!-- Adress (optional) -->
                    <div class="mb-3">
                        <label for="edit_adresse" class="form-label">Adresse</label>
                        <textarea class="form-control" id="edit_adresse" name="Adresse"
                            placeholder="Entrez l'adresse (facultatif)" rows="3"></textarea>
                    </div>

                    <!-- Plateform -->
                    <div class="mb-3">
                        <label for="edit_plateforme" class="form-label">Plateforme <span
                                class="text-danger">*</span></label>
                        <select class="form-select" id="edit_plateforme" name="Plateforme"
                            onchange="updateEditPlatformURL()" required>
                            <option value="">Choisir une plateforme...</option>
                            <option value="Wazo">Wazo</option>
                            <option value="OVH">OVH</option>
                            <option value="Yeastar">Yeastar</option>
                        </select>
                    </div>

                    <!-- Tenant (Wazo only) -->
                    <div class="mb-3" id="edit_tenant_div" style="display: none;">
                        <label for="edit_tenant_value" class="form-label">Wazo Tenant</label>
                        <select class="form-select" id="edit_tenant_value" name="Tenant"
                            onchange="updateEditPlatformURL()">
                            <option value="">Choisir un tenant...</option>
                            <?php foreach ($clientsHandler->getPlatforms()['Wazo'] as $name => $url): ?>
                                <option value="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- URL Plateform (readonly) -->
                    <div class="mb-3">
                        <label for="edit_plateforme_url" class="form-label">URL Plateforme</label>
                        <input type="text" class="form-control" id="edit_plateforme_url" name="PlateformeURL" readonly>
                    </div>

                    <!-- Submission button -->
                    <div class="text-center">
                        <button type="submit" name="update_client" class="btn btn-success">Mettre à jour</button>
                    </div>
                </form>
            </div>
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
                        if (isset($idpartenaire)) {
                            if ($idpartenaire == 0) {
                                echo "<th>Partenaire</th>";
                            }
                        }
                        ?>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="client-list">
                    <?php
                    if (isset($idpartenaire)) {
                        $Clients = $clientsHandler->getClientsByPartner($idpartenaire);
                        foreach ($Clients as $client) {
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
                            if ($idpartenaire == 0) {
                                echo "<td>$client[partenaires_idpartenaires]</td>";
                            }
                            echo "<td><button class=\"btn-delete\" data-client-id=\"$client[idclients]\">✖</button></td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <a href="javascript:history.back()" class="back-button">Revenir en arrière</a>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Modal gestionnary
            const modal = document.getElementById('myModal');
            const openModalLink = document.getElementById('add-client');
            const closeBtn = document.querySelector('.close-btn');

            // Open Modal
            openModalLink.addEventListener('click', (e) => {
                e.preventDefault();
                modal.style.display = 'flex';
            });

            // Close Modal
            closeBtn.addEventListener('click', () => {
                modal.style.display = 'none';
            });

            // Close Modal if click outside
            window.addEventListener('click', (event) => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // platform function for URL and tenant display
        // no safe method need improvment (security IP)
        function updatePlatformURL() {
            const platform = document.getElementById('plateforme').value;
            const tenant = document.getElementById('tenant');
            const platformURL = document.getElementById('plateforme_url');

            let url = '';
            if (platform === 'Wazo') {
                tenant.style.display = 'block';
                const tenantValue = document.getElementById('tenant_value').value;
                url = tenantValue;
            } else if (platform === 'OVH') {
                tenant.style.display = 'none';
                url = 'fr.proxysip.eu';
            } else if (platform === 'Yeastar') {
                tenant.style.display = 'none';
                url = '192.168.1.150';
            } else {
                tenant.style.display = 'none';
            }

            platformURL.value = url;
        }
 
// Fonction pour ouvrir la modal d'édition et charger les données du client
function editClient(clientId) {
    // Afficher le spinner de chargement
    document.getElementById('loading-spinner').style.display = 'flex';
    
    // Récupérer les données du client via une requête AJAX
    fetch(`../clientdetail/get_client_data.php?id=${clientId}`)
        .then(response => response.json())
        .then(client => {
            // Remplir le formulaire avec les données du client
            document.getElementById('edit_client_id').value = client.idclients;
            document.getElementById('edit_nom').value = client.Nom;
            document.getElementById('edit_email').value = client.Email;
            document.getElementById('edit_telephone').value = client.Telephone;
            document.getElementById('edit_adresse').value = client.Adresse || '';
            
            // Définir la plateforme
            const platformSelect = document.getElementById('edit_plateforme');
            platformSelect.value = client.Plateforme;
            
            // Mettre à jour l'affichage du tenant selon la plateforme
            if (client.Plateforme === 'Wazo') {
                document.getElementById('edit_tenant_div').style.display = 'block';
                document.getElementById('edit_tenant_value').value = client.PlateformeURL;
            } else {
                document.getElementById('edit_tenant_div').style.display = 'none';
            }
            
            // Afficher l'URL de la plateforme
            document.getElementById('edit_plateforme_url').value = client.PlateformeURL;
            
            // Masquer le spinner et afficher la modal
            document.getElementById('loading-spinner').style.display = 'none';
            document.getElementById('editModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Erreur lors du chargement des données client:', error);
            document.getElementById('loading-spinner').style.display = 'none';
            alert('Erreur lors du chargement des données client.');
        });
}

// Fonction pour mettre à jour l'URL de la plateforme dans le formulaire d'édition
function updateEditPlatformURL() {
    const platform = document.getElementById('edit_plateforme').value;
    const tenant = document.getElementById('edit_tenant_div');
    const platformURL = document.getElementById('edit_plateforme_url');

    let url = '';
    if (platform === 'Wazo') {
        tenant.style.display = 'block';
        const tenantValue = document.getElementById('edit_tenant_value').value;
        url = tenantValue;
    } else if (platform === 'OVH') {
        tenant.style.display = 'none';
        url = 'fr.proxysip.eu';
    } else if (platform === 'Yeastar') {
        tenant.style.display = 'none';
        url = '192.168.1.150';
    } else {
        tenant.style.display = 'none';
    }

    platformURL.value = url;
}

// Gestionnaire d'événement pour fermer la modal d'édition
document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('editModal');
    const closeEditBtn = document.querySelector('.close-edit-btn');
    
    // Fermer la modal au clic sur le bouton de fermeture
    closeEditBtn.addEventListener('click', () => {
        editModal.style.display = 'none';
    });
    
    // Fermer la modal si clic en dehors
    window.addEventListener('click', (event) => {
        if (event.target === editModal) {
            editModal.style.display = 'none';
        }
    });
    
    // Soumettre le formulaire d'édition via AJAX
    document.getElementById('editClientForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('../clientdetail/update_client.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Client mis à jour avec succès!');
                editModal.style.display = 'none';
                // Recharger la page pour afficher les modifications
                location.reload();
            } else {
                alert('Erreur: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de la mise à jour du client.');
        });
    });
});
</script>
    <script src="clientlist.js"></script>
</body>

</html>