<?php
require_once '../database/db.php';
include '../database/clients_request.php';
include '../database/utilisateurs_request.php';
include '../database/postes_request.php';
include '../classes/provisionning.php';

if (!isset($pdo)) {
    die("Erreur : La connexion PDO n'est toujours pas initialisée.");
}else{
    //Je commente outrageusement ton caca
    //echo 'annuaire.php -> ok caca | ';
}

$idclient = $_POST['idclient'];
$client = $ClientsForm->ClientsRecoveryById($idclient)[0];

//echo $_POST['EditSIPServeur']."//".$_POST['EditSIPLogin']."//".$_POST['EditSIPPassword']."<BR>";
	

$majdatabase = 0;
$idutilisateur = $_POST['idutilisateur'];

if (ISSET($_POST['Enregistrer'])) $majdatabase = 1;


if (ISSET($_POST['AutoBLF'])) 
{
	//Génération automatique des BLF
	$Provisionning->AutoBLF($UtilisateursForm, $_POST['idutilisateur']);
		
}


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
	
	$dataprov["Nom"] = $_POST['EditNom'];
	$dataprov["Extension"] = $_POST['EditExtension'];
	$dataprov["ServeurSIP"] = $_POST['EditSIPServeur'];
	$dataprov["LoginSIP"] = $_POST['EditSIPLogin'];
	$dataprov["PasswordSIP"] = $_POST['EditSIPPassword'];
	$dataprov["Plateforme"] = $client["Plateforme"];
	$dataprov["idPartenaire"] = $client["partenaires_idpartenaires"];
	
	//echo $dataprov["ServeurSIP"]."//".$dataprov["LoginSIP"]."//".$dataprov["PasswordSIP"];
	
	$UtilisateursForm->UtilisateursUpdate($_POST['idutilisateur'],$_POST['EditNom'],$_POST['EditExtension'],$_POST['EditTypePoste'],
		strtolower($_POST['EditMAC']),$_POST['EditSIPServeur'],$_POST['EditSIPLogin'],$_POST['EditSIPPassword'],$_POST['EditSN']);
	
	$utilisateurBLF = $UtilisateursForm->UtilisateursBLFRecoveryByUtilisateur($_POST['idutilisateur']);
	$posblf = 0;
	foreach($utilisateurBLF as $blf)
  {
  	if (ISSET($_POST["EditBLFType".$blf['idblf']]))
  	{
  		$posblf++;
  		if ($_POST["EditBLFType".$blf['idblf']] != "") $typeblf = $_POST["EditBLFType".$blf['idblf']];
  		else $typeblf = "";
  		if ($_POST["EditBLFEtiquette".$blf['idblf']] != "") $etiquetteblf = $_POST["EditBLFEtiquette".$blf['idblf']];
  		else $etiquetteblf = "";
  		if ($_POST["EditBLFValeur".$blf['idblf']] != "") $valeurblf = $_POST["EditBLFValeur".$blf['idblf']];
  		else $valeurblf = "";
  		
  		$datablf[$posblf]["TypeBLF"] = $typeblf;
  		$datablf[$posblf]["EtiquetteBLF"] = $etiquetteblf;
  		$datablf[$posblf]["ValeurBLF"] = $valeurblf;
  		
  		$UtilisateursForm->UtilisateursBLFUpdate($blf['idblf'],$typeblf,$etiquetteblf,$valeurblf);
  	}
  }
	
	
	$typetel = $TypePostesForm->TypePostesCategoriesRecovery($_POST['EditTypePoste'])[0];
	
	
	if ($_POST['EditMAC'] == "")
	{
		echo "Impossible de générer le fichier de provisionning, l'adresse MAC n'est pas renseignée.";
	}else{
		//Vérification de l'adresse MAC si l'on est pas sur un poste qui accepte plusieurs utilisateurs
		
		$mactemp = strtolower($_POST['EditMAC']);

		$verifmulti = 1;
		switch ($typetel["PosteCategorie"])
		{
			case "YeaW70":
				$verifmulti = 0;
				break;                			
		}
		
		$verif = $UtilisateursForm->UtilisateursVerificationMAC($mactemp, $_POST['idutilisateur']);
		
		//On vérifie que l'adresse MAC ne soit pas attribuée ailleurs
		if ((ISSET($verif[0]))&&($verifmulti == 1))
		{
			$util = $verif[0]['Nom'];
			$clientnom = $ClientsForm->ClientsRecoveryById($verif[0]['clients_idclients'])[0]['Nom'];
			echo "Impossible de générer le fichier de provisionning, cette adresse MAC est attribuée à $util ($clientnom).";
		}else{
			$Provisionning->GenerateProvisionningFiles($typetel["PosteCategorie"], $mactemp,$dataprov,$datablf,$UtilisateursForm);		
		}
	}
	
	
	
	
	
}

