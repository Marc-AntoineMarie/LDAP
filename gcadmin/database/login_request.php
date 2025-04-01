<?php
/**
 * Gestion des requêtes d'authentification
 * 
 * Ce fichier contient toutes les fonctions liées à :
 * - La vérification des identifiants
 * - La gestion des sessions utilisateur
 * - La récupération des informations de connexion
 * 
 * Sécurité :
 * - Utilisation de requêtes préparées
 * - Protection contre les injections SQL
 * - Gestion sécurisée des mots de passe
 */

session_start();
require_once '../database/db.php';

/**
 * Classe de gestion des connexions utilisateur
 * 
 * Cette classe :
 * - Vérifie les identifiants de connexion
 * - Gère les sessions utilisateur
 * - Récupère les informations de connexion
 */
class UserLogin {
    private $pdo;

    /**
     * Constructeur de la classe
     * 
     * @param PDO $pdo Instance de connexion à la base de données
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Vérifie les identifiants de connexion
     * 
     * @param string $login Nom d'utilisateur
     * @param string $password Mot de passe
     * @return string Message de résultat (succès ou erreur)
     * 
     * Cette fonction :
     * - Vérifie l'existence de l'utilisateur
     * - Compare les mots de passe de manière sécurisée
     * - Retourne les informations nécessaires à la session
     */
    public function login($login, $password) {
        if (empty($login) || empty($password)) {
            return "Veuillez remplir tous les champs.";
        }
    
        try {
            // Récupérer les informations de l'utilisateur
            $stmt = $this->pdo->prepare("SELECT * FROM Roles WHERE Login = :login");
            $stmt->bindParam(':login', $login, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch();

            if (!$user) {
                return "Identifiants incorrects.";
            }
    
            if ($user && password_verify($password, $user['MDP'])) {
                // Stocker les informations utilisateur dans la session
                $_SESSION['user_id'] = $user['idRole'];
                $_SESSION['role'] = $user['Status'];
                $_SESSION['login'] = $user['Login'];
                $_SESSION['partner_id'] = $user['partenaires_idpartenaires'] ?? null;
                $_SESSION['client_id'] = $user['clients_idclients'] ?? null;

                return $this->redirectBasedOnRole($user['Status'], $user);
            } else {
                return "Identifiants incorrects.";
            }
        } catch (PDOException $e) {
            return "Erreur lors de la connexion à la base de données : " . $e->getMessage();
        }
    }

    /**
     * Redirige l'utilisateur en fonction de son rôle
     * 
     * @param string $role Rôle de l'utilisateur (Admin/Partenaire/Client)
     * @param array $user Informations de l'utilisateur
     * @return string Message de résultat (succès ou erreur)
     * 
     * Cette fonction :
     * - Détermine le type d'utilisateur
     * - Permet l'attribution des droits appropriés
     * - Gère la navigation selon le rôle
     */
    private function redirectBasedOnRole($role, $user) {
        switch ($role) {
            case 'Admin':
                header('Location: ../admin/V1_admin.php');
                exit;
            case 'Partenaire':
                if (!empty($user['partenaires_idpartenaires'])) {
                    header('Location: ../clientlist/clientlist.php?idpartenaires=' . $user['partenaires_idpartenaires']);
                    exit;
                } else {
                    return "Aucun partenaire associé à cet utilisateur.";
                }
            case 'Client':
                if (!empty($user['clients_idclients'])) {
                    header('Location: ../clientdetail/clientdetail.php?idclient=' . $user['clients_idclients']);
                    exit;
                } else {
                    return "Aucun client associé à cet utilisateur.";
                }
            default:
                return "Rôle utilisateur inconnu.";
        }
    }
}

// Validation de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = htmlspecialchars($_POST['login'] ?? '');
    $password = htmlspecialchars($_POST['password'] ?? '');

    $userLogin = new UserLogin($pdo);
    $error = $userLogin->login($login, $password);
}

?>