<?php

class ClientManager {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Récupère un client par son ID
     */
    public function getClientById($clientId) {
        try {
            $sql = "SELECT c.*, p.idpartenaires 
                    FROM Clients c 
                    JOIN Partenaires p ON c.partenaires_idpartenaires = p.idpartenaires 
                    WHERE c.idclients = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$clientId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans getClientById : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère tous les clients d'un partenaire
     */
    public function getClientsByPartenaire($partenaireId) {
        try {
            $sql = "SELECT * FROM Clients WHERE partenaires_idpartenaires = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$partenaireId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur dans getClientsByPartenaire : " . $e->getMessage());
            return [];
        }
    }
}
