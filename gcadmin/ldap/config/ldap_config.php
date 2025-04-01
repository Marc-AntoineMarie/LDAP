<?php
require_once __DIR__ . '/../../utils/encryption.php';

// Protection contre les déclarations multiples
if (!function_exists('loadEnvSecrets')) {
    /**
     * Charge et déchiffre les variables d'environnement
     */
    function loadEnvSecrets() {
        error_log("Config LDAP - Début du chargement des secrets");
        
        $envFile = __DIR__ . '/../../config/.env';
        if (!file_exists($envFile)) {
            $error = 'Fichier .env manquant : ' . $envFile;
            error_log("Config LDAP - " . $error);
            throw new Exception($error);
        }
        
        error_log("Config LDAP - Fichier .env trouvé");
        
        $env = [];
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            error_log("Config LDAP - Traitement de la ligne : " . substr($line, 0, strpos($line, '=')));
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                if (strpos($value, 'ENC:') === 0) {
                    error_log("Config LDAP - Déchiffrement de la valeur pour : " . $key);
                    try {
                        $value = Encryption::decrypt(substr($value, 4));
                        error_log("Config LDAP - Déchiffrement réussi pour : " . $key);
                    } catch (Exception $e) {
                        error_log("Config LDAP - Erreur de déchiffrement pour " . $key . " : " . $e->getMessage());
                        throw new Exception("Erreur de déchiffrement pour " . $key . " : " . $e->getMessage());
                    }
                }
                $env[$key] = $value;
            }
        }
        
        error_log("Config LDAP - Secrets chargés avec succès");
        return $env;
    }
}

error_log("Config LDAP - Début du chargement de la configuration");

try {
    // Charger les secrets
    $secrets = loadEnvSecrets();
    error_log("Config LDAP - Secrets chargés");
    
    $config = [
        'server' => [
            'host' => '141.94.251.137',
            'name' => 'vps-8a03f373',
            'protocol' => 'ldap',
            'port' => 389
        ],
        'admin' => [
            'dn' => 'cn=admin,dc=test,dc=gcservice,dc=fr',
            'password' => $secrets['LDAP_ADMIN_PASSWORD']
        ],
        'base' => [
            'dn' => 'dc=test,dc=gcservice,dc=fr'
        ],
        'ssh' => [
            'host' => '141.94.251.137',
            'user' => 'root',
            'password' => $secrets['LDAP_SSH_PASSWORD'],
            'ldif_path' => '/home/debian',
            'port' => 673  // Port SSH mis à jour
        ]
    ];
    
    error_log("Config LDAP - Configuration complète : " . print_r($config, true));
    return $config;
    
} catch (Exception $e) {
    error_log("Config LDAP - Erreur fatale : " . $e->getMessage());
    error_log("Config LDAP - Trace : " . $e->getTraceAsString());
    throw $e;
}
