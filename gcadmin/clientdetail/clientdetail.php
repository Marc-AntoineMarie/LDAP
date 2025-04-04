<?php
require_once("../database/db.php");
include '../database/clients_request.php';
include '../database/utilisateurs_request.php';
include '../classes/provisionning.php';

if (!isset($pdo)) {
    die("Erreur : La connexion PDO n'est toujours pas initialisée.");
}else{
    //Je commente outrageusement ton caca
    //echo 'annuaire.php -> ok caca | ';
}

///////////////////// vérif des rôles ///////////////////

session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['Admin', 'Partenaire', 'Client'])) {
    header('Location: ../login/login.php');
    exit;
}

// Récupération de l'ID client
if (isset($_GET['idclient'])) $idclient = $_GET['idclient'];
if (isset($_POST['idclient'])) $idclient = $_POST['idclient'];

// Mettre à jour l'ID client dans la session
if (isset($idclient)) {
    $_SESSION['client_id'] = $idclient;
    error_log("[clientdetail.php] Updated client_id in session: " . $idclient);
}

// Récupération et mise à jour de l'ID partenaire dans la session
if (isset($idclient)) {
    $stmt = $pdo->prepare("SELECT partenaires_idpartenaires FROM Clients WHERE idclients = ?");
    $stmt->execute([$idclient]);
    $client = $stmt->fetch();
    if ($client) {
        $_SESSION['partner_id'] = $client['partenaires_idpartenaires'];
        error_log("[clientdetail.php] Updated partner_id in session: " . $client['partenaires_idpartenaires']);
    }
}

// Vérification pour un partenaire ou un client
if ($_SESSION['role'] === 'Partenaire') {
    // Le partenaire ne peut accéder qu'aux détails des clients associés à son partenaire ID
    if (!isset($idclient) || !in_array($idclient, getClientsForPartner($_SESSION['partner_id']))) {
        header('Location: ../login/login.php');
        exit;
    }
} elseif ($_SESSION['role'] === 'Client') {
    // Le client ne peut accéder qu'à son propre détail
    if (!isset($idclient) || $idclient != $_SESSION['client_id']) {
        header('Location: ../login/login.php');
        exit;
    }
}

// Fonction pour récupérer les IDs des clients pour un partenaire
function getClientsForPartner($partnerId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT idclients FROM Clients WHERE partenaires_idpartenaires = :partnerId");
    $stmt->bindParam(':partnerId', $partnerId, PDO::PARAM_INT);
    $stmt->execute();
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'idclients');
}
///////////////////// FIN vérif des rôles ///////////////////


if (ISSET($_GET['idclient'])) $idclient = $_GET['idclient'];
if (ISSET($_POST['idclient'])) $idclient = $_POST['idclient'];

if (ISSET($_POST['EditClient']))
{
	//Mise à jour des informations du client
	
	if ($_POST["EditNom"] != "") $nom = $_POST["EditNom"];
  else $nom = "";
  if ($_POST["EditEmail"] != "") $mail = $_POST["EditEmail"];
  else $mail = "";
  if ($_POST["EditTelephone"] != "") $tel = $_POST["EditTelephone"];
  else $tel = "";
  if ($_POST["EditAdresse"] != "") $adresse = $_POST["EditAdresse"];
  else $adresse = "";
  if ($_POST["EditPlateforme"] != "") $plateforme = $_POST["EditPlateforme"];
  else $plateforme = "";
  if ($_POST["EditPlateformeURL"] != "") $plateformeurl = $_POST["EditPlateformeURL"];
  else $plateformeurl = "";
  
	$ClientsForm->ClientsUpdate($idclient,$nom,$mail,$tel,$adresse,$plateforme,$plateformeurl);
	
}


if (ISSET($_POST['DeleteUser']))
{
	//Suppression d'un utilisateur
	$UtilisateursForm->UtilisateursDelete($_POST['idutilisateur']);	
}

if (ISSET($_POST['AutoBLF']))
{
	$utilisateurs = $UtilisateursForm->UtilisateursRecoveryByClient($idclient); 
                    
  foreach ($utilisateurs as $utilisateur) 
  {
		$Provisionning->AutoBLF($UtilisateursForm, $utilisateur["idutilisateurs"]);
	}
}

