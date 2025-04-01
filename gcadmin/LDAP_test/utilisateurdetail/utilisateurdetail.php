<?php
require_once '../database/db.php';
include '../database/clients_request.php';
include '../database/utilisateurs_request.php';
include '../database/postes_request.php';

if (!isset($pdo)) {
    die("Erreur : La connexion PDO n'est toujours pas initialisée.");
}else{
    //Je commente outrageusement ton caca
    //echo 'annuaire.php -> ok caca | ';
}



$majdatabase = 0;

if (ISSET($_POST['BLFBas']))
{
	//Modification de l'ordre des BLF + enregistrement des informations
	
	$blforigine = $UtilisateursForm->UtilisateursBLFRecoveryById($_POST['BLFBas'])[0];
	$blfdest = $UtilisateursForm->UtilisateursBLFRecoveryByPosition($_POST['idutilisateur'], $blforigine['Position']+1)[0];
	
	$UtilisateursForm->UtilisateursBLFUpdatePosition($blforigine['idblf'], $blforigine['Position']+1);
	$UtilisateursForm->UtilisateursBLFUpdatePosition($blfdest['idblf'], $blfdest['Position']-1);
	
	$majdatabase = 1;
}

if (ISSET($_POST['BLFHaut']))
{
	//Modification de l'ordre des BLF + enregistrement des informations
	
	$blforigine = $UtilisateursForm->UtilisateursBLFRecoveryById($_POST['BLFHaut'])[0];
	$blfdest = $UtilisateursForm->UtilisateursBLFRecoveryByPosition($_POST['idutilisateur'], $blforigine['Position']-1)[0];
	
	$UtilisateursForm->UtilisateursBLFUpdatePosition($blforigine['idblf'], $blforigine['Position']-1);
	$UtilisateursForm->UtilisateursBLFUpdatePosition($blfdest['idblf'], $blfdest['Position']+1);
	
	$majdatabase = 1;
}

if (ISSET($_POST['NewBLF']))
{
	$utilisateurBLF = $UtilisateursForm->UtilisateursBLFRecoveryByUtilisateur($_POST['idutilisateur']);
	$position = count($utilisateurBLF)+1;
	
	if ($_POST["NewBLFType"] != "") $typeblf = $_POST["NewBLFType"];
		else $typeblf = "";
		if ($_POST["NewBLFEtiquette"] != "") $etiquetteblf = $_POST["NewBLFEtiquette"];
		else $etiquetteblf = "";
		if ($_POST["NewBLFValeur"] != "") $valeurblf = $_POST["NewBLFValeur"];
		else $valeurblf = "";
	
	$UtilisateursForm->UtilisateursBLFInsert($_POST['idutilisateur'],$position,$typeblf,$etiquetteblf,$valeurblf);
	
	$majdatabase = 1;
}