if (ISSET($_POST['NewUserValidation']))
{
	//Création d'un nouvel utilisateur
	$idutilisateur = $UtilisateursForm->UtilisateursInsert($_POST['EditNom'],$_POST['EditExtension'],$_POST['EditTypePoste'],
	strtolower($_POST['EditMAC']),$_POST['EditSN'],$_POST['EditSIPServeur'],$_POST['EditSIPLogin'],$_POST['EditSIPPassword'],$idclient);
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
            	
            	if (ISSET($_POST['NewUser'])) echo "<h1>Création d'un nouvel utilisateur (".$client['Nom'].")</h1>";
            	else{
            		$utilisateur = $UtilisateursForm->UtilisateursRecoveryById($idutilisateur)[0];
            		
            		if ($typetel == "") $typetel = $TypePostesForm->TypePostesCategoriesRecovery($utilisateur['TypePoste'])[0];
            		
            		echo "<h1>Modification de l'utilisateur ".$utilisateur['Nom']." (".$client['Nom'].") </h1>";
            	} 
            ?>
        </div>

        <div class="table-container">
        	<form method='POST' action="utilisateurdetail.php" style='display:inline;'>
                            
            <table class="table">
                <tbody id="client-list">
                	<?php
                		echo "<input type='hidden' name='idutilisateur' value='$idutilisateur'>";
                    echo "<input type='hidden' name='idclient' value='$_POST[idclient]'>";
                    
                    echo "<tr>";
                    
                		if ($utilisateur['Nom'] != "") echo "<td>Nom :<BR><input type='text' name=\"EditNom\" value=\"$utilisateur[Nom]\"></input></td>";
                		else echo "<td>Nom :<BR><input type='text' name='EditNom' value=\"\"></td>";
                		
                		if ($utilisateur['Extension'] != "") echo "<td>Extension :<BR><input type='text' name='EditExtension' value=\"$utilisateur[Extension]\"></td>";
                		else echo "<td>Extension :<BR><input type='text' name='EditExtension' value=\"\"></td>";
                	
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
                		else echo "<td>MAC :<BR><input type='text' name='EditMAC' value=\"\"></td>";
                	

						if ($utilisateur['SerialNumber'] != "") echo "<td>N° série :<BR><input type='text' name='EditSN' value=\"$utilisateur[SerialNumber]\"></td>";
                		else echo "<td>N° série :<BR><input type='text' name='EditSN' value=\"\"></td>";
                		echo "</tr>";
                		
                		echo "<tr>";
                    
                		if ($utilisateur['SIPServeur'] != "") echo "<td>SRV SIP :<BR><input type='text' name='EditSIPServeur' value=\"$utilisateur[SIPServeur]\"></td>";
                		else echo "<td>SRV SIP :<BR><input type='text' name='EditSIPServeur' value=\"".$client['PlateformeURL']."\"></td>";
                		
                		
                		if ($utilisateur['SIPLogin'] != "") echo "<td>SIP Login :<BR><input type='text' name='EditSIPLogin' value=\"$utilisateur[SIPLogin]\"></td>";
                		else echo "<td>SIP Login :<BR><input type='text' name='EditSIPLogin' value=\"\"></td>";
                		
                		if ($utilisateur['SIPPassword'] != "") echo "<td>SIP Password :<BR><input type='password' name='EditSIPPassword' value=\"$utilisateur[SIPPassword]\"></td>";
                		else echo "<td>SIP Password :<BR><input type='password' name='EditSIPPassword' value=\"\"></td>";
                	
                		echo "</tr>";
                		
                		if (ISSET($_POST['NewUser']))
                		{
                			echo "<tr>";
                			echo "<td colspan=3><form method='POST' action=\"../utilisateurdetail/utilisateurdetail.php\" style='display:inline;'>
                            <input type='hidden' name='idclient' value='$idclient'>
                            <center><button name='NewUserValidation' class='action-button' type='submit'>Créer</button></center>
                           </form></td>";
                			echo "</tr>";
                			
                		}
                		
                	?>
                </tbody>
            </table>
            
            
                
                	
                	<?php
                		//Gestion des équipements pour lesquels on ne gère pas les BLF (DECT)
                		$affichblf = 1;
                		switch ($typetel["PosteCategorie"])
                		{
                			case "YeaW70":
                				$affichblf = 0;
                				break;                			
                		}
                		
                		if ((ISSET($_POST['NewUser']) == false)&&($affichblf == 1))
                		{
                			
                			echo "<section class='table-section'>
							            <table>
							                <thead>
							                    <tr>
							                        <th>Type</th>
							                        <th>Label</th>
							                        <th>Valeur</th>
							                        <th>Actions</th>
							                    </tr>
							                </thead>
							                <tbody id='contact-list'>";
                			
	                		$utilisateurBLF = $UtilisateursForm->UtilisateursBLFRecoveryByUtilisateur($idutilisateur);
	                		
	                		foreach($utilisateurBLF as $blf)
	                		{
	                			echo "<tr>";
	                			
	                			
	                			echo "<td>".$Provisionning->AffichTypeBLF("EditBLFType".$blf['idblf'], $blf['TypeBLF'])."</td>";
	                			
	                			
	                			echo "<td><input type='text' name='EditBLFEtiquette".$blf['idblf']."' value=\"$blf[Etiquette]\"></td>";
	                			echo "<td><input type='text' name='EditBLFValeur".$blf['idblf']."' value=\"$blf[Valeur]\"></td>";
	                			echo "<td id='BtnAction'>";
	                			if ($blf['Position'] != 1) echo "<button type='submit' name='BLFHaut' value='$blf[idblf]'>Haut</button>";
	                			if ($blf['Position'] != count($utilisateurBLF)) echo "<button type='submit' name='BLFBas' value='$blf[idblf]'>&nbsp;Bas</button>";
	                			echo "</td>";
		                		echo "</tr>";                			
	                			
	                		}   
	                		
	              		  echo "<tr>";
	              			echo "<td>".$Provisionning->AffichTypeBLF("NewBLFType")."</td>";
	              			echo "<td><input type='text' name='NewBLFEtiquette' value=\"\"></td>";
	              			echo "<td><input type='text' name='NewBLFValeur' value=\"\"></td>";
	              			echo "<td id='BtnAction'>";
	              			echo "<button type='submit' name='NewBLF' value=''>Ajouter</button>";
	              			echo "</td>";
	                		echo "</tr>";
	                		
                		}
                		
                		if (ISSET($_POST['NewUser']) == false)
                		{
	                		echo "</tbody>
									            </table>
									            
									            <table>
									            	<tbody id='client-list'>
									            		<tr>
									            			<td id='BtnAction'>
									            				<button type='submit' name='Enregistrer'' class='action-button''>Enregistrer</button>";
									     if ($affichblf == 1)echo "<button type='submit' name='AutoBLF' class='action-button''>Auto-BLF</button>";
									     
									     echo "</td>
									            		</tr>
									            	</tbody>
									            </table>
									            </section>";
									  }
                	?>
                	
            
            
          
        	</form>
        </div>
    </section>

		<?php
			echo "<form method='POST' action=\"../clientdetail/clientdetail.php\" style='display:inline;'>
             <input type='hidden' name='idclient' value='$idclient'>
             <button name='RetourArriere' class='back-button' type='submit'>Revenir en arrière</button>
             </form>";
    ?>
</body>
</html>
