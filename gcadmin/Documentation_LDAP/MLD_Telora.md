[[Fiche Bases de données, MCD, MLD.pdf]](exemple pour mieux comprendre)
[[MCD_Telora.pdf]](MCD associer)
***
User_annuaire (ID, Prenom, Nom, Email, Societe, Ville, Commentaire, Téléphone, #ID_annuaire)
***
UtilisateurBLF (ID, TypeBLF, Etiquette, Valeur, Position, #ID_utilisateur)
***
Utilisateur (ID, Nom, Extension, AdresseMac, SerialNumber, SIPLogin, SiPPassword, SIPServeur, #TypePoste_TypesPoste, #ID_client)
***
Annuaire (ID, Nom, #ID_client)
***
Plateforme (ID, PlateformeNom, #ID_client)
***
Clients (ID, Nom, Email, telephone, adresse, plateforme, plateformeURL, #ID_Partenaire)
***
Roles (ID, Login, MDP, Status, #ID_Partenaires, #ID_Clients)
***
TypePoste (ID, TypePoste, PosteCategorie)
***
Partenaires (ID, Nom, Email, Telephone, Adresse)




table Utilisateur : Supprimer colonne annuaires_idannuaires ??? question 4 lolo