// Récupération de l'ID client depuis l'URL
//$clientId = isset($_GET['idclient']) ? intval($_GET['idclient']) : 0;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telora - Détail Client</title>
    <link rel="icon" type="image/png" href="../admin/logo/Logo-ldap.png">
    <link rel="shortcut icon" type="image/png" href="../admin/logo/Logo-ldap.png">
    <link rel="stylesheet" href="clientdetail.css">
    <script src="clientdetail.js"></script>
</head>
<body>

    <!-- Barre latérale -->
 <?php include '../partials/barreclient.php'; ?>

    <!-- Contenu principal -->
   <main class="main-content">
        <?php include '../partials/header.php'; ?>
        <header class="main-header">
            <h1>Administration d'un client</h1>
            <div class="action-buttons">
                <div class="button-container">
                <form method="post" style="display: inline;">
                    <button id="gerer-annuaire" data-id="<?= $idclient ?>" class="action-button" type="button">Gérer l'annuaire</button>
                </form>
                </div>
            </div>
        </header>


        <section class="table-section">
            <h3>Liste des utilisateurs</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Extension</th>
                        <th>Type poste</th>
                        <th>MAC</th>
                        <th>SIPLog</th>
                        <th>SIPSRV</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="contact-list">
                    <?php
                    
                    $utilisateurs = $UtilisateursForm->UtilisateursRecoveryByClient($idclient, "Extension ASC");
                    
                    
                    foreach ($utilisateurs as $utilisateur) {
	                     echo "<tr>
                            <td>" . htmlspecialchars($utilisateur['Nom']) . "</td>
                            <td>$utilisateur[Extension]</td>
                            <td>$utilisateur[TypePoste]</td>
                            <td>$utilisateur[AdresseMAC]</td>
                            <td>$utilisateur[SIPLogin]</td>
                            <td>$utilisateur[SIPServeur]</td>
                            <td><form method='POST' action=\"../utilisateurdetail/utilisateurdetail.php\" style='display:inline;'>
                            <input type='hidden' name='idutilisateur' value='$utilisateur[idutilisateurs]'>
                            <input type='hidden' name='idclient' value='$idclient'>
                            <button class='btn-delete' type='submit'><img src=\"../logo/Editer2.png\"></button></form>
                            <form method='POST' action=\"../clientdetail/clientdetail.php\" style='display:inline;'>
                            <input type='hidden' name='idutilisateur' value='$utilisateur[idutilisateurs]'>
                            <input type='hidden' name='idclient' value='$idclient'>
                            <button name='DeleteUser' class='btn-delete' data-id='" . $contact['idAnnuaire'] . "'>✖</button></form></td>
                        </tr>";
                    }
                    
                    echo "<tr>";
                    echo "<td colspan=8 id='action-button-new'><form method='POST' action=\"../utilisateurdetail/utilisateurdetail.php\" style='display:inline;'>
                            <input type='hidden' name='idclient' value='$idclient'>
                            <button name='NewUser' class='action-button' type='submit'>Nouveau</button>
                           </form>
                           <form method='POST' action=\"../clientdetail/clientdetail.php\" style='display:inline;'>
                            <input type='hidden' name='idclient' value='$idclient'>
                            <button name='AutoBLF' class='action-button' type='submit'>Auto-BLF</button>
                           </form>
                           </td>";
                    echo "</tr>";
                     
                    // Récupération des contacts associés au client
                    /*$contacts = $annuaireManager->getAnnuaireByClient($clientsId);
                    foreach ($contacts as $contact) {
                        echo "<tr>
                            <td><input type='checkbox' class='contact-checkbox'></td>
                            <td>" . htmlspecialchars($contact['Nom']) . "</td>
                            <td>" . htmlspecialchars($contact['Adresse']) . "</td>
                            <td>" . htmlspecialchars($contact['Telephone']) . "</td>
                            <td>" . htmlspecialchars($contact['Email']) . "</td>
                            <td><button class='btn-delete' data-id='" . $contact['idAnnuaire'] . "'>✖</button></td>
                        </tr>";
                    }*/
                    ?>
               </tbody>
            </table>
        </section>
    </main>

    <?php
			echo "<form method='POST' action=\"../clientlist/clientlist.php\" style='display:inline;'>
             <input type='hidden' name='idpartenaire' value='".$client['partenaires_idpartenaires']."'>
             <button name='RetourArriere' class='back-button' type='submit'>Revenir en arrière</button>
             </form>";
    ?>


</body>
</html>
