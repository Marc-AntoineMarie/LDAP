<?php
require_once 'encryption.php';

if ($argc != 2) {
    die("Usage: php encrypt_password.php <password>\n");
}

$password = $argv[1];
echo "Mot de passe chiffré : ENC:" . Encryption::encrypt($password) . "\n";
