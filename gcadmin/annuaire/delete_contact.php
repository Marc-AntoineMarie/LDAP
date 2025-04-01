<?php
require_once '../database/db.php';
require_once '../database/Annuaire_request.php';

///////////////////// vérif des rôles ///////////////////
session_start();
if (!isset($_SESSION['role'])) {
    header('Location: ../login/login.php');
    exit;
}
///////////////////// FIN vérif des rôles ///////////////////

// Vérifier que l'ID est fourni
if (!isset($_GET['id'])) {
    header('Location: annuaire.php');
    exit;
}

$contactId = (int)$_GET['id'];

try {
    $annuaireManager = new AnnuaireManager($pdo);
    $result = $annuaireManager->deleteContact($contactId);
    
    if ($result) {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    } else {
        echo "Erreur lors de la suppression du contact";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
