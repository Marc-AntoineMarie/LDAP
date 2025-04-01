<?php

	class Provisionning
	{
		private $Categorie;
		private $AdresseMAC;
		private $tabProvisionning;
		private $tabBLF;
		private $srvProvisionning = "54.36.189.50";
		private $UtilisateursForm;

		function __construct() {
    }

		function GenerateProvisionningFiles($categorie,$mac,$dataprov,$datablf,$UtilForm)
		{
			$this->Categorie = $categorie;
			$this->AdresseMAC = $mac;
			$this->tabProvisionning = $dataprov;
			$this->tabBLF = $datablf;
			$this->UtilisateursForm = $UtilForm;

			switch ($categorie)
			{
				case "YeaT5X":
				case "YeaT4X":
					$this->GenerateYealinkFiles("Fixe");
					break;
					
				case "YeaW70":
					$this->GenerateYealinkFiles("DECT");
					break;
					
				case "Fanvil":
					$this->GenerateFanvilFiles();
					break;
			}
    }


    function GenerateYealinkFiles($typetel="Fixe")
    {
    	switch ($this->tabProvisionning['Plateforme'])
    	{
    		case "Wazo":
    			$this->GenerateYealinkWazoFiles($typetel);
    			break;
    			
    		case "OVH":
    			$this->GenerateYealinkOVHFiles($typetel);
    			break;
    			
    		case "Yeastar":
    			$this->GenerateYealinkYeastarFiles($typetel);
    			break;
    	}
    }

    function GenerateYealinkWazoFiles($typetel="Fixe")
    {
    	//Génération du fichier de boot
			$this->GenerateYealinkBoot();

		  //Génération du fichier .cfg
		  $this->GenerateYealinkConfiguration("5566","*8",$typetel);
    }
    
    function GenerateYealinkOVHFiles($typetel="Fixe")
    {
    	//Génération du fichier de boot
		$this->GenerateYealinkBoot();

		//Génération du fichier .cfg
		$this->GenerateYealinkConfiguration("5060","*11*",$typetel);
    }
    
    function GenerateYealinkYeastarFiles($typetel="Fixe")
    {
    	//Génération du fichier de boot
			$this->GenerateYealinkBoot();

		  //Génération du fichier .cfg
		  $this->GenerateYealinkConfiguration("5060","*04",$typetel);
    }
    
    function GenerateFanvilFiles()
    {
    	switch ($this->tabProvisionning['Plateforme'])
    	{
    		case "Wazo":
    			$this->GenerateFanvilConfiguration("5566","*8");
    			break;
    			
    		case "OVH":
    			$this->GenerateFanvilConfiguration("5060","*11*");
    			break;
    			
    		case "Yeastar":
    			$this->GenerateFanvilConfiguration("5060","*4");
    			break;
    	}
    }
    
    /*-------------------------------------------------------------------------------------------------------*/
    /*----------------------------- Fonctions de génération de bouts de fichiers ----------------------------*/
    /*-------------------------------------------------------------------------------------------------------*/
    
    function GenerateYealinkBoot()
    {
    	$fichier = fopen("../../Autoprov/".$this->AdresseMAC.".boot", 'w+b');
			$txt = "#!version:1.0.0.1\r\n";
			$txt .= "## The header above must appear as-is in the first line\r\n";
			$txt .= "##[$MODEL]include:config <xxx.cfg>\r\n";
			$txt .= "##[$MODEL,$MODEL]include:config \"xxx.cfg\"\r\n";
			$txt .= "include:config <".$this->AdresseMAC.".cfg>\r\n";
			$txt .= "include:config \"".$this->AdresseMAC.".cfg\"\r\n";
			$txt .= "overwrite_mode = 1\r\n";
			$txt .= "specific_model.excluded_mode=0\r\n";

		  fwrite($fichier, $txt);
		  fclose($fichier);
    }
    
    function GenerateYealinkConfiguration($port='5060',$extension="*8", $typetel="Fixe")
    {
    	$fichier = fopen("../../Autoprov/".$this->AdresseMAC.".cfg", 'w+b');
			
			$txt = "#!version:1.0.0.1\r\n";
			
			if ($typetel != "DECT")
			{
				$txt .= "account.1.auth_name = ".$this->tabProvisionning['LoginSIP']."\r\n";
			  $txt .= "account.1.display_name = ".$this->tabProvisionning['Nom']."\r\n";
			  $txt .= "account.1.enable = 1\r\n";
			  $txt .= "account.1.label = ".$this->tabProvisionning['Nom']."\r\n";
			 	$txt .= "account.1.sip_server.1.address = ".$this->tabProvisionning['ServeurSIP']."\r\n";
			 	$txt .= "account.1.sip_server.1.port = ".$port."\r\n";
			 	$txt .= "account.1.user_name = ".$this->tabProvisionning['LoginSIP']."\r\n";
			 	$txt .= "account.1.password = ".$this->tabProvisionning['PasswordSIP']."\r\n";
			}else{
				//Gestion des multiples comptes, on recherche tous les utilisateurs avec cette MAC
				$txt .= "custom.handset.date_format = 3\r\n";
				$comptes = $this->UtilisateursForm->UtilisateursRecoveryByMAC($this->AdresseMAC);
				$idcompte = 1;
				foreach($comptes as $compte)
			  {
			  	$txt .= "account.".$idcompte.".auth_name = ".$compte['SIPLogin']."\r\n";
				  $txt .= "account.".$idcompte.".display_name = ".$compte['Nom']."\r\n";
				  $txt .= "account.".$idcompte.".enable = 1\r\n";
				  $txt .= "account.".$idcompte.".label = ".$compte['Nom']."\r\n";
				 	$txt .= "account.".$idcompte.".sip_server.1.address = ".$compte['SIPServeur']."\r\n";
				 	$txt .= "account.".$idcompte.".sip_server.1.port = ".$port."\r\n";
				 	$txt .= "account.".$idcompte.".user_name = ".$compte['SIPLogin']."\r\n";
				 	$txt .= "account.".$idcompte.".password = ".$compte['SIPPassword']."\r\n";
			  	$txt .= "handset.".$idcompte.".name = ".$compte['Nom']."\r\n";
			  	
			  	
			  	$idcompte++;
			  }
				
				
			}
		  
			$txt .= "lang.gui = French\r\n";
			$txt .= "lang.wui = French\r\n";
			
			if ($typetel == "Fixe")
			{
				$i = 1;
				while(ISSET($this->tabBLF[$i]))
				{
					switch ($this->tabBLF[$i]["TypeBLF"])
					{
						case "Ligne":
							$txt .= "linekey.".$i.".label = ".$this->tabBLF[$i]["EtiquetteBLF"]."\r\n";
							$txt .= "linekey.".$i.".line = 1\r\n";
							$txt .= "linekey.".$i.".value = 0\r\n";
							break;
						case "BLF":
							$txt .= "linekey.".$i.".label = ".$this->tabBLF[$i]["EtiquetteBLF"]."\r\n";
							$txt .= "linekey.".$i.".line = 1\r\n";
							$txt .= "linekey.".$i.".type = 16\r\n";
							$txt .= "linekey.".$i.".extension = ".$extension."\r\n";
							$txt .= "linekey.".$i.".value = ".$this->tabBLF[$i]["ValeurBLF"]."\r\n";
							break;
						case "Numerotation":
							$txt .= "linekey.".$i.".label = ".$this->tabBLF[$i]["EtiquetteBLF"]."\r\n";
							$txt .= "linekey.".$i.".line = 0\r\n";
							$txt .= "linekey.".$i.".type = 13\r\n";
							$txt .= "linekey.".$i.".value = ".$this->tabBLF[$i]["ValeurBLF"]."\r\n";
							break;

					}
					$i++;
				}
			}

			$txt .= "local_time.dhcp_time = 1\r\n";
			$txt .= "local_time.time_zone = +1\r\n";
			$txt .= "local_time.time_zone_name = France(Paris)\r\n";
			$txt .= "phone_setting.custom_headset_mode_status = 1\r\n";
			$txt .= "transfer.dsskey_deal_type = 1\r\n";
			$txt .= "static.security.user_password = admin:UGCI8376\r\n";
			if ($typetel == "Fixe")
			{
				$txt .= "wallpaper_upload.url = http://54.36.189.50/Autoprov/LogoPart".$this->tabProvisionning['idPartenaire'].".png\r\n";
				$txt .= "phone_setting.lcd_logo.mode = 2\r\n";
				$txt .= "phone_setting.backgrounds = LogoPart".$this->tabProvisionning['idPartenaire'].".png\r\n";
			}
			$txt .= "static.auto_provision.custom.protect = 0\r\n";
			$txt .= "static.auto_provision.custom.sync = 0\r\n";
			$txt .= "static.auto_provision.server.url = http://".$this->srvProvisionning."/Autoprov/\r\n";
			$txt .= "static.auto_provision.server.username = admin\r\n";
			$txt .= "static.auto_provision.server.password = UGCI8376\r\n";
			$txt .= "static.auto_provision.repeat.enable = 1\r\n";
			$txt .= "static.auto_provision.repeat.minutes = 2\r\n";

    	fwrite($fichier, $txt);
		  fclose($fichier);
    }
    
    function GenerateFanvilConfiguration($port="5060",$extension="*8")
    {
    	$fichier = fopen("../../Autoprov/".$this->AdresseMAC.".txt", 'w+b');
    	$txt = "<<VOIP CONFIG FILE>>Version:2.0000000000\r\n";
		  $txt .= "<SIP CONFIG MODULE>\r\n";
		  $txt .= "--SIP Line List--  :\r\n";
		  $txt .= "SIP1 Phone Number       :".$this->tabProvisionning['LoginSIP']."\r\n";
		  $txt .= "SIP1 Display Name       :".$this->tabProvisionning['Nom']."\r\n";
		  $txt .= "SIP1 Sip Name       :\r\n";
		  $txt .= "SIP1 Register Addr       :".$this->tabProvisionning['ServeurSIP']."\r\n";
		  $txt .= "SIP1 Register Port       :$port\r\n";
		  $txt .= "SIP1 Register User       :".$this->tabProvisionning['LoginSIP']."\r\n";
		  $txt .= "SIP1 Register Pswd       :".$this->tabProvisionning['PasswordSIP']."\r\n";
		  $txt .= "SIP1 Register TTL       :3600\r\n";
		  $txt .= "SIP1 Enable Reg       :1\r\n";

		  $txt .= "<PHONE FEATURE MODULE>\r\n";
		  $txt .= "--Display Input--  :\r\n";
		  $txt .= "Default Language   :fr\r\n";
		  $txt .= "--DateTime Config--:\r\n";
		  $txt .= "SNTP Server        :0.pool.ntp.org\r\n";
		  $txt .= "Second SNTP Server :time.nist.gov\r\n";
		  $txt .= "Time Zone          :4\r\n";
		  $txt .= "Time Zone Name     :UTC+1\r\n";
		  $txt .= "SNTP Timeout       :9600\r\n";
		  $txt .= "DST Type           :0\r\n";
		  $txt .= "DST Location       :10\r\n";
		  $txt .= "DST Rule Mode      :0\r\n";
		  $txt .= "<DSSKEY CONFIG MODULE>\r\n";
		 	$txt .= "--Sidekey Config1--:\r\n";
		  
		  $i = 1;
			while(ISSET($this->tabBLF[$i]))
			{
				
				if ($i <= 2)
				{
					//Touches de ligne à mettre sur les sidekeys
					$txt .= "Fkey".$i." Type               :2\r\n";
					$txt .= "Fkey".$i." Value              :AUTO\r\n";
					$txt .= "Fkey".$i." Title               :".$this->tabBLF[$i]["EtiquetteBLF"]."\r\n";
					$txt .= "Fkey".$i." ICON              :Green\r\n";
				}else{
					
					$itemp = $i - 2;
					
					if (($itemp % 6) == 1)
					{
						//On passe à la page suivante
						$page = (($itemp-1) / 6)+1;
						$txt .= "--Dsskey Config".$page."--:\r\n";
					}
					
					$numblf = $itemp % 6;
					
					if ($numblf == 0) $numblf = 6;
					
					switch ($this->tabBLF[$i]["TypeBLF"])
					{
						case "Ligne":
							$txt .= "Fkey".$numblf." Type               :2\r\n";
							$txt .= "Fkey".$numblf." Value              :AUTO\r\n";
							$txt .= "Fkey".$numblf." Title               :".$this->tabBLF[$i]["EtiquetteBLF"]."\r\n";
							$txt .= "Fkey".$numblf." ICON              :Green\r\n";
							break;
						case "BLF":
							$txt .= "Fkey".$numblf." Type               :1\r\n";
							$txt .= "Fkey".$numblf." Value              :".$this->tabBLF[$i]["ValeurBLF"]."@1/bc".$extension."\r\n";
							$txt .= "Fkey".$numblf." Title               :".$this->tabBLF[$i]["EtiquetteBLF"]."\r\n";
							$txt .= "Fkey".$numblf." ICON              :Green\r\n";
							break;
						case "Numerotation":
							$txt .= "Fkey".$numblf." Type               :1\r\n";
							$txt .= "Fkey".$numblf." Value              :".$this->tabBLF[$i]["ValeurBLF"]."@1/f\r\n";
							$txt .= "Fkey".$numblf." Title               :".$this->tabBLF[$i]["EtiquetteBLF"]."\r\n";
							$txt .= "Fkey".$numblf." ICON              :Green\r\n";
							break;

					}
					
				}
				
				$i++;
			}
    	
    	$txt .= "<MMI CONFIG MODULE>\r\n";
		  $txt .= "--MMI Account--    :\r\n";
		  $txt .= "Account1 Name       :admin\r\n";
		  $txt .= "Account1 Password       :UGCI8376\r\n";
		  $txt .= "Account1 Level       :10\r\n";
    	
    	$txt .= "<AUTOUPDATE CONFIG MODULE>\r\n";
    	$txt .= "Default Username   :admin\r\n";
    	$txt .= "Default Password   :UGCI8376\r\n";
    	$txt .= "Download CommonConf:1\r\n";
    	$txt .= "Save Provision Info:0\r\n";
    	$txt .= "Check FailTimes    :1\r\n";
    	
    	$txt .= "Flash Server IP       :http://".$this->srvProvisionning."/Autoprov/\r\n";
    	$txt .= "Flash File Name       :".$this->AdresseMAC.".txt\r\n";
    	$txt .= "Flash Protocol       :4\r\n";
    	$txt .= "Flash Mode       :2\r\n";
    	$txt .= "Flash Interval       :0.01\r\n";
    	$txt .= "update PB Interval :720\r\n";
    	$txt .= "AP Config Priority :0\r\n";
    	
    	$txt .= "<<END OF FILE>>";
    	
    	fwrite($fichier, $txt);
		  fclose($fichier);
    }
    
    /*-------------------------------------------------------------------------------------------------------*/
    /*------------------------- Fonctions de modification des infos de provisionning ------------------------*/
    /*-------------------------------------------------------------------------------------------------------*/

		function AutoBLF($UtilisateursForm, $idutilisateur)
		{
			$idclient = $UtilisateursForm->UtilisateursRecoveryById($idutilisateur)[0]["clients_idclients"];
	
			//Suppression des anciennes BLF
			$utilisateurs = $UtilisateursForm->UtilisateursBLFDeleteAll($idutilisateur);
			
			//Ajout des nouvelles
			$UtilisateursForm->UtilisateursBLFInsert($idutilisateur,1,"Ligne","Ligne 1","");
			$UtilisateursForm->UtilisateursBLFInsert($idutilisateur,2,"Ligne","Ligne 2","");
			
			$utilisateurs = $UtilisateursForm->UtilisateursRecoveryByClient($idclient, "Extension ASC");
			$position = 3;
			foreach($utilisateurs as $utilisateur)
			{
				if ($idutilisateur != $utilisateur['idutilisateurs'])
				{
					$UtilisateursForm->UtilisateursBLFInsert($idutilisateur,$position,"BLF",$utilisateur['Nom'],$utilisateur['Extension']);
					$position++;
				}
			}
			
		}
		
		function AffichTypeBLF($nomselect, $typeact="")
		{
			$txt = "<SELECT name='$nomselect'>";
		   		
  		if ($typeact == "") $txt .= "<option value='' selected>Aucune</option>";
  		else $txt .= "<option value=''>Aucune</option>";
  		
  		if ($typeact == "Ligne") $txt .= "<option value='Ligne' selected>Ligne</option>";
  		else $txt .= "<option value='Ligne'>Ligne</option>";
  		
  		if ($typeact == "BLF") $txt .= "<option value='BLF' selected>BLF</option>";
  		else $txt .= "<option value='BLF'>BLF</option>";
  		
  		if ($typeact == "Numerotation") $txt .= "<option value='Numerotation' selected>Num. rapide</option>";
  		else $txt .= "<option value='Numerotation'>Num. rapide</option>";
			
			$txt .= "</SELECT>";
			
			return $txt;
		}

	}

	$Provisionning = new Provisionning();

?>