if ($majdatabase == 1)
{
	//On met à jour l'ensemble des champs
	
	$UtilisateursForm->UtilisateursUpdate($_POST['idutilisateur'],$_POST['EditNom'],$_POST['EditExtension'],$_POST['EditTypePoste'],
		$_POST['EditMAC'],$_POST['EditSIPServeur'],$_POST['EditSIPLogin'],$_POST['EditSIPPassword']);
	
	
	$utilisateurBLF = $UtilisateursForm->UtilisateursBLFRecoveryByUtilisateur($_POST['idutilisateur']);
	foreach($utilisateurBLF as $blf)
  {
  	if (ISSET($_POST["EditBLFType".$blf['idblf']]))
  	{
  		if ($_POST["EditBLFType".$blf['idblf']] != "") $typeblf = $_POST["EditBLFType".$blf['idblf']];
  		else $typeblf = "";
  		if ($_POST["EditBLFEtiquette".$blf['idblf']] != "") $etiquetteblf = $_POST["EditBLFEtiquette".$blf['idblf']];
  		else $etiquetteblf = "";
  		if ($_POST["EditBLFValeur".$blf['idblf']] != "") $valeurblf = $_POST["EditBLFValeur".$blf['idblf']];
  		else $valeurblf = "";
  		
  		$UtilisateursForm->UtilisateursBLFUpdate($blf['idblf'],$typeblf,$etiquetteblf,$valeurblf);
  	}
  }
	
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edition poste</title>
    <link rel="stylesheet" href="utilisateurdetail.css">
</head>
<body>

    <img src="../admin/logo/Logo-ldap.png" alt="Logo" class="logo-header">

    <section class="main-section">
        <div class="title-container">
            
            <?php
            	$client = $ClientsForm->ClientsRecoveryById($_POST['idclient'])[0];
            	$utilisateur = $UtilisateursForm->UtilisateursRecoveryById($_POST['idutilisateur'])[0];
            	
            	echo "<h1>Modification de l'utilisateur ".$utilisateur['Nom']." (".$client['Nom'].") </h1>";
            ?>
        </div>

        <div class="table-container">
        	<form method='POST' action="utilisateurdetail.php" style='display:inline;'>
                            
            <table class="table">
                <tbody id="client-list">
                	<?php
                		echo "<input type='hidden' name='idutilisateur' value='$_POST[idutilisateur]'>";
                    echo "<input type='hidden' name='idclient' value='$_POST[idclient]'>";
                    
                    echo "<tr>";
                    
                		if ($utilisateur['Nom'] != "") echo "<td>Nom :<BR><input type='text' name=\"EditNom\" value=\"$utilisateur[Nom]\"></input></td>";
                		else echo "<td>Nom :<BR><input type='text' name='EditNom' value=\"Renseignez le nom de l'utilisateur\"></td>";
                		
                		if ($utilisateur['Extension'] != "") echo "<td>Extension :<BR><input type='text' name='EditExtension' value=\"$utilisateur[Extension]\"></td>";
                		else echo "<td>Extension :<BR><input type='text' name='EditExtension' value=\"Renseignez l'extension\"></td>";
                	
                		echo "<td></td>";
                		echo "</tr>";
                		
                		echo "<tr>";
                		echo "<td>Type de poste:<br><select name='EditTypePoste'>";
                		
                		if ($utilisateur['TypePoste'] == "") echo "<option value='' selected>Non défini</option>";
                		else echo "<option value=''>Non défini</option>";
                		
                		foreach($TypePostes as $type)
                		{
                			if ($utilisateur['TypePoste'] == $type['TypePoste']) echo "<option value=\"$type[TypePoste]\" selected>$type[TypePoste]</option>";
                			else echo "<option value=\"$type[TypePoste]\">$type[TypePoste]</option>";
                		}
                		
                		echo "</select></td>";
                		
                		if ($utilisateur['AdresseMAC'] != "") echo "<td>MAC :<BR><input type='text' name='EditMAC' value=\"$utilisateur[AdresseMAC]\"></td>";
                		else echo "<td>MAC :<BR><input type='text' name='EditMAC' value=\"Renseignez l'adresse MAC\"></td>";
                	
                		echo "<td></td>";
                		echo "</tr>";
                		
                		
                		echo "<tr>";
                    
                		if ($utilisateur['SIPServeur'] != "") echo "<td>SRV SIP :<BR><input type='text' name='EditSIPServeur' value=\"$utilisateur[SIPServeur]\"></td>";
                		else echo "<td>SRV SIP :<BR><input type='text' name='EditSIPServeur' value=\"Renseignez le serveur SIP\"></td>";
                		
                		if ($utilisateur['SIPLogin'] != "") echo "<td>SIP Login :<BR><input type='text' name='EditSIPLogin' value=\"$utilisateur[SIPLogin]\"></td>";
                		else echo "<td>SIP Login :<BR><input type='text' name='EditSIPLogin' value=\"Renseignez le login SIP\"></td>";
                		
                		if ($utilisateur['SIPPassword'] != "") echo "<td>SIP Password :<BR><input type='text' name='EditSIPPassword' value=\"$utilisateur[SIPPassword]\"></td>";
                		else echo "<td>SIP Password :<BR><input type='text' name='EditSIPPassword' value=\"Renseignez le mot de passe SIP\"></td>";
                	
                		echo "</tr>";
                		
                	?>
                </tbody>
            </table>
            
            <section class="table-section">
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Label</th>
                        <th>Valeur</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="contact-list">
                	
                	<?php
                		$utilisateurBLF = $UtilisateursForm->UtilisateursBLFRecoveryByUtilisateur($_POST['idutilisateur']);
                		
                		foreach($utilisateurBLF as $blf)
                		{
                			echo "<tr>";
                			echo "<td><input type='text' name='EditBLFType".$blf['idblf']."' value=\"$blf[TypeBLF]\"></td>";
                			echo "<td><input type='text' name='EditBLFEtiquette".$blf['idblf']."' value=\"$blf[Etiquette]\"></td>";
                			echo "<td><input type='text' name='EditBLFValeur".$blf['idblf']."' value=\"$blf[Valeur]\"></td>";
                			echo "<td id='BtnAction'>";
                			if ($blf['Position'] != 1) echo "<button type='submit' name='BLFHaut' value='$blf[idblf]'>Haut</button>";
                			if ($blf['Position'] != count($utilisateurBLF)) echo "<button type='submit' name='BLFBas' value='$blf[idblf]'>&nbsp;Bas</button>";
                			echo "</td>";
	                		echo "</tr>";                			
                			
                		}   
                		
              		  echo "<tr>";
              			echo "<td><input type='text' name='NewBLFType' value=\"\"></td>";
              			echo "<td><input type='text' name='NewBLFEtiquette' value=\"\"></td>";
              			echo "<td><input type='text' name='NewBLFValeur' value=\"\"></td>";
              			echo "<td id='BtnAction'>";
              			echo "<button type='submit' name='NewBLF' value=''>Ajouter</button>";
              			echo "</td>";
                		echo "</tr>";        	
                	
                	?>
                	
            		</tbody>
            </table>
          </section>
        	</form>
        </div>
    </section>

    <a href="javascript:history.back()" class="back-button">Revenir en arrière</a>
    <!-- <script src="clientlist.js"></script> -->
</body>
</html>
