<?php

//Gestion des clients
class ShowTypePostesForm {

    private $pdo; 
    private $TypePostesRecoverySQLRequest = "SELECT * FROM TypePostes ORDER BY TypePoste";
    
    
    //Constructeur pour initialiser la connexion PDO
    function __construct($pdo) {
        $this->pdo = $pdo; 
    }

    //Récupération de tous les types de postes
    function TypePostesRecovery(){

        $stmt = $this->pdo->prepare($this->TypePostesRecoverySQLRequest);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}

// Instance de la class ShowClientForm
$TypePostesForm = new ShowTypePostesForm($pdo);
$TypePostes = $TypePostesForm->TypePostesRecovery();

?>