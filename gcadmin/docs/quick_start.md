# Guide Rapide - Intégration LDAP GCAdmin

## Configuration rapide

1. **Installer les extensions PHP requises**
```bash
sudo apt-get install php8.1-ldap php8.1-ssh2
sudo systemctl restart apache2
```

2. **Chiffrer les mots de passe**
- Accéder à : `http://54.36.189.50/gcadmin/utils/encrypt_form.php`
- Chiffrer les mots de passe LDAP et SSH
- Copier les valeurs dans `config/.env`

3. **Tester les connexions**
- Accéder à : `http://54.36.189.50/gcadmin/ldap/test_connection.php`
- Vérifier que LDAP et SSH fonctionnent

## Fichiers importants

- `config/.env` : Mots de passe chiffrés
- `ldap/config/ldap_config.php` : Configuration LDAP
- `ldap/test_connection.php` : Page de test
- `utils/encrypt_form.php` : Interface de chiffrement

## Serveurs

1. **Serveur Web (54.36.189.50)**
   - Héberge GCAdmin
   - SSH : port 715
   - User : debian

2. **Serveur LDAP (141.94.251.137)**
   - LDAP : port 389
   - SSH : port 673
   - User : root

## En cas de problème

1. Vérifier les logs PHP
2. Tester le chiffrement avec encrypt_form.php
3. Vérifier les connexions avec test_connection.php
