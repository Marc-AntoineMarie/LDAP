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
    <link rel="stylesheet" href="clientlist.css">
    <link rel="stylesheet" href="styles.scss">
</head>

<body>
    <img src="../admin/logo/Logo-ldap.png" alt="Logo" class="logo-header">
    <?php include '../partials/header.php'; ?>

    <section class="main-section">
        <div class="title-container">
            <h1>Liste des clients pour :
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
            </h1>
        </div>

        <div class="button-container">
            <?php if ($_SESSION['role'] === 'Admin'): ?>
                <a href="#=<?php echo $idpartenaire; ?>" class="add-button" id="add-client"
                    style="text-decoration:none">Ajouter un client</a>
            <?php endif; ?>
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
                                <option value="<?= htmlspecialchars($url) ?>"><?= htmlspecialchars($name) ?></option>
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
                        <button type="submit" name="add_client" class="btn btn-success">Ajouter le Client</button>
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
    </section>

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
    </script>
    <script src="clientlist.js"></script>
</body>

</html>