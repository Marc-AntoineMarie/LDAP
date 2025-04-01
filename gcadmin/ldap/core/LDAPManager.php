<?php
require_once __DIR__ . '/../config/ldap_config.php';
require_once __DIR__ . '/../templates/ldif_templates.php';

/**
 * Gestionnaire principal LDAP
 */
class LDAPManager {
    private $config;
    private $ldapConn;
    private $sshConn;
    private $tempDir;

    public function __construct() {
        $this->log("LDAPManager - Début de la construction");
        
        try {
            $this->config = require __DIR__ . '/../config/ldap_config.php';
            $this->log("LDAPManager - Configuration chargée : " . print_r($this->config, true));
            $this->tempDir = sys_get_temp_dir();
            $this->connect();
        } catch (Exception $e) {
            $this->log("LDAPManager - Erreur lors de la construction : " . $e->getMessage());
            $this->log("LDAPManager - Trace : " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Établit les connexions LDAP et SSH
     */
    private function connect() {
        $this->log("LDAPManager - Début de la connexion");
        
        // Connexion LDAP
        $server = "{$this->config['server']['protocol']}://{$this->config['server']['host']}:{$this->config['server']['port']}";
        $this->log("LDAPManager - Tentative de connexion LDAP à : " . $server);
        
        $this->ldapConn = ldap_connect($server);
        if (!$this->ldapConn) {
            $error = "Impossible de se connecter au serveur LDAP : " . ldap_error($this->ldapConn);
            $this->log("LDAPManager - " . $error);
            throw new Exception($error);
        }
        
        $this->log("LDAPManager - Connexion LDAP établie, configuration des options");
        ldap_set_option($this->ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($this->ldapConn, LDAP_OPT_REFERRALS, 0);
        
        $this->log("LDAPManager - Tentative d'authentification LDAP avec DN : " . $this->config['admin']['dn']);
        if (!@ldap_bind($this->ldapConn, $this->config['admin']['dn'], $this->config['admin']['password'])) {
            $error = "Échec de l'authentification LDAP : " . ldap_error($this->ldapConn);
            $this->log("LDAPManager - " . $error);
            throw new Exception($error);
        }
        $this->log("LDAPManager - Authentification LDAP réussie");
        
        // Connexion SSH
        $this->log("LDAPManager - Tentative de connexion SSH à : " . $this->config['ssh']['host']);
        $this->sshConn = ssh2_connect($this->config['ssh']['host'], $this->config['ssh']['port']);
        if (!$this->sshConn) {
            $error = "Impossible de se connecter en SSH";
            $this->log("LDAPManager - " . $error);
            throw new Exception($error);
        }
        
        $this->log("LDAPManager - Tentative d'authentification SSH");
        if (!ssh2_auth_password($this->sshConn, $this->config['ssh']['user'], $this->config['ssh']['password'])) {
            $error = "Échec de l'authentification SSH";
            $this->log("LDAPManager - " . $error);
            throw new Exception($error);
        }
        $this->log("LDAPManager - Authentification SSH réussie");
    }

    /**
     * Exécute une commande LDAP via SSH
     */
    private function executeSSHCommand($command) {
        $this->log("LDAPManager - Exécution de la commande SSH : " . $command);
        $stream = ssh2_exec($this->sshConn, $command);
        if (!$stream) {
            $error = "Échec de l'exécution de la commande";
            $this->log("LDAPManager - " . $error);
            throw new Exception($error);
        }
        
        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);
        fclose($stream);
        
        $this->log("LDAPManager - Sortie de la commande SSH : " . $output);
        return $output;
    }

    /**
     * Écrit un fichier LDIF sur le serveur
     */
    private function writeLDIFFile($content, $filename) {
        $this->log("LDAPManager - Écriture du fichier LDIF : " . $filename);
        $path = "{$this->config['ssh']['ldif_path']}/{$filename}";
        $sftp = ssh2_sftp($this->sshConn);
        file_put_contents("ssh2.sftp://{$sftp}{$path}", $content);
        $this->log("LDAPManager - Fichier LDIF écrit avec succès");
        return $path;
    }

    /**
     * Ajoute un contact dans l'annuaire
     */
    public function addContact($partenaireClient, $contactData) {
        $this->log("LDAPManager - Ajout d'un contact");
        $dn = "uid={$contactData['uid']},ou={$partenaireClient},{$this->config['base']['dn']}";
        
        // Générer le LDIF
        $ldif = LDIFTemplates::getAddTemplate($dn, $contactData);
        $this->log("LDAPManager - Génération du LDIF pour l'ajout du contact");
        $ldifPath = $this->writeLDIFFile($ldif, 'ldapadd.lddif');
        
        // Exécuter la commande
        $command = "ldapadd -x -H ldap://{$this->config['server']['name']} " .
                  "-D '{$this->config['admin']['dn']}' " .
                  "-w '{$this->config['admin']['password']}' " .
                  "-f {$ldifPath}";
        $this->log("LDAPManager - Exécution de la commande d'ajout du contact");
        return $this->executeSSHCommand($command);
    }

    /**
     * Recherche des contacts
     */
    public function searchContacts($partenaireClient, $filter = "*") {
        $this->log("LDAPManager - Recherche de contacts");
        $searchDN = "ou={$partenaireClient},{$this->config['base']['dn']}";
        $searchFilter = "(|(cn=*{$filter}*)(sn=*{$filter}*))";
        
        $result = ldap_search($this->ldapConn, $searchDN, $searchFilter);
        if (!$result) {
            $error = "Erreur lors de la recherche de contacts";
            $this->log("LDAPManager - " . $error);
            return false;
        }
        
        $this->log("LDAPManager - Résultats de la recherche de contacts");
        return ldap_get_entries($this->ldapConn, $result);
    }

    /**
     * Met à jour un contact
     */
    public function updateContact($partenaireClient, $uid, $contactData) {
        $this->log("LDAPManager - Mise à jour d'un contact");
        $dn = "uid={$uid},ou={$partenaireClient},{$this->config['base']['dn']}";
        
        // Générer le LDIF
        $ldif = LDIFTemplates::getModifyTemplate($dn, $contactData);
        $this->log("LDAPManager - Génération du LDIF pour la mise à jour du contact");
        $ldifPath = $this->writeLDIFFile($ldif, 'ldapmodify.ldif');
        
        // Exécuter la commande
        $command = "ldapmodify -x -H ldap://{$this->config['server']['name']} " .
                  "-D '{$this->config['admin']['dn']}' " .
                  "-w '{$this->config['admin']['password']}' " .
                  "-f {$ldifPath}";
        $this->log("LDAPManager - Exécution de la commande de mise à jour du contact");
        return $this->executeSSHCommand($command);
    }

    /**
     * Supprime un contact
     */
    public function deleteContact($partenaireClient, $uid) {
        $this->log("LDAPManager - Suppression d'un contact");
        $dn = "uid={$uid},ou={$partenaireClient},{$this->config['base']['dn']}";
        
        // Générer le LDIF
        $ldif = LDIFTemplates::getDeleteTemplate($dn);
        $this->log("LDAPManager - Génération du LDIF pour la suppression du contact");
        $ldifPath = $this->writeLDIFFile($ldif, 'ldapdelete.ldif');
        
        // Exécuter la commande
        $command = "ldapmodify -x -H ldap://{$this->config['server']['name']} " .
                  "-D '{$this->config['admin']['dn']}' " .
                  "-w '{$this->config['admin']['password']}' " .
                  "-f {$ldifPath}";
        $this->log("LDAPManager - Exécution de la commande de suppression du contact");
        return $this->executeSSHCommand($command);
    }

    /**
     * Ajoute un nouveau contact dans l'annuaire LDAP
     */
    public function addLdapContact($uid, $ou, $sn, $employeeNumber, $givenName = '') {
        try {
            $this->log("DEBUG - Début addLdapContact - UID: $uid, OU: $ou");
            
            // Vérifie et crée l'OU si nécessaire
            if (!$this->checkOrCreateOU($ou)) {
                throw new Exception("Impossible de créer ou vérifier l'OU");
            }
            
            // Vérifie si le contact existe déjà
            $contactExists = $this->checkIfContactExists($uid, $ou);
            
            // Génère le LDIF approprié selon que le contact existe ou non
            if ($contactExists) {
                $ldif = $this->generateModifyTemplate($uid, $ou, $sn, $employeeNumber, $givenName);
                $operation = 'modify';
            } else {
                $ldif = LDIFTemplates::generateAddTemplate($uid, $ou, $sn, $employeeNumber, $givenName);
                $operation = 'add';
            }
            
            $this->log("DEBUG - LDIF généré : \n$ldif");
            $this->log("DEBUG - Opération choisie : " . $operation);
            
            // Applique les modifications via SSH
            $result = $this->applyLdifChanges($ldif, $operation);
            
            // Vérifie que l'entrée existe bien dans LDAP
            if ($result) {
                $verified = $this->verifyLdapEntry($uid, $ou);
                $this->log("DEBUG - Vérification finale : " . ($verified ? "entrée trouvée" : "entrée non trouvée"));
                $result = $verified;
            }
            
            $this->log("DEBUG - Résultat final : " . ($result ? "succès" : "échec"));
            return $result;
        } catch (Exception $e) {
            $this->log("ERREUR - Ajout contact : " . $e->getMessage());
            throw $e;
        }
    }

    private function generateModifyTemplate($uid, $ou, $sn, $employeeNumber, $givenName = '') {
        $dn = "uid=$uid,ou=$ou,{$this->config['base']['dn']}";
        $cn = $givenName ? "$givenName $sn" : $sn;
        return "dn: $dn\nchangetype: modify\nreplace: sn\nsn: $sn\n-\nreplace: employeeNumber\nemployeeNumber: $employeeNumber\n-\nreplace: cn\ncn: $cn\n";
    }

    private function checkIfContactExists($uid, $ou) {
        $this->log("DEBUG - Vérification si le contact existe - UID: $uid, OU: $ou");
        
        $searchBase = "ou={$ou},{$this->config['base']['dn']}";
        $filter = "(uid=$uid)";
        
        $search = @ldap_search($this->ldapConn, $searchBase, $filter);
        if ($search) {
            $entries = ldap_get_entries($this->ldapConn, $search);
            $exists = ($entries['count'] > 0);
            $this->log("DEBUG - Contact " . ($exists ? "existe" : "n'existe pas"));
            return $exists;
        }
        
        $this->log("DEBUG - Erreur lors de la recherche du contact");
        return false;
    }

    private function checkOrCreateOU($ou) {
        $this->log("DEBUG - Vérification de l'existence de l'OU: $ou");
        
        // Vérifie si l'OU existe
        $search = @ldap_search($this->ldapConn, $this->config['base']['dn'], "(ou=$ou)");
        if ($search && ldap_count_entries($this->ldapConn, $search) > 0) {
            $this->log("DEBUG - OU $ou existe déjà");
            return true;
        }

        $this->log("DEBUG - OU $ou n'existe pas, création...");
        
        // Crée l'OU
        $ouLdif = "dn: ou={$ou},{$this->config['base']['dn']}\n" .
                  "objectClass: organizationalUnit\n" .
                  "ou: {$ou}\n";

        // Applique les modifications via SSH
        return $this->applyLdifChanges($ouLdif, 'add_ou');
    }

    /**
     * Modifie un contact existant
     */
    public function modifyLdapContact($uid, $ou, $sn, $employeeNumber) {
        try {
            $this->log("DEBUG - Début modifyLdapContact - UID: $uid, OU: $ou");
            
            // Vérifie si le contact existe
            if (!$this->checkIfContactExists($uid, $ou)) {
                $this->log("DEBUG - Contact n'existe pas, impossible de modifier");
                return false;
            }
            
            // Génère le LDIF de modification
            $ldif = $this->generateModifyTemplate($uid, $ou, $sn, $employeeNumber);
            $this->log("DEBUG - LDIF généré : \n$ldif");
            
            // Applique la modification
            $result = $this->applyLdifChanges($ldif, 'modify');
            
            // Vérifie que l'entrée existe et a été modifiée
            if ($result) {
                $verified = $this->verifyLdapEntry($uid, $ou);
                $this->log("DEBUG - Vérification finale : " . ($verified ? "entrée modifiée" : "entrée non trouvée"));
                $result = $verified;
            }
            
            $this->log("DEBUG - Résultat final : " . ($result ? "succès" : "échec"));
            return $result;
            
        } catch (Exception $e) {
            $this->log("ERREUR - Modification contact : " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Supprime un contact
     */
    public function deleteLdapContact($uid, $ou) {
        try {
            $this->log("DEBUG - Début deleteLdapContact - UID: $uid, OU: $ou");
            
            // Vérifie si le contact existe
            if (!$this->checkIfContactExists($uid, $ou)) {
                $this->log("DEBUG - Contact n'existe pas, rien à supprimer");
                return true;
            }
            
            // Construit le DN
            $dn = "uid=$uid,ou=$ou,{$this->config['base']['dn']}";
            $this->log("DEBUG - DN à supprimer : $dn");
            
            // Utilise l'extension PHP LDAP pour supprimer l'entrée
            if (!ldap_delete($this->ldapConn, $dn)) {
                $error = ldap_error($this->ldapConn);
                $this->log("DEBUG - Erreur lors de la suppression : $error");
                throw new Exception("Erreur lors de la suppression : $error");
            }
            
            $this->log("DEBUG - Suppression effectuée");
            
            // Vérifie que l'entrée n'existe plus
            $verified = !$this->checkIfContactExists($uid, $ou);
            $this->log("DEBUG - Vérification finale : " . ($verified ? "entrée supprimée" : "entrée toujours présente"));
            
            return $verified;
            
        } catch (Exception $e) {
            $this->log("ERREUR - Suppression contact : " . $e->getMessage());
            throw $e;
        }
    }

    private function generateDeleteTemplate($uid, $ou) {
        $dn = "uid=$uid,ou=$ou,{$this->config['base']['dn']}";
        return "dn: $dn\nchangetype: delete";
    }

    /**
     * Applique les modifications LDIF via SSH
     */
    private function applyLdifChanges($ldif, $operation) {
        try {
            $this->log("DEBUG - Début applyLdifChanges - Opération: " . $operation);
            $this->log("DEBUG - LDIF à appliquer:\n" . $ldif);

            // Crée un fichier LDIF temporaire
            $filename = $operation . '_' . time() . '.ldif';
            $ldifPath = $this->writeLDIFFile($ldif, $filename);
            $this->log("DEBUG - Fichier LDIF créé: " . $ldifPath);

            // Construit la commande selon l'opération
            switch ($operation) {
                case 'add':
                case 'add_ou':
                    $command = "ldapadd";
                    break;
                case 'modify':
                    $command = "ldapmodify";
                    break;
                case 'delete':
                    $command = "ldapdelete";
                    break;
                default:
                    throw new Exception("Opération non reconnue: " . $operation);
            }

            // Exécute la commande LDAP
            $fullCommand = "$command -x -H ldap://{$this->config['server']['host']} " .
                         "-D \"{$this->config['admin']['dn']}\" " .
                         "-w \"{$this->config['admin']['password']}\" " .
                         "-f \"$ldifPath\"";

            $this->log("DEBUG - Exécution de la commande: " . $fullCommand);
            $output = $this->executeSSHCommand($fullCommand);
            $this->log("DEBUG - Sortie de la commande:\n" . $output);

            // Nettoyage du fichier LDIF
            $cleanupCommand = "rm -f \"$ldifPath\"";
            $this->executeSSHCommand($cleanupCommand);
            $this->log("DEBUG - Fichier LDIF nettoyé");

            return true;
        } catch (Exception $e) {
            $this->log("ERREUR - applyLdifChanges: " . $e->getMessage());
            return false;
        }
    }

    private function verifyLdapEntry($uid, $ou) {
        $this->log("DEBUG - Vérification de l'entrée LDAP après opération");
        $command = "ldapsearch -x -D \"{$this->config['admin']['dn']}\" -w {$this->config['admin']['password']} -b \"ou=$ou,{$this->config['base']['dn']}\" \"(uid=$uid)\" -LLL";
        
        $stream = ssh2_exec($this->sshConn, $command);
        stream_set_blocking($stream, true);
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
        stream_set_blocking($errorStream, true);
        
        $output = stream_get_contents($stream);
        $errors = stream_get_contents($errorStream);
        
        $this->log("DEBUG - Résultat de la vérification :\n" . $output);
        if ($errors) {
            $this->log("DEBUG - Erreurs de la vérification :\n" . $errors);
        }
        
        return !empty($output);
    }

    /**
     * Vérifie si une entrée existe dans LDAP
     */
    public function entryExists($ou, $uid) {
        try {
            $this->log("DEBUG - Vérification existence entrée: ou=$ou, uid=$uid");
            
            $dn = $this->buildDN($ou, $uid);
            $command = "ldapsearch -x -D \"{$this->config['admin']['dn']}\" -w {$this->config['admin']['password']} -b \"$dn\" -s base";
            
            $stream = ssh2_exec($this->sshConn, $command);
            stream_set_blocking($stream, true);
            $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            stream_set_blocking($errorStream, true);
            
            $output = stream_get_contents($stream);
            $errors = stream_get_contents($errorStream);
            
            // Si on trouve une entrée, elle existe
            return strpos($output, "# numEntries:") !== false;
            
        } catch (Exception $e) {
            $this->log("ERREUR - Vérification entrée : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajoute une entrée dans LDAP
     */
    public function addEntry($ou, $uid, $data) {
        try {
            $this->log("DEBUG - Ajout entrée: ou=$ou, uid=$uid");
            
            // Prépare le fichier LDIF avec le format exact des tests
            $dn = $this->buildDN($ou, $uid);
            $ldifContent = "dn: $dn\n";
            $ldifContent .= "uid: $uid\n";
            $ldifContent .= "objectClass: inetOrgPerson\n";
            $ldifContent .= "sn: {$data['sn']}\n";
            $ldifContent .= "employeeNumber: {$data['employeeNumber']}\n";
            $ldifContent .= "cn: {$data['cn']}\n";
            
            $this->log("DEBUG - Contenu LDIF généré :\n$ldifContent");
            
            // Crée un fichier LDIF temporaire
            $tempFile = tempnam($this->config['ssh']['ldif_path'], "add_");
            $ldifFile = $tempFile . ".ldif";
            rename($tempFile, $ldifFile);
            
            // Écrit le contenu LDIF
            $sftp = ssh2_sftp($this->sshConn);
            $stream = fopen("ssh2.sftp://$sftp$ldifFile", 'w');
            if ($stream === false) {
                $this->log("ERREUR - Impossible d'ouvrir le fichier LDIF pour écriture");
                return false;
            }
            
            if (fwrite($stream, $ldifContent) === false) {
                $this->log("ERREUR - Impossible d'écrire dans le fichier LDIF");
                fclose($stream);
                return false;
            }
            fclose($stream);
            
            // Exécute la commande ldapadd
            $command = "ldapadd -x -D \"{$this->config['admin']['dn']}\" -w {$this->config['admin']['password']} -f \"$ldifFile\"";
            
            $stream = ssh2_exec($this->sshConn, $command);
            if ($stream === false) {
                $this->log("ERREUR - Impossible d'exécuter la commande ldapadd");
                return false;
            }
            
            stream_set_blocking($stream, true);
            $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            stream_set_blocking($errorStream, true);
            
            $output = stream_get_contents($stream);
            $errors = stream_get_contents($errorStream);
            
            // Supprime le fichier LDIF
            if (!unlink("ssh2.sftp://$sftp$ldifFile")) {
                $this->log("ATTENTION - Impossible de supprimer le fichier LDIF temporaire");
            }
            
            $this->log("DEBUG - Sortie de la commande :\n" . $output);
            if ($errors) {
                $this->log("DEBUG - Erreurs de la commande :\n" . $errors);
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->log("ERREUR - Ajout entrée : " . $e->getMessage() . "\nTrace : " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Met à jour une entrée dans LDAP
     */
    public function updateEntry($ou, $uid, $data) {
        try {
            $this->log("DEBUG - Début updateEntry - OU: $ou, UID: $uid");
            $this->log("DEBUG - Données à mettre à jour : " . print_r($data, true));

            // Construction du DN
            $dn = "uid=$uid,ou=$ou,{$this->config['base']['dn']}";
            $this->log("DEBUG - DN construit : $dn");

            // Création du contenu LDIF
            $ldif = "dn: $dn\n" .
                   "changetype: modify\n";

            // Ajout de chaque modification
            foreach ($data as $attr => $value) {
                $ldif .= "replace: $attr\n";
                if (is_array($value)) {
                    foreach ($value as $val) {
                        $ldif .= "$attr: $val\n";
                    }
                } else {
                    $ldif .= "$attr: $value\n";
                }
                $ldif .= "-\n";
            }

            $this->log("DEBUG - LDIF généré : \n$ldif");

            // Écriture du fichier LDIF
            $filename = 'modify_' . time() . '.ldif';
            $ldifPath = $this->writeLDIFFile($ldif, $filename);

            // Exécution de la commande LDAP
            $command = "ldapmodify -x -H ldap://{$this->config['server']['host']} " .
                      "-D \"{$this->config['admin']['dn']}\" " .
                      "-w \"{$this->config['admin']['password']}\" " .
                      "-f $ldifPath";

            $this->log("DEBUG - Exécution de la commande de modification");
            $output = $this->executeSSHCommand($command);
            $this->log("DEBUG - Sortie de la commande : $output");

            return true;
        } catch (Exception $e) {
            $this->log("ERROR - Erreur lors de la mise à jour : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une entrée de LDAP
     */
    public function deleteEntry($ou, $uid) {
        try {
            $this->log("DEBUG - Début deleteEntry - OU: $ou, UID: $uid");

            // Construction du DN
            $dn = "uid=$uid,ou=$ou,{$this->config['base']['dn']}";
            $this->log("DEBUG - DN construit : $dn");

            // Création du contenu LDIF
            $ldif = "dn: $dn\n" .
                   "changetype: delete\n";

            $this->log("DEBUG - LDIF généré : \n$ldif");

            // Écriture du fichier LDIF
            $filename = 'delete_' . time() . '.ldif';
            $ldifPath = $this->writeLDIFFile($ldif, $filename);

            // Exécution de la commande LDAP
            $command = "ldapmodify -x -H ldap://{$this->config['server']['host']} " .
                      "-D \"{$this->config['admin']['dn']}\" " .
                      "-w \"{$this->config['admin']['password']}\" " .
                      "-f $ldifPath";

            $this->log("DEBUG - Exécution de la commande de suppression");
            $output = $this->executeSSHCommand($command);
            $this->log("DEBUG - Sortie de la commande : $output");

            return true;
        } catch (Exception $e) {
            $this->log("ERROR - Erreur lors de la suppression : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Échappe un DN LDAP
     */
    private function escapeDN($dn) {
        // Échappe les caractères spéciaux dans le DN
        $escaped = str_replace(
            ['\\', ',', '=', '+', '<', '>', ';', '"', '#'],
            ['\\\\', '\\,', '\\=', '\\+', '\\<', '\\>', '\\;', '\\"', '\\#'],
            $dn
        );
        
        // Échappe les espaces en début et fin
        if (substr($escaped, 0, 1) === ' ') {
            $escaped = '\\' . $escaped;
        }
        if (substr($escaped, -1) === ' ') {
            $escaped = substr($escaped, 0, -1) . '\\ ';
        }
        
        return $escaped;
    }

    /**
     * Construit un DN complet
     */
    private function buildDN($ou, $uid = null) {
        $ou = $this->escapeDN($ou);
        $baseDN = $this->config['base']['dn'];
        
        if ($uid !== null) {
            $uid = $this->escapeDN($uid);
            return "uid=$uid,ou=$ou,$baseDN";
        }
        
        return "ou=$ou,$baseDN";
    }

    /**
     * Vérifie si un OU existe dans LDAP
     */
    public function checkIfOUExists($ou) {
        try {
            $this->log("DEBUG - Vérification existence OU: $ou");
            
            $dn = "ou=$ou,{$this->config['base']['dn']}";
            $command = "ldapsearch -x -D \"{$this->config['admin']['dn']}\" -w {$this->config['admin']['password']} -b \"$dn\" -s base";
            
            $stream = ssh2_exec($this->sshConn, $command);
            stream_set_blocking($stream, true);
            $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            stream_set_blocking($errorStream, true);
            
            $output = stream_get_contents($stream);
            $errors = stream_get_contents($errorStream);
            
            $this->log("DEBUG - Sortie de la commande :\n" . $output);
            if ($errors) {
                $this->log("DEBUG - Erreurs de la commande :\n" . $errors);
            }
            
            // Si on trouve une entrée, l'OU existe
            return strpos($output, "# numEntries:") !== false;
            
        } catch (Exception $e) {
            $this->log("ERREUR - Vérification OU : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crée un nouvel OU dans LDAP
     */
    public function createOU($ou) {
        try {
            $this->log("DEBUG - Création OU: $ou");
            
            // Prépare le fichier LDIF
            $ldifContent = "dn: ou=$ou,{$this->config['base']['dn']}\n";
            $ldifContent .= "objectClass: organizationalUnit\n";
            $ldifContent .= "ou: $ou\n";
            
            // Crée un fichier LDIF temporaire
            $tempFile = tempnam($this->config['ssh']['ldif_path'], "ou_");
            $ldifFile = $tempFile . ".ldif";
            rename($tempFile, $ldifFile);
            
            // Écrit le contenu LDIF
            $sftp = ssh2_sftp($this->sshConn);
            $stream = fopen("ssh2.sftp://$sftp$ldifFile", 'w');
            fwrite($stream, $ldifContent);
            fclose($stream);
            
            // Exécute la commande ldapadd
            $command = "ldapadd -x -D \"{$this->config['admin']['dn']}\" -w {$this->config['admin']['password']} -f \"$ldifFile\"";
            
            $stream = ssh2_exec($this->sshConn, $command);
            stream_set_blocking($stream, true);
            $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            stream_set_blocking($errorStream, true);
            
            $output = stream_get_contents($stream);
            $errors = stream_get_contents($errorStream);
            
            // Supprime le fichier LDIF
            unlink("ssh2.sftp://$sftp$ldifFile");
            
            $this->log("DEBUG - Sortie de la commande :\n" . $output);
            if ($errors) {
                $this->log("DEBUG - Erreurs de la commande :\n" . $errors);
                if (strpos($errors, "Already exists") !== false) {
                    return true; // L'OU existe déjà, ce n'est pas une erreur
                }
                return false;
            }
            
            return true;
            
        } catch (Exception $e) {
            $this->log("ERREUR - Création OU : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Teste les connexions LDAP et SSH
     */
    public function testConnections() {
        $result = [
            'ldap' => false,
            'ssh' => false,
            'error' => null
        ];

        try {
            // Test LDAP
            $search = ldap_search($this->ldapConn, $this->config['base']['dn'], "(objectclass=*)");
            if ($search) {
                $result['ldap'] = true;
            }

            // Test SSH
            $stream = ssh2_exec($this->sshConn, 'echo "Test SSH connection"');
            if ($stream) {
                stream_set_blocking($stream, true);
                $output = stream_get_contents($stream);
                if (strpos($output, 'Test SSH connection') !== false) {
                    $result['ssh'] = true;
                }
            }

        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        error_log("[LDAP $timestamp] " . $message);
    }

    public function __destruct() {
        $this->log("LDAPManager - Destruction de l'objet");
        if ($this->ldapConn) {
            ldap_close($this->ldapConn);
        }
    }
}
