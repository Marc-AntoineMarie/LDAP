<?php
/**
 * Templates pour les fichiers LDIF
 */
class LDIFTemplates {
    
    /**
     * Génère le LDIF pour ajouter un contact
     */
    public static function generateAddTemplate($uid, $ou, $sn, $employeeNumber, $givenName = '') {
        $cn = $givenName ? "$givenName $sn" : $sn;
        return "dn: uid={$uid},ou={$ou},dc=test,dc=gcservice,dc=fr\n" .
               "objectClass: inetOrgPerson\n" .
               "uid: {$uid}\n" .
               "sn: {$sn}\n" .
               "employeeNumber: {$employeeNumber}\n" .
               "cn: {$cn}\n";
    }

    /**
     * Génère le LDIF pour supprimer un contact
     */
    public static function generateDeleteTemplate($uid, $ou) {
        return "dn: uid={$uid},ou={$ou},dc=test,dc=gcservice,dc=fr\n" .
               "changetype: delete\n";
    }

    /**
     * Génère le LDIF pour modifier un contact
     */
    public static function generateModifyTemplate($uid, $ou, $modifications) {
        $ldif = "dn: uid={$uid},ou={$ou},dc=test,dc=gcservice,dc=fr\n" .
                "changetype: modify\n";

        foreach ($modifications as $attribute => $newValue) {
            if ($attribute !== 'objectClass' && $attribute !== 'uid') {
                $ldif .= "replace: {$attribute}\n";
                if (is_array($newValue)) {
                    foreach ($newValue as $val) {
                        $ldif .= "{$attribute}: {$val}\n";
                    }
                } else {
                    $ldif .= "{$attribute}: {$newValue}\n";
                }
                $ldif .= "-\n";
            }
        }

        return $ldif;
    }

    /**
     * Exemple d'utilisation :
     * $modifications = [
     *     'sn' => 'geoffroychacun',
     *     'employeeNumber' => '0634493250'
     * ];
     * $ldif = LDIFTemplates::generateModifyTemplate('laurentbrunet', 'Omnitel-HTVS', $modifications);
     */
}
