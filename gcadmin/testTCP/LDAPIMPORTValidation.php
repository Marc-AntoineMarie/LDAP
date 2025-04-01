<?php
	
	$test = file_get_contents($_FILES["LDAPIMPORT"]["tmp_name"]);
	$contacts = explode("\r",$test);
	
	$i = 0;
	
	while(ISSET($contacts[$i]))
	{
		$contact = explode (";",$contacts[$i]);
		$uid = str_replace(" ", "", $contact[0])."$i";
		$tel = str_replace(" ", "", $contact[1]);
		$tel = str_replace(".", "", $tel);
		
		
		echo "dn: uid=$uid,ou=".$_POST['LDAPName'].",dc=test,dc=gcservice,dc=fr<BR>";
		echo "uid: ".trim($contact[0])."<BR>";
		echo "objectClass: inetOrgPerson<BR>";
		echo "sn: ".trim($contact[0])."<BR>";
		echo "employeeNumber: $tel<BR>";
		echo "cn: ".trim($contact[0])."<BR><BR>";

		$i++;
	}

?>