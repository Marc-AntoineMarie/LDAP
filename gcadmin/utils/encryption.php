<?php
/**
 * Classe pour gérer le chiffrement/déchiffrement des données sensibles
 */
class Encryption {
    // Clé statique pour le chiffrement (32 caractères)
    private static $key = "GCAdmin_LDAP_Secure_Key_2025_02_06";
    private static $cipher = "aes-256-cbc";
    
    /**
     * Chiffre une chaîne
     */
    public static function encrypt($data) {
        error_log("Encryption - Début du chiffrement");
        
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $encrypted = openssl_encrypt($data, self::$cipher, self::$key, 0, $iv);
        
        if ($encrypted === false) {
            $error = "Échec du chiffrement : " . openssl_error_string();
            error_log("Encryption - " . $error);
            throw new Exception($error);
        }
        
        error_log("Encryption - Chiffrement réussi");
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Déchiffre une chaîne
     */
    public static function decrypt($data) {
        error_log("Encryption - Début du déchiffrement");
        
        try {
            $data = base64_decode($data);
            if ($data === false) {
                throw new Exception("Données base64 invalides");
            }
            
            $ivlen = openssl_cipher_iv_length(self::$cipher);
            $iv = substr($data, 0, $ivlen);
            $encrypted = substr($data, $ivlen);
            
            $decrypted = openssl_decrypt($encrypted, self::$cipher, self::$key, 0, $iv);
            if ($decrypted === false) {
                throw new Exception("Échec du déchiffrement : " . openssl_error_string());
            }
            
            error_log("Encryption - Déchiffrement réussi");
            return $decrypted;
            
        } catch (Exception $e) {
            error_log("Encryption - Erreur de déchiffrement : " . $e->getMessage());
            throw $e;
        }
    }
}
