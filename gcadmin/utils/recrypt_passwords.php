<?php
require_once 'encryption.php';

// Les mots de passe en clair (ceux que vous aviez utilisés)
$passwords = [
    'LDAP_ADMIN_PASSWORD' => '!@DICY644hs',
    'LDAP_SSH_PASSWORD' => '!@DICY644hs'
];

// Chiffrer les mots de passe
$encrypted = [];
foreach ($passwords as $key => $password) {
    $encrypted[$key] = 'ENC:' . Encryption::encrypt($password);
}

// Générer le contenu du fichier .env
$envContent = '';
foreach ($encrypted as $key => $value) {
    $envContent .= "$key=$value\n";
}

// Écrire dans le fichier .env
file_put_contents(__DIR__ . '/../config/.env', $envContent);

echo "Mots de passe rechiffrés avec succès :\n\n";
echo $envContent;
