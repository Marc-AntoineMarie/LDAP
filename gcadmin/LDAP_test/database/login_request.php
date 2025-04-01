<?php

// il manque l'insertion de la donner partenaires_idpartenaires
// il y a peu etre des soucis au niveau du traitement de cette donnée aussi
// et il peu y avoir des soucis avec clients_idclients que je n'est pas encore eu le temps de traiter car j'essayais d'avoir une gestion d'erreur clean
// et un netoyage au propre

class UserAuthentication {
    private $pdo;
    private $loginRequest = "SELECT idRole, MDP, Status FROM roles WHERE Login = :Login";
    private $registerRequest = "INSERT INTO roles (Login, Email, MDP, Status) VALUES (:Login, :Email, :MDP, :Status)";
    private $updatePasswordRequest = "UPDATE roles SET MDP = :MDP WHERE Email = :Email";
    private $deleteRequest = "DELETE FROM roles WHERE idRole = :idRole";

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Nettoyer les données
    private function sanitizeInput($input) {
        return htmlspecialchars(trim($input));
    }

        // CREATE (inscription user)
    function register($Login, $Email, $MDP, $status, $partnerId) {
        try {
            // Nettoyage des données
            $Login = $this->sanitizeInput($Login);
            $Email = $this->sanitizeInput($Email);
            $MDP = $this->sanitizeInput($MDP);
            $status = $this->sanitizeInput($status);
            $partnerId = $this->sanitizeInput($partnerId); // Nettoyage de l'ID du partenaire
        
            // Validation des champs obligatoires
            if (empty($Login) || empty($MDP) || empty($status) || empty($partnerId)) {
                throw new Exception('Les champs Login, Mot de passe, Status et Partenaire sont obligatoires.');
            }
        
            // Validation de l'email si fourni
            if (!empty($Email) && !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email invalide.');
            }
        
            // Si l'email est vide, on remplace par "non fournis"
            if (empty($Email)) {
                $Email = 'non fournis';
            }
        
            // Vérifier si le login est déjà utilisé
            $checkLoginQuery = "SELECT COUNT(*) FROM roles WHERE Login = :Login";
            $stmt = $this->pdo->prepare($checkLoginQuery);
            $stmt->bindParam(":Login", $Login, PDO::PARAM_STR);
            $stmt->execute();
            $loginCount = $stmt->fetchColumn();
        
            if ($loginCount > 0) {
                throw new Exception('Ce login est déjà utilisé.');
            }
        
            // Vérifier si le partenaire existe dans la table partenaires
            $checkPartnerQuery = "SELECT COUNT(*) FROM partenaires WHERE idpartenaires = :partnerId";
            $stmt = $this->pdo->prepare($checkPartnerQuery);
            $stmt->bindParam(":partnerId", $partnerId, PDO::PARAM_INT);
            $stmt->execute();
            $partnerCount = $stmt->fetchColumn();
        
            if ($partnerCount == 0) {
                throw new Exception('Le partenaire sélectionné n\'existe pas.');
            }
        
            // Préparer la requête SQL pour l'insertion
            $stmt = $this->pdo->prepare($this->registerRequest);
            $hashedPassword = password_hash($MDP, PASSWORD_BCRYPT);
        
            // Bind les paramètres
            $stmt->bindParam(":Login", $Login, PDO::PARAM_STR);
            $stmt->bindParam(":Email", $Email, PDO::PARAM_STR);
            $stmt->bindParam(":MDP", $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(":Status", $status, PDO::PARAM_STR);
            $stmt->bindParam(":PartnerId", $partnerId, PDO::PARAM_INT); // Bind l'ID du partenaire
        
            // Exécution de la requête
            $result = $stmt->execute();
        
            // Si l'insertion est réussie
            if ($result) {
                return true;
            } else {
                throw new Exception('Une erreur est survenue lors de l\'inscription.');
            }
        } catch (PDOException $e) {
            throw new Exception('Erreur de base de données : ' . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception('Erreur : ' . $e->getMessage());
        }
    }

    // READ (connexion d'un user)
    function Login($Login, $MDP) {
        try {
            // Nettoyage des données
            $Login = $this->sanitizeInput($Login);
            $MDP = $this->sanitizeInput($MDP);

            // Validation des champs obligatoires
            if (empty($Login) || empty($MDP)) {
                throw new Exception('Les champs Login et Mot de passe sont obligatoires.');
            }

            // Préparer la requête SQL
            $stmt = $this->pdo->prepare($this->loginRequest);
            $stmt->bindParam(":Login", $Login, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($MDP, $user['MDP'])) {
                    return $user;
                } else {
                    throw new Exception('Mot de passe incorrect.');
                }
            } else {
                throw new Exception('Aucun compte trouvé avec ce login.');
            }
        } catch (PDOException $e) {
            throw new Exception('Erreur de base de données : ' . $e->getMessage());
        }
    }

    // UPDATE (changer le mot de passe utilisateur)
    function Updatepass($Email, $newPass) {
        try {
            // Nettoyage des données
            $Email = $this->sanitizeInput($Email);
            $newPass = $this->sanitizeInput($newPass);

            // Validation des champs obligatoires
            if (empty($Email) || empty($newPass)) {
                throw new Exception('Les champs Email et Nouveau mot de passe sont obligatoires.');
            }

            // Validation de l'email
            if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Email invalide.');
            }

            // Préparer la requête SQL
            $stmt = $this->pdo->prepare($this->updatePasswordRequest);
            $hashedPass = password_hash($newPass, PASSWORD_BCRYPT);

            $stmt->bindParam(":Email", $Email, PDO::PARAM_STR);
            $stmt->bindParam(":MDP", $hashedPass, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception('Erreur de base de données : ' . $e->getMessage());
        }
    }

    // DELETE (supprimer un utilisateur par ID)
    function deleteUser($idRole) {
        try {
            // Nettoyage des données
            $idRole = $this->sanitizeInput($idRole);

            // Validation des champs obligatoires
            if (empty($idRole)) {
                throw new Exception('L\'ID de l\'utilisateur est obligatoire.');
            }

            // Préparer la requête SQL
            $stmt = $this->pdo->prepare($this->deleteRequest);
            $stmt->bindParam(":idRole", $idRole, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            throw new Exception('Erreur de base de données : ' . $e->getMessage());
        }
    }
}


// Instanciation de la classe
$rolesCRUD = new UserAuthentication($pdo);

?>
