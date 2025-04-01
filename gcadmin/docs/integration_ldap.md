# Documentation d'Intégration LDAP - GCAdmin
Date : 06/02/2025

## 1. Mise en place initiale

### 1.1 Structure du projet
Nous avons organisé le code en plusieurs dossiers :
```
/var/www/GCAdmin/
├── docs/               # Documentation
├── config/            # Configuration générale
├── utils/             # Utilitaires
└── ldap/              # Gestion LDAP
```

### 1.2 Système de chiffrement
Premier élément mis en place pour sécuriser les mots de passe.

1. Création de `utils/encryption.php` :
   - Chiffrement AES-256-CBC
   - Clé statique pour éviter les problèmes de permissions
   - Fonctions de chiffrement/déchiffrement

2. Création de `utils/encrypt_form.php` :
   - Interface web pour chiffrer les mots de passe
   - Test automatique du déchiffrement
   - Copie facile des valeurs chiffrées
//// peut servir pour d'autre projet car c'est méga pratique ///////////

### 1.3 Configuration sécurisée
Mise en place du système de configuration avec mots de passe chiffrés.

1. Création de `config/.env` :
```
LDAP_ADMIN_PASSWORD=ENC:[valeur_chiffrée]
LDAP_SSH_PASSWORD=ENC:[valeur_chiffrée]
```

2. Configuration LDAP dans `ldap/config/ldap_config.php` :
   - Chargement des secrets depuis .env
   - Configuration du serveur LDAP
   - Configuration SSH pour les opérations LDIF

## 2. Implémentation LDAP

### 2.1 Classes principales
1. `ldap/core/LDAPManager.php` :
   - Gestion des connexions LDAP et SSH
   - Opérations CRUD sur les contacts
   - Gestion des fichiers LDIF

2. `ldap/templates/ldif_templates.php` :
   - Templates pour les opérations LDAP
   - Génération des fichiers LDIF

### 2.2 Page de test
Création de `ldap/test_connection.php` :
   - Test de la connexion LDAP
   - Test de la connexion SSH
   - Affichage des erreurs détaillé

## 3. Installation des dépendances

### 3.1 Extensions PHP requises
Sur le serveur web (54.36.189.50) :
```bash
sudo apt-get install php8.1-ldap
sudo apt-get install php8.1-ssh2
sudo systemctl restart apache2
```

## 4. Configuration des serveurs

### 4.1 Serveur Web (54.36.189.50)
- Héberge l'application GCAdmin
- Extensions PHP installées : ldap, ssh2
- Port SSH : 715

### 4.2 Serveur LDAP (141.94.251.137)
- Serveur LDAP pur (pas de serveur web)
- Port LDAP : 389 
- SSH : 
  - Utilisateur : root
  - Port : 673 
  - Connexion fonctionnelle

## 5. Tests et débogage

### 5.1 Test du chiffrement
1. Accéder à `http://54.36.189.50/gcadmin/utils/encrypt_form.php`
2. Entrer le mot de passe
3. Vérifier le déchiffrement automatique
4. Copier la valeur chiffrée dans .env

### 5.2 Test des connexions
1. Accéder à `http://54.36.189.50/gcadmin/ldap/test_connection.php`
2. Vérifier les logs pour les erreurs
3. Test telnet pour vérifier l'accès SSH

## 6. Problèmes en cours

### 6.1 Connexion SSH
 Problème résolu :
- Port SSH correct : 673
- Connexion SSH fonctionnelle
- Tests de connexion réussis

### 6.2 Prochaines étapes
1. Implémenter la gestion des contacts dans l'interface
2. Tester l'intégration complète avec l'annuaire

## 7. Utilisation quotidienne

### 7.1 Modification des mots de passe
1. Utiliser encrypt_form.php pour chiffrer
2. Mettre à jour .env
3. Redémarrer Apache si nécessaire

### 7.2 Surveillance
- Vérifier régulièrement test_connection.php
- Surveiller les logs PHP pour les erreurs
- Maintenir les mots de passe à jour
